<?php

/**
 * ============================================================
 * Service: StripmapService
 * ============================================================
 * Business logic untuk Strip Map.
 */

class StripmapService
{
    private Stripmap  $model;
    private RuasJalan $ruasModel;

    public function __construct()
    {
        $this->model     = new Stripmap();
        $this->ruasModel = new RuasJalan();
    }

    /**
     * Ambil semua stripmap berdasarkan ruas ID
     */
    public function getByRuasId(int $ruasId): array
    {
        return $this->model->getByRuasId($ruasId);
    }

    /**
     * Ambil satu stripmap
     */
    public function findById(int $id): ?array
    {
        return $this->model->findById($id);
    }

    /**
     * Ambil ringkasan kondisi
     */
    public function getSummary(int $ruasId): array
    {
        return $this->model->getSummaryByRuasId($ruasId);
    }

    /**
     * Validasi & simpan stripmap baru
     */
    public function create(int $ruasId, array $input): array
    {
        $errors = $this->validate($input, $ruasId);
        if (!empty($errors)) {
            return ['success' => false, 'message' => implode('<br>', $errors)];
        }

        $staAwal  = sta_to_meter($input['sta_awal']);
        $staAkhir = sta_to_meter($input['sta_akhir']);
        $panjang  = $staAkhir - $staAwal;

        $id = $this->model->create([
            'ruas_id'      => $ruasId,
            'sta_awal'     => $staAwal,
            'sta_akhir'    => $staAkhir,
            'panjang'      => $panjang,
            'baik'         => (float) $input['baik'],
            'sedang'       => (float) $input['sedang'],
            'rusak_ringan' => (float) $input['rusak_ringan'],
            'rusak_berat'  => (float) $input['rusak_berat'],
        ]);

        return ['success' => true, 'message' => 'Data strip map berhasil ditambahkan.', 'id' => $id];
    }

    /**
     * Simpan banyak segmen sekaligus (batch insert)
     * Input: array of rows dari form multi-segmen
     */
    public function batchCreate(int $ruasId, array $rows): array
    {
        $errors = [];
        $clean  = [];

        foreach ($rows as $i => $row) {
            $rowErrors = $this->validate($row, $ruasId, null, false);
            if (!empty($rowErrors)) {
                foreach ($rowErrors as $e) {
                    $errors[] = "Baris " . ($i + 1) . ": $e";
                }
            } else {
                $staAwal  = sta_to_meter($row['sta_awal']);
                $staAkhir = sta_to_meter($row['sta_akhir']);
                $clean[]  = [
                    'original_index' => $i + 1,
                    'sta_awal_str'  => $row['sta_awal'],
                    'sta_akhir_str' => $row['sta_akhir'],
                    'ruas_id'      => $ruasId,
                    'sta_awal'     => $staAwal,
                    'sta_akhir'    => $staAkhir,
                    'panjang'      => $staAkhir - $staAwal,
                    'baik'         => (float) $row['baik'],
                    'sedang'       => (float) $row['sedang'],
                    'rusak_ringan' => (float) $row['rusak_ringan'],
                    'rusak_berat'  => (float) $row['rusak_berat'],
                ];
            }
        }

        if (!empty($errors)) {
            return ['success' => false, 'message' => implode('<br>', $errors)];
        }

        // Validasi Overlap & Duplikasi Segmen
        usort($clean, fn($a, $b) => $a['sta_awal'] <=> $b['sta_awal']);
        for ($i = 1; $i < count($clean); $i++) {
            if ($clean[$i]['sta_awal'] < $clean[$i-1]['sta_akhir']) {
                $errors[] = "Tumpang tindih terdeteksi antara Baris " . $clean[$i-1]['original_index'] . " (" . $clean[$i-1]['sta_awal_str'] . " s/d " . $clean[$i-1]['sta_akhir_str'] . ") dan Baris " . $clean[$i]['original_index'] . " (" . $clean[$i]['sta_awal_str'] . " s/d " . $clean[$i]['sta_akhir_str'] . ").";
            }
        }

        if (!empty($errors)) {
            return ['success' => false, 'message' => implode('<br>', $errors)];
        }

        foreach ($clean as $data) {
            // Hapus index bantu original_index dkk sebelum insert ke model
            unset($data['original_index']);
            unset($data['sta_awal_str']);
            unset($data['sta_akhir_str']);
            $this->model->create($data);
        }

        return ['success' => true, 'message' => count($clean) . ' segmen berhasil disimpan.'];
    }

