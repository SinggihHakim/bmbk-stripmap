<?php

/**
 * ============================================================
 * Helper: ExcelImporter
 * ============================================================
 * Parser file Excel (.xlsx) dan CSV (.csv) native PHP tanpa dependency.
 * Membaca data Rekapitulasi Kondisi & Perkerasan Jalan secara otomatis.
 */

class ExcelImporter
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Parse file upload (.xlsx atau .csv) dan simpan ke database
     *
     * @param string $filePath Path file temporary di server
     * @param string $originalName Nama file asli
     * @return array Result [success => bool, message => string, count => int, errors => array]
     */
    public function import(string $filePath, string $originalName): array
    {
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        $rows = [];
        if ($ext === 'csv' || $ext === 'txt') {
            $rows = $this->parseCsv($filePath);
        } elseif ($ext === 'xlsx') {
            $rows = $this->parseXlsx($filePath);
        } else {
            return [
                'success' => false,
                'message' => 'Format file tidak didukung. Harap upload file berformat .xlsx atau .csv',
                'count'   => 0
            ];
        }

        if (empty($rows)) {
            return [
                'success' => false,
                'message' => 'File kosong atau lembar Excel tidak dapat dibaca.',
                'count'   => 0
            ];
        }

        return $this->processDataRows($rows);
    }

    /**
     * Parse CSV file
     */
    private function parseCsv(string $filePath): array
    {
        $rows = [];
        $handle = fopen($filePath, 'r');
        if (!$handle) return [];

        $firstLine = fgets($handle);
        rewind($handle);
        $delimiter = ';';
        if (substr_count($firstLine, ',') > substr_count($firstLine, ';')) {
            $delimiter = ',';
        } elseif (substr_count($firstLine, "\t") > substr_count($firstLine, ';')) {
            $delimiter = "\t";
        }

        while (($data = fgetcsv($handle, 4096, $delimiter)) !== false) {
            $rows[] = array_map('trim', $data);
        }
        fclose($handle);
        return $rows;
    }

    /**
     * Parse XLSX file menggunakan ZipArchive + SimpleXML
     */
    private function parseXlsx(string $filePath): array
    {
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            return [];
        }

        // 1. Baca Shared Strings
        $sharedStrings = [];
        $sharedStringXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedStringXml) {
            $xml = simplexml_load_string($sharedStringXml);
            if ($xml) {
                foreach ($xml->children() as $item) {
                    if ($item->getName() === 'si') {
                        if (isset($item->t)) {
                            $sharedStrings[] = (string)$item->t;
                        } elseif (isset($item->r)) {
                            $text = '';
                            foreach ($item->r as $r) {
                                $text .= (string)$r->t;
                            }
                            $sharedStrings[] = $text;
                        } else {
                            $sharedStrings[] = '';
                        }
                    }
                }
            }
        }

        // 2. Cari file worksheet teratas
        $sheetXml = null;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            if (preg_match('#xl/worksheets/sheet\d+\.xml#i', $stat['name'])) {
                $sheetXml = $zip->getFromIndex($i);
                break;
            }
        }
        if (!$sheetXml) {
            $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        }
        $zip->close();

        if (!$sheetXml) return [];

        $xml = simplexml_load_string($sheetXml);
        if (!$xml || !isset($xml->sheetData->row)) return [];

        $rows = [];
        foreach ($xml->sheetData->row as $row) {
            $rowData = [];
            foreach ($row->c as $c) {
                $cellRef  = (string)$c['r']; // e.g. A1, B5
                $colAlpha = preg_replace('/[0-9]/', '', $cellRef);
                $colIndex = $this->columnLetterToIndex($colAlpha);

                $type = (string)$c['t'];
                $val  = (string)$c->v;

                if ($type === 's' && isset($sharedStrings[(int)$val])) {
                    $cellVal = $sharedStrings[(int)$val];
                } elseif ($type === 'inlineStr' && isset($c->is->t)) {
                    $cellVal = (string)$c->is->t;
                } else {
                    $cellVal = $val;
                }

                $rowData[$colIndex] = trim($cellVal);
            }

            if (!empty($rowData)) {
                ksort($rowData);
                $maxIndex = max(array_keys($rowData));
                $filledRow = [];
                for ($k = 0; $k <= $maxIndex; $k++) {
                    $filledRow[$k] = $rowData[$k] ?? '';
                }
                $rows[] = $filledRow;
            }
        }

        return $rows;
    }

    /**
     * Konversi huruf kolom Excel ke indeks 0-based (A -> 0, B -> 1, Z -> 25, AA -> 26)
     */
    private function columnLetterToIndex(string $col): int
    {
        $col = strtoupper($col);
        $len = strlen($col);
        $index = 0;
        for ($i = 0; $i < $len; $i++) {
            $index = $index * 26 + (ord($col[$i]) - ord('A') + 1);
        }
        return $index - 1;
    }

    /**
     * Memproses baris-baris data dari Excel/CSV ke database
     */
    private function processDataRows(array $rows): array
    {
        $ruasService      = new RuasService();
        $stripmapService  = new StripmapService();
        $perkerasanService= new PerkerasanService();

        $currentKoridor = null;
        $colMap = [];
        $processedCount = 0;
        $errors = [];

        // Deteksi header HANYA dari 10 baris pertama untuk menghindari baris ringkasan/total di bawah me-overwrite colMap
        $maxHeaderRows = min(10, count($rows));
        for ($i = 0; $i < $maxHeaderRows; $i++) {
            if ($this->isHeaderRow($rows[$i])) {
                $foundMap = $this->detectColumnIndices($rows[$i]);
                $colMap   = array_merge($colMap, $foundMap);
            }
        }

        // Fallback default jika format rekap standar BMBK Surkon (A=0, C=2, D=3, O=14, P=15, Q=16, R=17, S=18, T=19, U=20, V=21)
        if (!isset($colMap['kode_ruas'])) $colMap['kode_ruas'] = 0;
        if (!isset($colMap['nama_ruas'])) $colMap['nama_ruas'] = 2;
        if (!isset($colMap['panjang']))   $colMap['panjang']   = 3;
        if (!isset($colMap['rigid']))     $colMap['rigid']     = 14;
        if (!isset($colMap['aspal']))     $colMap['aspal']     = 15;
        if (!isset($colMap['telford']))   $colMap['telford']   = 16;
        if (!isset($colMap['tanah']))     $colMap['tanah']     = 17;
        if (!isset($colMap['baik']))      $colMap['baik']      = 18;
        if (!isset($colMap['sedang']))    $colMap['sedang']    = 19;
        if (!isset($colMap['rusak_ringan'])) $colMap['rusak_ringan'] = 20;
        if (!isset($colMap['rusak_berat']))  $colMap['rusak_berat']  = 21;

        foreach ($rows as $rowIndex => $row) {
            $rowString = implode(' ', array_filter($row));

            // 1. Deteksi Baris Header Koridor (Contoh: "KORIDOR 1", "KORIDOR 2")
            if (preg_match('/KORIDOR\s+\d+/i', $rowString, $matches)) {
                $currentKoridor = strtoupper(trim($matches[0]));
                continue;
            }

            // Abaikan baris total / summary ringkasan di bawah
            if (preg_match('/TOTAL\s+PANJANG|REKAPITULASI|JUMLAH/i', $rowString)) {
                continue;
            }

            // 2. Baca Data Baris
            $kodeRuasBase = trim($row[$colMap['kode_ruas']] ?? '');
            $namaRuas     = trim($row[$colMap['nama_ruas']] ?? '');

            // Abaikan baris header atau baris kosong
            if (empty($kodeRuasBase) || empty($namaRuas) || preg_match('/nmr|kode|ruas/i', $kodeRuasBase) || preg_match('/nama|ruas/i', $namaRuas)) {
                continue;
            }

            // Gabungkan sub-kode jika ada di kolom sebelahnya (misal "012" + "11K" -> "012 11K")
            $subKodeCol = $colMap['kode_ruas'] + 1;
            $subKode    = isset($row[$subKodeCol]) ? trim($row[$subKodeCol]) : '';
            if (!empty($subKode) && preg_match('/^[0-9A-Za-z]+$/', $subKode) && strlen($subKode) <= 5 && !preg_match('/nama|panjang|sk/i', $subKode)) {
                $kodeRuasFull = $kodeRuasBase . ' ' . $subKode;
            } else {
                $kodeRuasFull = $kodeRuasBase;
            }

            $kodeRuas = $this->formatKodeRuas($kodeRuasFull);

            // Parsing Panjang (SK)
            $panjangKm = isset($colMap['panjang']) ? $this->parseFloatVal($row[$colMap['panjang']] ?? '0') : 0;
            $panjangMeter = round($panjangKm * 1000, 2);

            // Extract lokasi / kabupaten_kota (dari kolom terdeteksi atau teks dalam tanda kurung pada nama_ruas)
            $kabupatenKota = isset($colMap['kabupaten_kota']) 
                ? trim($row[$colMap['kabupaten_kota']] ?? '') 
                : $this->extractKabupatenKota($namaRuas);

            // Parsing Perkerasan (Km -> meter)
            $rigidKm   = isset($colMap['rigid']) ? $this->parseFloatVal($row[$colMap['rigid']] ?? '0') : 0;
            $aspalKm   = isset($colMap['aspal']) ? $this->parseFloatVal($row[$colMap['aspal']] ?? '0') : 0;
            $telfordKm = isset($colMap['telford']) ? $this->parseFloatVal($row[$colMap['telford']] ?? '0') : 0;
            $tanahKm   = isset($colMap['tanah']) ? $this->parseFloatVal($row[$colMap['tanah']] ?? '0') : 0;
            $agregatTanahKm = $telfordKm + $tanahKm;

            // Parsing Kondisi (Km -> meter)
            $baikKm        = isset($colMap['baik']) ? $this->parseFloatVal($row[$colMap['baik']] ?? '0') : 0;
            $sedangKm      = isset($colMap['sedang']) ? $this->parseFloatVal($row[$colMap['sedang']] ?? '0') : 0;
            $rusakRinganKm = isset($colMap['rusak_ringan']) ? $this->parseFloatVal($row[$colMap['rusak_ringan']] ?? '0') : 0;
            $rusakBeratKm  = isset($colMap['rusak_berat']) ? $this->parseFloatVal($row[$colMap['rusak_berat']] ?? '0') : 0;

            // Upsert Ruas Jalan
            $existingRuas = $ruasService->findByKode($kodeRuas);
            $ruasData = [
                'kode_ruas' => $kodeRuas,
                'nama_ruas' => $namaRuas,
                'sta_awal'  => 0,
                'sta_akhir' => $panjangMeter,
                'panjang'   => $panjangMeter,
                'koridor'   => $currentKoridor ?? ($existingRuas['koridor'] ?? null),
                'kabupaten_kota' => !empty($kabupatenKota) ? $kabupatenKota : ($existingRuas['kabupaten_kota'] ?? null),
            ];

            if ($existingRuas) {
                $ruasId = (int)$existingRuas['id'];
                $ruasService->update($ruasId, $ruasData);
            } else {
                $createRes = $ruasService->create($ruasData);
                if (!$createRes['success']) {
                    $errors[] = "Gagal membuat ruas {$kodeRuas}: " . $createRes['message'];
                    continue;
                }
                $ruasId = (int)$createRes['id'];
            }

            // Hapus data stripmap & perkerasan lama untuk ruas ini jika merekap ulang
            $stripmapService->deleteByRuasId($ruasId);
            $perkerasanService->deleteByRuasId($ruasId);

            // Total Meter Kondisi & Perkerasan
            $baikM   = round($baikKm * 1000, 2);
            $sedangM = round($sedangKm * 1000, 2);
            $rrM     = round($rusakRinganKm * 1000, 2);
            $rbM     = round($rusakBeratKm * 1000, 2);
            $totalKondisiM = $baikM + $sedangM + $rrM + $rbM;

            $rigidM    = round($rigidKm * 1000, 2);
            $aspalM    = round($aspalKm * 1000, 2);
            $agregatM  = round($telfordKm * 1000 + $tanahKm * 1000, 2);
            $totalPerkerasanM = $rigidM + $aspalM + $agregatM;

            // Simpan Data Strip Map Baru (1 Rekap Segmen Utuh)
            if ($totalKondisiM > 0) {
                $stripmapService->batchCreate($ruasId, [[
                    'sta_awal'     => 0,
                    'sta_akhir'    => $totalKondisiM,
                    'baik'         => $baikM,
                    'sedang'       => $sedangM,
                    'rusak_ringan' => $rrM,
                    'rusak_berat'  => $rbM,
                ]]);
            }

            // Simpan Data Perkerasan Baru (1 Rekap Segmen Utuh)
            if ($totalPerkerasanM > 0) {
                $perkerasanService->batchCreate($ruasId, [[
                    'sta_awal'      => 0,
                    'sta_akhir'     => $totalPerkerasanM,
                    'rigid'         => $rigidM,
                    'aspal'         => $aspalM,
                    'agregat_tanah' => $agregatM,
                    'belum_tembus'  => 0,
                ]]);
            }

            // Sinkronkan STA
            $ruasService->syncStaFromStripmap($ruasId);
            $processedCount++;
        }

        return [
            'success' => $processedCount > 0,
            'message' => "Berhasil mengimpor {$processedCount} data ruas jalan beserta kondisi dan perkerasan.",
            'count'   => $processedCount,
            'errors'  => $errors,
        ];
    }

    /**
     * Ekstrak nama Kabupaten / Kota dari string nama ruas (misal dalam tanda kurung)
     */
    private function extractKabupatenKota(string $namaRuas): ?string
    {
        if (preg_match('/\(([^)]+)\)/', $namaRuas, $matches)) {
            $inner = trim($matches[1]);
            if (!preg_match('/KOR\s*\d+/i', $inner)) {
                return ucwords(strtolower($inner));
            }
        }
        return null;
    }

    /**
     * Cek apakah baris merupakan header tabel
     */
    private function isHeaderRow(array $row): bool
    {
        $text = strtolower(implode(' ', $row));
        $keywords = ['nmr', 'kode', 'nama', 'panjang', 'rigid', 'aspal', 'lapen', 'telford', 'kerikil', 'tanah', 'baik', 'sedang', 'rusak', 'mantap'];
        foreach ($keywords as $kw) {
            if (str_contains($text, $kw)) return true;
        }
        return false;
    }

    /**
     * Deteksi posisi indeks tiap kolom secara dinamis berdasarkan kata kunci header
     */
    private function detectColumnIndices(array $row): array
    {
        $map = [];
        foreach ($row as $index => $colName) {
            $clean = strtolower(trim(str_replace(["\r", "\n"], ' ', $colName)));

            if (str_contains($clean, 'nmr') || str_contains($clean, 'kode') || $clean === 'no' || str_contains($clean, 'nomor')) {
                $map['kode_ruas'] = $index;
            } elseif (str_contains($clean, 'nama')) {
                $map['nama_ruas'] = $index;
            } elseif (str_contains($clean, 'panjang sk') || str_contains($clean, 'panjang (km)') || ($clean === 'panjang' && !isset($map['panjang']))) {
                $map['panjang'] = $index;
            } elseif (str_contains($clean, 'rigid')) {
                $map['rigid'] = $index;
            } elseif (str_contains($clean, 'aspal') || str_contains($clean, 'lapen')) {
                $map['aspal'] = $index;
            } elseif (str_contains($clean, 'telford') || str_contains($clean, 'kerikil')) {
                $map['telford'] = $index;
            } elseif (str_contains($clean, 'tanah')) {
                $map['tanah'] = $index;
            } elseif (str_contains($clean, 'baik') && ($index >= 14 || !isset($map['baik']))) {
                $map['baik'] = $index;
            } elseif (str_contains($clean, 'sedang') && ($index >= 14 || !isset($map['sedang']))) {
                $map['sedang'] = $index;
            } elseif ((str_contains($clean, 'rusak ringan') || str_contains($clean, 'r.ringan')) && ($index >= 14 || !isset($map['rusak_ringan']))) {
                $map['rusak_ringan'] = $index;
            } elseif ((str_contains($clean, 'rusak berat') || str_contains($clean, 'r.berat')) && ($index >= 14 || !isset($map['rusak_berat']))) {
                $map['rusak_berat'] = $index;
            } elseif (str_contains($clean, 'kabupaten') || str_contains($clean, 'kota') || str_contains($clean, 'wilayah')) {
                $map['kabupaten_kota'] = $index;
            }
        }
        return $map;
    }

    /**
     * Standardisasi string kode ruas (misal angka 33 -> 033)
     */
    private function formatKodeRuas(string $val): string
    {
        $val = trim($val);
        if (is_numeric($val) && strlen($val) < 3) {
            return str_pad($val, 3, '0', STR_PAD_LEFT);
        }
        return $val;
    }

    /**
     * Parsing nilai float (mengubah koma ',' menjadi titik '.')
     */
    private function parseFloatVal(string $val): float
    {
        $val = trim($val);
        if ($val === '' || $val === '-') return 0.0;

        if (str_contains($val, '.') && str_contains($val, ',')) {
            $val = str_replace('.', '', $val);
            $val = str_replace(',', '.', $val);
        } elseif (str_contains($val, ',')) {
            $val = str_replace(',', '.', $val);
        }
        return is_numeric($val) ? (float)$val : 0.0;
    }
}
