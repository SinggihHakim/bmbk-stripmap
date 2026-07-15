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

        if ($ext === 'xlsx') {
            $zip = new ZipArchive();
            if ($zip->open($filePath) === true) {
                $sheetMapping = $this->getWorksheetsMapping($zip);
                
                $aspalSheet = null;
                $betonSheet = null;
                $nonAspalSheet = null;
                foreach ($sheetMapping as $sheetName => $xmlPath) {
                    $lowerName = strtolower($sheetName);
                    if (str_contains($lowerName, 'rekap') && str_contains($lowerName, 'aspal') && !str_contains($lowerName, 'non')) {
                        $aspalSheet = $xmlPath;
                    } elseif (str_contains($lowerName, 'rekap') && str_contains($lowerName, 'beton')) {
                        $betonSheet = $xmlPath;
                    } elseif (str_contains($lowerName, 'rekap') && str_contains($lowerName, 'non') && str_contains($lowerName, 'aspal')) {
                        $nonAspalSheet = $xmlPath;
                    }
                }
                
                if ($aspalSheet !== null || $betonSheet !== null || $nonAspalSheet !== null) {
                    $result = $this->importDetailedSurvey($zip, $aspalSheet, $betonSheet, $nonAspalSheet);
                    $zip->close();
                    return $result;
                }
                $zip->close();
            }
        }

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
        $currentKabupaten = null;
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

            // 1b. Deteksi Baris Header Kabupaten/Kota (Contoh: "KABUPATEN PRINGSEWU", "KOTA BANDAR LAMPUNG")
            $firstCol = trim($row[0] ?? '');
            $isEmptyRest = empty(trim(implode('', array_slice($row, 1))));
            if ($isEmptyRest && preg_match('/^(KABUPATEN|KOTA)\s+[A-Z\s]+$/i', $firstCol)) {
                $currentKabupaten = ucwords(strtolower($firstCol));
                continue;
            }

            // Abaikan baris total / summary ringkasan di bawah
            if (preg_match('/TOTAL\s+PANJANG|REKAPITULASI|JUMLAH|SUB TOTAL/i', $rowString)) {
                continue;
            }

            // 2. Baca Data Baris
            $kodeRuasBase = trim($row[$colMap['kode_ruas']] ?? '');
            $namaRuas     = trim($row[$colMap['nama_ruas']] ?? '');

            // Abaikan baris header atau baris kosong
            if (empty($kodeRuasBase) || empty($namaRuas) || preg_match('/nmr|kode|ruas/i', $kodeRuasBase) || preg_match('/nama|ruas/i', $namaRuas)) {
                continue;
            }

            // Abaikan jika nama ruas hanya berupa angka (misal baris petunjuk kolom 1, 2, 3...)
            if (is_numeric($namaRuas)) {
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
                'kabupaten_kota' => !empty($kabupatenKota) ? $kabupatenKota : ($currentKabupaten ?? ($existingRuas['kabupaten_kota'] ?? null)),
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
     * Dapatkan Kabupaten / Kota berdasarkan daftar pemetaan nama ruas jalan BMBK Lampung
     */
    private function getKabupatenFromMapping(string $namaRuas): ?string
    {
        $normalized = strtoupper(trim(preg_replace('/\s+/', ' ', $namaRuas)));

        $mapping = [
            'JALAN MAYJEN. H.M. RYACUDU (BANDAR LAMPUNG)' => 'Kota Bandar Lampung',
            'JALAN TENGGIRI (BANDAR LAMPUNG)' => 'Kota Bandar Lampung',
            'JALAN R.E. MARTADINATA (BANDAR LAMPUNG)' => 'Kota Bandar Lampung',
            'KALIREJO - PRINGSEWU' => 'Kabupaten Pringsewu',
            'PRINGSEWU - PARDASUKA' => 'Kabupaten Pringsewu',
            'PARDASUKA - SUKAMARA' => 'Kabupaten Pringsewu',
            'BRANTI - GEDONG TATAAN' => 'Kabupaten Pesawaran',
            'GEDONG TATAAN - KEDONDONG' => 'Kabupaten Pesawaran',
            'KEDONDONG - PARDASUKA' => 'Kabupaten Pesawaran',
            'PADANG CERMIN - KEDONDONG' => 'Kabupaten Pesawaran',
            'LEMPASING - PADANG CERMIN' => 'Kabupaten Pesawaran',
            'PADANG CERMIN - SP TELUK KILUAN' => 'Kabupaten Pesawaran',
            'JALAN ZAINAL ABIDIN PAGARALAM (KALIANDA)' => 'Kabupaten Lampung Selatan',
            'KALIANDA - KUNYIR - GAYAM' => 'Kabupaten Lampung Selatan',
            'GAYAM - KETAPANG' => 'Kabupaten Lampung Selatan',
            'SP. SIDOMULYO - BELIMBING SARI' => 'Kabupaten Lampung Selatan',
            'SP. KORPRI - SUKADAMAI' => 'Kabupaten Lampung Selatan',
            'SP. KORPRI - PURWOTANI' => 'Kabupaten Lampung Selatan',
            'BELIMBING SARI - JABUNG' => 'Kabupaten Lampung Timur',
            'JABUNG - SP. LABUHAN MARINGGAI' => 'Kabupaten Lampung Timur',
            'METRO - TANJUNG KARI' => 'Kabupaten Lampung Timur',
            'NYAMPIR - TANJUNG KARI' => 'Kabupaten Lampung Timur',
            'TANJUNG KARI - PUGUNG RAHARJO' => 'Kabupaten Lampung Timur',
            'PUGUNG RAHARJO - JABUNG' => 'Kabupaten Lampung Timur',
            'SUKADAMAI - KIBANG' => 'Kabupaten Lampung Timur',
            'KOTA GAJAH - GEDONG DALEM' => 'Kabupaten Lampung Timur',
            'METRO - KOTA GAJAH' => 'Kabupaten Lampung Tengah',
            'KOTA GAJAH - SP. RANDU' => 'Kabupaten Lampung Tengah',
            'SP. RANDU - SEPUTIH SURABAYA' => 'Kabupaten Lampung Tengah',
            'SEPUTIH SURABAYA - SADEWA' => 'Kabupaten Lampung Tengah',
            'BANDAR JAYA - SP. MANDALA' => 'Kabupaten Lampung Tengah',
            'GUNUNG SUGIH - KOTA GAJAH' => 'Kabupaten Lampung Tengah',
            'KALIREJO - BANGUNREJO' => 'Kabupaten Lampung Tengah',
            'BANGUNREJO - WATES' => 'Kabupaten Lampung Tengah',
            'WATES - METRO' => 'Kabupaten Lampung Tengah',
            'GUNUNG SUGIH - PADANG RATU' => 'Kabupaten Lampung Tengah',
            'PADANG RATU - PEKURUN UDIK' => 'Kabupaten Lampung Tengah',
            'PADANG RATU - KALIREJO' => 'Kabupaten Lampung Tengah',
            'JALAN AHMAD YANI (METRO)' => 'Kota Metro',
            'JALAN BUDI UTOMO (METRO)' => 'Kota Metro',
            'JALAN SOEKARNO HATTA (METRO)' => 'Kota Metro',
            'JALAN VETERAN (METRO)' => 'Kota Metro',
            'JALAN PATTIMURA (METRO)' => 'Kota Metro',
            'JALAN BRIGJEN. KATAMSO (METRO)' => 'Kota Metro',
            'PEKURUN UDIK - AJI KAGUNGAN' => 'Kabupaten Lampung Utara',
            'TAMAN SISWA - RAJA ASLI' => 'Kabupaten Lampung Utara',
            'KOTA BUMI - BANDAR ABUNG' => 'Kabupaten Lampung Utara',
            'BANDAR ABUNG - BANDAR SAKTI' => 'Kabupaten Lampung Utara',
            'BANDAR ABUNG - SP. TUJOK' => 'Kabupaten Lampung Utara',
            'NEGARA RATU - SP. TUJOK' => 'Kabupaten Lampung Utara',
            'KOTA BUMI - KETAPANG' => 'Kabupaten Lampung Utara',
            'KETAPANG - NEGARA RATU' => 'Kabupaten Lampung Utara',
            'NEGARA RATU - GUNUNG BETUAH' => 'Kabupaten Lampung Utara',
            'NEGARA RATU - SP. SOPONYONO' => 'Kabupaten Lampung Utara',
            'GUNUNG BETUAH - GN LABUAN' => 'Kabupaten Way Kanan',
            'SP. EMPAT - KASUI' => 'Kabupaten Way Kanan',
            'KASUI - AI RINGKIH (BTS SUMATERA SELATAN)' => 'Kabupaten Way Kanan',
            'SP. EMPAT - BLAMBANGAN UMPU' => 'Kabupaten Way Kanan',
            'BLAMBANGAN UMPU - SRI REJEKI' => 'Kabupaten Way Kanan',
            'SRI REJEKI - PAKUAN RATU' => 'Kabupaten Way Kanan',
            'PAKUAN RATU - BUMIHARJO' => 'Kabupaten Way Kanan',
            'BUMIHARJO - SP. WAY TUBA' => 'Kabupaten Way Kanan',
            'SP. SOPONYONO - SERUPA INDAH' => 'Kabupaten Way Kanan',
            'SERUPA INDAH - PAKUAN RATU' => 'Kabupaten Way Kanan',
            'SERUPA INDAH - TAJAB' => 'Kabupaten Way Kanan',
            'TEGAL MUKTI - TAJAB' => 'Kabupaten Way Kanan',
            'TAJAB - ADIJAYA' => 'Kabupaten Way Kanan',
            'SUKAMARA - KURIPAN' => 'Kabupaten Tanggamus',
            'SP TELUK KILUAN - SP. UMBAR' => 'Kabupaten Tanggamus',
            'SP. UMBAR - PUTIH DOH' => 'Kabupaten Tanggamus',
            'PUTIH DOH - KURIPAN' => 'Kabupaten Tanggamus',
            'KURIPAN - SP. KOTA AGUNG' => 'Kabupaten Tanggamus',
            'SP. BLOK 9 - SANGGI' => 'Kabupaten Tanggamus',
            'TALANG PADANG - NGARIP' => 'Kabupaten Tanggamus',
            'NGARIP - ULU SEMONG' => 'Kabupaten Tanggamus',
            'ULU SEMONG - SP. TRIMULYO' => 'Kabupaten Tanggamus',
            'TEKAD - BATUTEGI' => 'Kabupaten Tanggamus',
            'PEKON BALAK - SUOH' => 'Kabupaten Lampung Barat',
            'SUOH - SP. BLOK 9' => 'Kabupaten Lampung Barat',
            'JALAN RADEN INTAN (LIWA)' => 'Kabupaten Lampung Barat',
            'LIWA - BTS. SUMATERA SELATAN' => 'Kabupaten Lampung Barat',
            'SP. TRIMULYO - BUNGIN - SP. TUGU SARI' => 'Kabupaten Lampung Barat',
            'JALAN ADAM MALIK (KRUI)' => 'Kabupaten Pesisir Barat',
            'KRUI - PEKON SERAI' => 'Kabupaten Pesisir Barat',
            'KOTAJAWA - KAMPUNG BARU' => 'Kabupaten Pesisir Barat',
            'JALAN RAYA GUNUNG SAKTI (MENGGALA)' => 'Kabupaten Tulang Bawang',
            'BUJUNG TENUK - PENUMANGAN' => 'Kabupaten Tulang Bawang',
            'SP. UNIT VIII - GEDONG AJI' => 'Kabupaten Tulang Bawang',
            'GEDONG AJI - UMBUL MESIR' => 'Kabupaten Tulang Bawang',
            'BANDAR SAKTI - SP. DAYA MURNI' => 'Kabupaten Tulang Bawang Barat',
            'SP. DAYA MURNI - GUNUNG BATIN' => 'Kabupaten Tulang Bawang Barat',
            'SP. TUJOK - PANARAGAN JAYA' => 'Kabupaten Tulang Bawang Barat',
            'PANARAGAN JAYA - SP. PANARAGAN' => 'Kabupaten Tulang Bawang Barat',
            'PENUMANGAN - TEGAL MUKTI' => 'Kabupaten Tulang Bawang Barat',
            'ADIJAYA - TULUNG RANDU' => 'Kabupaten Tulang Bawang Barat',
            'PENUMANGAN - UNIT VI' => 'Kabupaten Tulang Bawang Barat',
            'SP. PEMATANG - BRABASAN' => 'Kabupaten Mesuji',
            'BRABASAN - WIRALAGA' => 'Kabupaten Mesuji',
        ];

        return $mapping[$normalized] ?? null;
    }

    /**
     * Ekstrak nama Kabupaten / Kota dari string nama ruas
     */
    private function extractKabupatenKota(string $namaRuas): ?string
    {
        // 1. Cek dari pemetaan presisi BMBK Lampung
        $mapped = $this->getKabupatenFromMapping($namaRuas);
        if ($mapped) {
            return $mapped;
        }

        // 2. Cek text di dalam tanda kurung
        if (preg_match('/\(([^)]+)\)/', $namaRuas, $matches)) {
            $inner = trim($matches[1]);
            if (!preg_match('/KOR\s*\d+/i', $inner)) {
                $innerClean = strtolower($inner);
                if ($innerClean === 'kalianda') {
                    return 'Kabupaten Lampung Selatan';
                }
                if ($innerClean === 'liwa') {
                    return 'Kabupaten Lampung Barat';
                }
                if ($innerClean === 'krui') {
                    return 'Kabupaten Pesisir Barat';
                }
                if ($innerClean === 'menggala') {
                    return 'Kabupaten Tulang Bawang';
                }
                return ucwords(strtolower($inner));
            }
        }

        // 3. Cek kata kunci dalam nama jalan
        $namaLower = strtolower($namaRuas);
        
        if (str_contains($namaLower, 'pringsewu') || str_contains($namaLower, 'pardasuka')) {
            return 'Kabupaten Pringsewu';
        }
        if (str_contains($namaLower, 'gedong tataan') || str_contains($namaLower, 'kedondong') || str_contains($namaLower, 'lempasing') || str_contains($namaLower, 'padang cermin')) {
            return 'Kabupaten Pesawaran';
        }
        if (str_contains($namaLower, 'kalianda') || str_contains($namaLower, 'gayam') || str_contains($namaLower, 'ketapang') || str_contains($namaLower, 'sidomulyo') || str_contains($namaLower, 'korpri') || str_contains($namaLower, 'purwotani')) {
            return 'Kabupaten Lampung Selatan';
        }
        if (str_contains($namaLower, 'belimbing sari') || str_contains($namaLower, 'jabung') || str_contains($namaLower, 'maringgai') || str_contains($namaLower, 'tanjung kari') || str_contains($namaLower, 'nyampir') || str_contains($namaLower, 'pugung raharjo') || str_contains($namaLower, 'kibang') || str_contains($namaLower, 'gedong dalem')) {
            return 'Kabupaten Lampung Timur';
        }
        if (str_contains($namaLower, 'kota gajah') || str_contains($namaLower, 'kalirejo') || str_contains($namaLower, 'bangunrejo') || str_contains($namaLower, 'wates') || str_contains($namaLower, 'seputih surabaya') || str_contains($namaLower, 'gunung sugih') || str_contains($namaLower, 'padang ratu')) {
            return 'Kabupaten Lampung Tengah';
        }
        if (str_contains($namaLower, 'bandar lampung') || str_contains($namaLower, 'ryacudu') || str_contains($namaLower, 'tenggiri') || str_contains($namaLower, 'martadinata')) {
            return 'Kota Bandar Lampung';
        }
        if (str_contains($namaLower, 'metro')) {
            return 'Kota Metro';
        }
        if (str_contains($namaLower, 'way kanan') || str_contains($namaLower, 'kasui') || str_contains($namaLower, 'blambangan umpu') || str_contains($namaLower, 'pakuan ratu')) {
            return 'Kabupaten Way Kanan';
        }
        if (str_contains($namaLower, 'tanggamus') || str_contains($namaLower, 'kiluan') || str_contains($namaLower, 'putih doh') || str_contains($namaLower, 'kuripan') || str_contains($namaLower, 'talang padang') || str_contains($namaLower, 'batutegi')) {
            return 'Kabupaten Tanggamus';
        }
        if (str_contains($namaLower, 'liwa') || str_contains($namaLower, 'suoh')) {
            return 'Kabupaten Lampung Barat';
        }
        if (str_contains($namaLower, 'krui') || str_contains($namaLower, 'kotajawa')) {
            return 'Kabupaten Pesisir Barat';
        }
        if (str_contains($namaLower, 'menggala') || str_contains($namaLower, 'bujung tenuk')) {
            return 'Kabupaten Tulang Bawang';
        }
        if (str_contains($namaLower, 'panaragan') || str_contains($namaLower, 'tegal mukti') || str_contains($namaLower, 'adijaya') || str_contains($namaLower, 'tulung randu')) {
            return 'Kabupaten Tulang Bawang Barat';
        }
        if (str_contains($namaLower, 'mesuji') || str_contains($namaLower, 'pematang') || str_contains($namaLower, 'brabasan') || str_contains($namaLower, 'wiralaga')) {
            return 'Kabupaten Mesuji';
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

            if (str_contains($clean, 'nmr') || str_contains($clean, 'nomor ruas') || $clean === 'no' || (str_contains($clean, 'link') && !str_contains($clean, 'sub')) || (str_contains($clean, 'kode') && !str_contains($clean, 'sub'))) {
                $map['kode_ruas'] = $index;
            } elseif (str_contains($clean, 'nama')) {
                $map['nama_ruas'] = $index;
            } elseif (str_contains($clean, 'panjang') && (str_contains($clean, 'ruas') || str_contains($clean, 'sk') || (!str_contains($clean, 'jenis') && !str_contains($clean, 'kondisi') && !str_contains($clean, 'tiap')))) {
                $map['panjang'] = $index;
            } elseif (str_contains($clean, 'rigid') || str_contains($clean, 'beton')) {
                $map['rigid'] = $index;
            } elseif (str_contains($clean, 'aspal') || str_contains($clean, 'lapen')) {
                $map['aspal'] = $index;
            } elseif (str_contains($clean, 'telford') || str_contains($clean, 'kerikil')) {
                $map['telford'] = $index;
            } elseif (str_contains($clean, 'tanah')) {
                $map['tanah'] = $index;
            } elseif (str_contains($clean, 'baik') && ($index >= 8 || !isset($map['baik']))) {
                $map['baik'] = $index;
            } elseif (str_contains($clean, 'sedang') && ($index >= 8 || !isset($map['sedang']))) {
                $map['sedang'] = $index;
            } elseif ((str_contains($clean, 'rusak ringan') || str_contains($clean, 'r.ringan')) && ($index >= 8 || !isset($map['rusak_ringan']))) {
                $map['rusak_ringan'] = $index;
            } elseif ((str_contains($clean, 'rusak berat') || str_contains($clean, 'r.berat')) && ($index >= 8 || !isset($map['rusak_berat']))) {
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
     * Get mapping of sheet names to internal XML target paths
     */
    private function getWorksheetsMapping(ZipArchive $zip): array
    {
        $sheetMapping = [];
        $workbookXml = $zip->getFromName('xl/workbook.xml');
        $relsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');
        if (!$workbookXml || !$relsXml) {
            return [];
        }

        $wb = simplexml_load_string($workbookXml);
        $rels = simplexml_load_string($relsXml);
        if (!$wb || !$rels) {
            return [];
        }

        $rIdToTarget = [];
        foreach ($rels->Relationship as $rel) {
            $id = (string)$rel['Id'];
            $target = (string)$rel['Target'];
            $rIdToTarget[$id] = $target;
        }

        $namespaces = $wb->getNamespaces(true);
        $sheets = isset($wb->sheets->sheet) ? $wb->sheets->sheet : $wb->xpath('//sheet');
        if ($sheets) {
            foreach ($sheets as $sheet) {
                $name = (string)$sheet['name'];
                $rIdAttr = null;
                if (isset($namespaces['r'])) {
                    $rIdAttr = (string)$sheet->attributes($namespaces['r'])['id'];
                }
                if (!$rIdAttr) {
                    $rIdAttr = (string)$sheet['id'];
                }

                if ($rIdAttr && isset($rIdToTarget[$rIdAttr])) {
                    $targetPath = $rIdToTarget[$rIdAttr];
                    if (strpos($targetPath, '/') === 0) {
                        $targetPath = substr($targetPath, 1);
                    }
                    if (strpos($targetPath, 'xl/') !== 0 && strpos($targetPath, 'worksheets/') === 0) {
                        $targetPath = 'xl/' . $targetPath;
                    }
                    $sheetMapping[trim($name)] = $targetPath;
                }
            }
        }

        return $sheetMapping;
    }

    /**
     * Parse single worksheet XML to array of rows and columns
     */
    private function parseWorksheet(ZipArchive $zip, string $xmlPath, array $sharedStrings): array
    {
        $sheetXml = $zip->getFromName($xmlPath);
        if (!$sheetXml) return [];
        $xml = simplexml_load_string($sheetXml);
        if (!$xml || !isset($xml->sheetData->row)) return [];

        $rows = [];
        foreach ($xml->sheetData->row as $row) {
            $rowNum = (int)$row['r'];
            $rowData = [];
            foreach ($row->c as $c) {
                $rAttr = (string)$c['r'];
                preg_match('/^[A-Z]+/', $rAttr, $matches);
                $colLetter = $matches[0];
                $colIndex = $this->columnLetterToIndex($colLetter);

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
                $rows[$rowNum] = $filledRow;
            }
        }

        return $rows;
    }

    /**
     * Find value in rows based on label search followed by a colon
     */
    private function findMetaValue(array $rows, string $label): ?string
    {
        foreach ($rows as $rData) {
            $rowStr = implode(' ', $rData);
            if (stripos($rowStr, $label) !== false) {
                $colonIndex = -1;
                foreach ($rData as $idx => $val) {
                    if (trim($val) === ':') {
                        $colonIndex = $idx;
                        break;
                    }
                }
                if ($colonIndex !== -1) {
                    for ($k = $colonIndex + 1; $k < count($rData); $k++) {
                        if (trim($rData[$k]) !== '') {
                            return trim($rData[$k]);
                        }
                    }
                }
            }
        }
        return null;
    }

    /**
     * Import detailed survey worksheet (Rekap Back Up Aspal/Beton/Non-Aspal)
     */
    private function importDetailedSurvey(ZipArchive $zip, ?string $aspalSheet, ?string $betonSheet, ?string $nonAspalSheet): array
    {
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

        // 2. Parse worksheets
        $aspalRows = $aspalSheet ? $this->parseWorksheet($zip, $aspalSheet, $sharedStrings) : null;
        $betonRows = $betonSheet ? $this->parseWorksheet($zip, $betonSheet, $sharedStrings) : null;
        $nonAspalRows = $nonAspalSheet ? $this->parseWorksheet($zip, $nonAspalSheet, $sharedStrings) : null;

        // 3. Extract Metadata
        $metaRows = $aspalRows ?: ($betonRows ?: $nonAspalRows);
        if (empty($metaRows)) {
            return [
                'success' => false,
                'message' => 'Gagal membaca data dari lembar detail survey.',
                'count'   => 0
            ];
        }

        $kodeRuasRaw = $this->findMetaValue($metaRows, 'NO. RUAS');
        $namaRuas = $this->findMetaValue($metaRows, 'NAMA RUAS');
        $panjangRuasRaw = $this->findMetaValue($metaRows, 'PANJANG RUAS');

        if (empty($kodeRuasRaw) || empty($namaRuas)) {
            return [
                'success' => false,
                'message' => 'Gagal mengekstrak metadata ruas (Nomor Ruas / Nama Ruas tidak ditemukan).',
                'count'   => 0
            ];
        }

        $kodeRuas = $this->formatKodeRuas($kodeRuasRaw);
        $panjangMeter = round($this->parseFloatVal($panjangRuasRaw) * 1000, 2);
        $kabupatenKota = $this->extractKabupatenKota($namaRuas);

        $ruasService = new RuasService();
        $existingRuas = $ruasService->findByKode($kodeRuas);

        $ruasData = [
            'kode_ruas' => $kodeRuas,
            'nama_ruas' => $namaRuas,
            'sta_awal'  => 0,
            'sta_akhir' => $panjangMeter,
            'panjang'   => $panjangMeter,
            'kabupaten_kota' => $kabupatenKota,
        ];

        if ($existingRuas) {
            $ruasId = (int)$existingRuas['id'];
            if (!empty($existingRuas['koridor'])) {
                $ruasData['koridor'] = $existingRuas['koridor'];
            }
            $ruasService->update($ruasId, $ruasData);
        } else {
            $createRes = $ruasService->create($ruasData);
            if (!$createRes['success']) {
                return [
                    'success' => false,
                    'message' => "Gagal membuat ruas jalan {$kodeRuas}: " . $createRes['message'],
                    'count'   => 0
                ];
            }
            $ruasId = (int)$createRes['id'];
        }

        // Hapus data stripmap & perkerasan lama
        $db = Database::getInstance()->getConnection();
        $db->prepare("DELETE FROM stripmap WHERE ruas_id = ?")->execute([$ruasId]);
        $db->prepare("DELETE FROM perkerasan WHERE ruas_id = ?")->execute([$ruasId]);

        $insertedCount = 0;
        
        $stmtStripmap = $db->prepare("INSERT INTO stripmap (ruas_id, sta_awal, sta_akhir, panjang, baik, sedang, rusak_ringan, rusak_berat) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmtPerkerasan = $db->prepare("INSERT INTO perkerasan (ruas_id, sta_awal, sta_akhir, panjang, rigid, aspal, agregat_tanah, belum_tembus) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        // 4. Proses Segmen
        $sheetsToProcess = [
            'aspal' => [$aspalRows, 'aspal'],
            'rigid' => [$betonRows, 'rigid'],
            'agregat_tanah' => [$nonAspalRows, 'agregat_tanah']
        ];

        foreach ($sheetsToProcess as $type => $info) {
            $rows = $info[0];
            if (empty($rows)) continue;

            $colMap = [];
            for ($i = 1; $i <= 25; $i++) {
                if (!isset($rows[$i])) continue;
                foreach ($rows[$i] as $idx => $val) {
                    $clean = strtolower(trim($val));
                    if ($clean === 'dari' || $clean === 'dari (m)' || $clean === 'dari ( m )' || str_contains($clean, 'sta awal') || str_contains($clean, 'sta. awal')) {
                        $colMap['sta_awal'] = $idx;
                    } elseif ($clean === 'ke' || $clean === 'ke (m)' || $clean === 'ke ( m )' || str_contains($clean, 'sta akhir') || str_contains($clean, 'sta. akhir')) {
                        $colMap['sta_akhir'] = $idx;
                    } elseif ($clean === 'lebar' || $clean === 'lebar (m)' || $clean === 'lebar ( m )') {
                        $colMap['lebar'] = $idx;
                    } elseif ($clean === 'kondisi') {
                        $colMap['kondisi'] = $idx;
                    }
                }
            }

            if (!isset($colMap['sta_awal']) || !isset($colMap['sta_akhir']) || !isset($colMap['kondisi'])) {
                continue;
            }

            foreach ($rows as $rData) {
                $staAwalVal = isset($rData[$colMap['sta_awal']]) ? trim($rData[$colMap['sta_awal']]) : '';
                $staAkhirVal = isset($rData[$colMap['sta_akhir']]) ? trim($rData[$colMap['sta_akhir']]) : '';
                $kondisiVal = isset($rData[$colMap['kondisi']]) ? trim($rData[$colMap['kondisi']]) : '';

                if ($staAwalVal === '' || $staAkhirVal === '' || $kondisiVal === '') continue;
                if (!is_numeric($staAwalVal) || !is_numeric($staAkhirVal)) continue;

                $staAwal = $this->parseFloatVal($staAwalVal);
                $staAkhir = $this->parseFloatVal($staAkhirVal);
                $panjang = $staAkhir - $staAwal;

                if ($panjang <= 0) continue;

                $baik = $sedang = $rr = $rb = 0;
                $cond = strtoupper(trim($kondisiVal));
                if ($cond === 'B') $baik = $panjang;
                elseif ($cond === 'S') $sedang = $panjang;
                elseif ($cond === 'RR' || $cond === 'R') $rr = $panjang;
                elseif ($cond === 'RB') $rb = $panjang;

                // Simpan ke stripmap
                $stmtStripmap->execute([$ruasId, $staAwal, $staAkhir, $panjang, $baik, $sedang, $rr, $rb]);

                // Simpan ke perkerasan
                $rigidVal = ($type === 'rigid') ? $panjang : 0;
                $aspalVal = ($type === 'aspal') ? $panjang : 0;
                $agregatVal = ($type === 'agregat_tanah') ? $panjang : 0;
                $stmtPerkerasan->execute([$ruasId, $staAwal, $staAkhir, $panjang, $rigidVal, $aspalVal, $agregatVal, 0]);

                $insertedCount++;
            }
        }

        // Sinkronkan STA
        $ruasService->syncStaFromStripmap($ruasId);

        return [
            'success' => true,
            'message' => "Berhasil mengimpor detail survey ruas jalan {$kodeRuas} ({$namaRuas}) sebanyak {$insertedCount} segmen.",
            'count'   => $insertedCount
        ];
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