    /**
     * Validasi & update stripmap
     */
    public function update(int $id, array $input): array
    {
        $existing = $this->model->findById($id);
        if (!$existing) {
            return ['success' => false, 'message' => 'Data strip map tidak ditemukan.'];
        }

        $errors = $this->validate($input, $existing['ruas_id'], $id, true);
        if (!empty($errors)) {
            return ['success' => false, 'message' => implode('<br>', $errors)];
        }

        $staAwal  = sta_to_meter($input['sta_awal']);
        $staAkhir = sta_to_meter($input['sta_akhir']);
        $panjang  = $staAkhir - $staAwal;

        $this->model->update($id, [
            'sta_awal'     => $staAwal,
            'sta_akhir'    => $staAkhir,
            'panjang'      => $panjang,
            'baik'         => (float) $input['baik'],
            'sedang'       => (float) $input['sedang'],
            'rusak_ringan' => (float) $input['rusak_ringan'],
            'rusak_berat'  => (float) $input['rusak_berat'],
        ]);

        return ['success' => true, 'message' => 'Data strip map berhasil diperbarui.', 'ruas_id' => $existing['ruas_id']];
    }

    /**
     * Hapus stripmap
     */
    public function delete(int $id): array
    {
        $existing = $this->model->findById($id);
        if (!$existing) {
            return ['success' => false, 'message' => 'Data strip map tidak ditemukan.'];
        }

        $this->model->delete($id);
        return ['success' => true, 'message' => 'Data strip map berhasil dihapus.', 'ruas_id' => $existing['ruas_id']];
    }

    /**
     * Hapus semua stripmap berdasarkan ruas_id
     */
    public function deleteByRuasId(int $ruasId): bool
    {
        return $this->model->deleteByRuasId($ruasId);
    }

    /**
     * Ambil ringkasan kondisi global seluruh ruas jalan
     */
    public function getGlobalSummary(?array $ruasIds = null): array
    {
        return $this->model->getGlobalSummary($ruasIds);
    }

    /**
     * Ambil ringkasan kondisi per ruas jalan
     */
    public function getConditionSummaryPerRuas(): array
    {
        return $this->model->getConditionSummaryPerRuas();
    }

    /**
     * Ambil ringkasan kemantapan per kabupaten/kota (untuk line chart dashboard)
     */
    public function getSummaryByKabupaten(): array
    {
        return $this->model->getSummaryByKabupaten();
    }

    /**
     * Validasi input stripmap
     */
    private function validate(array $input, int $ruasId, ?int $excludeId = null, bool $checkDbOverlap = true): array
    {
        $errors = [];

        // Validasi STA
        $staAwalRaw  = trim((string)($input['sta_awal'] ?? ''));
        $staAkhirRaw = trim((string)($input['sta_akhir'] ?? ''));

        if ($staAwalRaw === '') {
            $errors[] = 'STA Awal wajib diisi.';
        }
        if ($staAkhirRaw === '') {
            $errors[] = 'STA Akhir wajib diisi.';
        }

        if ($staAwalRaw !== '' && $staAkhirRaw !== '') {
            $staAwal  = sta_to_meter($input['sta_awal']);
            $staAkhir = sta_to_meter($input['sta_akhir']);
            $panjang  = $staAkhir - $staAwal;

            if ($staAwal < 0) {
                $errors[] = 'STA Awal tidak boleh negatif.';
            }
            if ($staAkhir < 0) {
                $errors[] = 'STA Akhir tidak boleh negatif.';
            }
            if ($staAkhir <= $staAwal) {
                $errors[] = 'STA Akhir harus lebih besar dari STA Awal.';
            }

            // Validasi kondisi jalan
            $baik        = (float) ($input['baik'] ?? 0);
            $sedang      = (float) ($input['sedang'] ?? 0);
            $rusakRingan = (float) ($input['rusak_ringan'] ?? 0);
            $rusakBerat  = (float) ($input['rusak_berat'] ?? 0);

            if ($baik < 0 || $sedang < 0 || $rusakRingan < 0 || $rusakBerat < 0) {
                $errors[] = 'Nilai kondisi jalan tidak boleh negatif.';
            }

            $totalKondisi = $baik + $sedang + $rusakRingan + $rusakBerat;

            if ($panjang > 0 && abs($totalKondisi - $panjang) > 0.01) {
                $errors[] = "Jumlah kondisi ({$totalKondisi} m) harus sama dengan panjang segmen ({$panjang} m).";
            }

            // Deteksi tumpang tindih dengan segmen yang sudah ada di database
            if ($checkDbOverlap) {
                $existingSegments = $this->model->getByRuasId($ruasId);
                foreach ($existingSegments as $es) {
                    if ($excludeId && (int)$es['id'] === $excludeId) {
                        continue;
                    }
                    $esAwal = (float)$es['sta_awal'];
                    $esAkhir = (float)$es['sta_akhir'];
                    if (max($staAwal, $esAwal) < min($staAkhir, $esAkhir)) {
                        $errors[] = "Segmen ini tumpang tindih dengan segmen yang sudah ada: STA " . meter_to_sta($esAwal) . " s/d " . meter_to_sta($esAkhir) . ".";
                        break;
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Ambil ringkasan kemantapan per koridor
     */
    public function getSummaryByKoridor(): array
    {
        return $this->model->getSummaryByKoridor();
    }
}
