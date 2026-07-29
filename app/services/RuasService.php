<?php

/**
 * ============================================================
 * Service: RuasService
 * ============================================================
 * Business logic untuk Ruas Jalan.
 * Controller memanggil service, service memanggil model.
 */

class RuasService
{
    private RuasJalan $model;

    public function __construct()
    {
        $this->model = new RuasJalan();
    }

    /**
     * Ambil semua ruas
     */
    public function getAll(): array
    {
        return $this->model->getAll();
    }

    /**
     * Ambil satu ruas
     */
    public function findById(int $id): ?array
    {
        return $this->model->findById($id);
    }

    /**
     * Ambil satu ruas berdasarkan kode
     */
    public function findByKode(string $kodeRuas): ?array
    {
        return $this->model->findByKode($kodeRuas);
    }

    /**
     * Validasi & simpan ruas baru
     * @return array ['success' => bool, 'message' => string, 'id' => ?int]
     */
    public function create(array $input): array
    {
        $errors = $this->validate($input);
        if (!empty($errors)) {
            return ['success' => false, 'message' => implode('<br>', $errors), 'errors' => $errors];
        }

        // Kita biarkan 0 untuk awal, ini akan diperbarui dari summary stripmap
        $staAwal  = 0;
        $staAkhir = 0;
        $panjang  = 0;

        $id = $this->model->create([
            'kode_ruas' => trim($input['kode_ruas']),
            'nama_ruas' => trim($input['nama_ruas']),
            'sta_awal'  => $staAwal,
            'sta_akhir' => $staAkhir,
            'panjang'   => $panjang,
            'koridor'   => !empty(trim($input['koridor'] ?? '')) ? trim($input['koridor']) : null,
            'kabupaten_kota' => !empty(trim($input['kabupaten_kota'] ?? '')) ? trim($input['kabupaten_kota']) : null,
            'lat_awal'       => $this->normalizeCoord($input['lat_awal'] ?? null),
            'lng_awal'       => $this->normalizeCoord($input['lng_awal'] ?? null),
            'lat_akhir'      => $this->normalizeCoord($input['lat_akhir'] ?? null),
            'lng_akhir'      => $this->normalizeCoord($input['lng_akhir'] ?? null),
            'koordinat_json' => !empty($input['koordinat_json']) ? $input['koordinat_json'] : null,
        ]);

        return ['success' => true, 'message' => 'Ruas jalan berhasil ditambahkan.', 'id' => $id];
    }

    /**
     * Validasi & update ruas
     */
    public function update(int $id, array $input): array
    {
        $existing = $this->model->findById($id);
        if (!$existing) {
            return ['success' => false, 'message' => 'Ruas jalan tidak ditemukan.'];
        }

        // Merge input dengan data existing agar partial update (misal impor KML saja) tidak gagal validasi
        $mergedInput = array_merge($existing, array_filter($input, fn($v) => $v !== null));

        $errors = $this->validate($mergedInput, $id);
        if (!empty($errors)) {
            return ['success' => false, 'message' => implode('<br>', $errors), 'errors' => $errors];
        }

        $updateData = [
            'kode_ruas' => trim($mergedInput['kode_ruas']),
            'nama_ruas' => trim($mergedInput['nama_ruas']),
            'koridor'   => !empty(trim($mergedInput['koridor'] ?? '')) ? trim($mergedInput['koridor']) : null,
            'kabupaten_kota' => !empty(trim($mergedInput['kabupaten_kota'] ?? '')) ? trim($mergedInput['kabupaten_kota']) : null,
            'lat_awal'  => array_key_exists('lat_awal', $input) ? $this->normalizeCoord($input['lat_awal']) : $existing['lat_awal'],
            'lng_awal'  => array_key_exists('lng_awal', $input) ? $this->normalizeCoord($input['lng_awal']) : $existing['lng_awal'],
            'lat_akhir' => array_key_exists('lat_akhir', $input) ? $this->normalizeCoord($input['lat_akhir']) : $existing['lat_akhir'],
            'lng_akhir' => array_key_exists('lng_akhir', $input) ? $this->normalizeCoord($input['lng_akhir']) : $existing['lng_akhir'],
            'koordinat_json' => array_key_exists('koordinat_json', $input) ? (!empty($input['koordinat_json']) ? $input['koordinat_json'] : null) : $existing['koordinat_json'],
        ];

        if (isset($input['sta_awal']))  $updateData['sta_awal']  = (float)$input['sta_awal'];
        if (isset($input['sta_akhir'])) $updateData['sta_akhir'] = (float)$input['sta_akhir'];
        if (isset($input['panjang']))   $updateData['panjang']   = (float)$input['panjang'];

        $this->model->update($id, $updateData);

        return ['success' => true, 'message' => 'Ruas jalan berhasil diperbarui.'];
    }

    /**
     * Hapus ruas
     */
    public function delete(int $id): array
    {
        $ruas = $this->model->findById($id);
        if (!$ruas) {
            return ['success' => false, 'message' => 'Ruas jalan tidak ditemukan.'];
        }

        $this->model->delete($id);
        return ['success' => true, 'message' => 'Ruas jalan berhasil dihapus.'];
    }

    /**
     * Hitung total ruas
     */
    public function count(): int
    {
        return $this->model->count();
    }

    /**
     * Sinkronisasi STA awal, akhir, panjang ruas berdasarkan data stripmap
     */
    public function syncStaFromStripmap(int $ruasId): void
    {
        $this->model->updateStaFromStripmap($ruasId);
    }

    /**
     * Normalisasi nilai koordinat: string kosong -> null, selainnya -> float.
     * Menerima koma sebagai desimal (mis. "-5,45" -> -5.45).
     */
    private function normalizeCoord($value): ?float
    {
        if ($value === null) {
            return null;
        }
        $value = trim(str_replace(',', '.', (string) $value));
        if ($value === '' || !is_numeric($value)) {
            return null;
        }
        return (float) $value;
    }

    /**
     * Validasi input ruas.
     * $excludeId digunakan untuk validasi uniqueness kode_ruas saat edit
     * (mengecualikan ruas yang sedang diedit dari pengecekan duplikat).
     */
    private function validate(array $input, ?int $excludeId = null): array
    {
        $errors = [];

        $kodeRuas = trim($input['kode_ruas'] ?? '');
        $namaRuas = trim($input['nama_ruas'] ?? '');

        if (empty($kodeRuas)) {
            $errors[] = 'Kode ruas wajib diisi.';
        }
        if (empty($namaRuas)) {
            $errors[] = 'Nama ruas wajib diisi.';
        }

        // Validasi uniqueness kode_ruas (skip baris sendiri saat edit)
        if (!empty($kodeRuas)) {
            $existing = $this->model->findByKodeExcluding($kodeRuas, $excludeId);
            if ($existing) {
                $errors[] = "Kode ruas '{$kodeRuas}' sudah digunakan oleh ruas lain.";
            }
        }

        return $errors;
    }
}
