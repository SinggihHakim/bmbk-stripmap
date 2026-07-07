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
        require_once BASE_PATH . '/app/models/RuasJalan.php';
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
     * Validasi & simpan ruas baru
     * @return array ['success' => bool, 'message' => string, 'id' => ?int]
     */
    public function create(array $input): array
    {
        $errors = $this->validate($input);
        if (!empty($errors)) {
            return ['success' => false, 'message' => implode('<br>', $errors)];
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
        ]);

        return ['success' => true, 'message' => 'Ruas jalan berhasil ditambahkan.', 'id' => $id];
    }

    /**
     * Validasi & update ruas
     */
    public function update(int $id, array $input): array
    {
        $errors = $this->validate($input, $id);
        if (!empty($errors)) {
            return ['success' => false, 'message' => implode('<br>', $errors)];
        }

        // Update tidak menyentuh STA dan Panjang (akan disinkronisasi ketika stripmap di update)
        $this->model->update($id, [
            'kode_ruas' => trim($input['kode_ruas']),
            'nama_ruas' => trim($input['nama_ruas']),
        ]);

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
     * Validasi input ruas
     */
    private function validate(array $input, ?int $excludeId = null): array
    {
        $errors = [];

        if (empty(trim($input['kode_ruas'] ?? ''))) {
            $errors[] = 'Kode ruas wajib diisi.';
        }
        if (empty(trim($input['nama_ruas'] ?? ''))) {
            $errors[] = 'Nama ruas wajib diisi.';
        }

        return $errors;
    }
}
