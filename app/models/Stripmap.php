<?php

/**
 * ============================================================
 * Model: Stripmap
 * ============================================================
 * Mengelola query ke tabel `stripmap`.
 */

class Stripmap
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Ambil semua stripmap berdasarkan ruas_id
     */
    public function getByRuasId(int $ruasId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM stripmap WHERE ruas_id = :ruas_id ORDER BY sta_awal ASC'
        );
        $stmt->execute(['ruas_id' => $ruasId]);
        return $stmt->fetchAll();
    }

    /**
     * Ambil satu stripmap berdasarkan ID
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM stripmap WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Simpan stripmap baru
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO stripmap (ruas_id, sta_awal, sta_akhir, panjang, baik, sedang, rusak_ringan, rusak_berat)
             VALUES (:ruas_id, :sta_awal, :sta_akhir, :panjang, :baik, :sedang, :rusak_ringan, :rusak_berat)'
        );
        $stmt->execute([
            'ruas_id'      => $data['ruas_id'],
            'sta_awal'     => $data['sta_awal'],
            'sta_akhir'    => $data['sta_akhir'],
            'panjang'      => $data['panjang'],
            'baik'         => $data['baik'],
            'sedang'       => $data['sedang'],
            'rusak_ringan' => $data['rusak_ringan'],
            'rusak_berat'  => $data['rusak_berat'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Update stripmap berdasarkan ID
     */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE stripmap
             SET sta_awal     = :sta_awal,
                 sta_akhir    = :sta_akhir,
                 panjang      = :panjang,
                 baik         = :baik,
                 sedang       = :sedang,
                 rusak_ringan = :rusak_ringan,
                 rusak_berat  = :rusak_berat
             WHERE id = :id'
        );
        return $stmt->execute([
            'sta_awal'     => $data['sta_awal'],
            'sta_akhir'    => $data['sta_akhir'],
            'panjang'      => $data['panjang'],
            'baik'         => $data['baik'],
            'sedang'       => $data['sedang'],
            'rusak_ringan' => $data['rusak_ringan'],
            'rusak_berat'  => $data['rusak_berat'],
            'id'           => $id,
        ]);
    }

    /**
     * Hapus stripmap berdasarkan ID
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM stripmap WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Hapus semua stripmap berdasarkan ruas_id
     */
    public function deleteByRuasId(int $ruasId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM stripmap WHERE ruas_id = :ruas_id');
        return $stmt->execute(['ruas_id' => $ruasId]);
    }

    /**
     * Hitung total segmen stripmap untuk sebuah ruas
     */
    public function countByRuasId(int $ruasId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) as total FROM stripmap WHERE ruas_id = :ruas_id');
        $stmt->execute(['ruas_id' => $ruasId]);
        return (int) $stmt->fetch()['total'];
    }

    /**
     * Ambil ringkasan kondisi untuk sebuah ruas (total per kondisi)
     */
    public function getSummaryByRuasId(int $ruasId): array
    {
        $stmt = $this->db->prepare(
            'SELECT
                SUM(panjang)       as total_panjang,
                SUM(baik)          as total_baik,
                SUM(sedang)        as total_sedang,
                SUM(rusak_ringan)  as total_rusak_ringan,
                SUM(rusak_berat)   as total_rusak_berat
             FROM stripmap
             WHERE ruas_id = :ruas_id'
        );
        $stmt->execute(['ruas_id' => $ruasId]);
        return $stmt->fetch() ?: [
            'total_panjang'       => 0,
            'total_baik'          => 0,
            'total_sedang'        => 0,
            'total_rusak_ringan'  => 0,
            'total_rusak_berat'   => 0,
        ];
    }

    /**
     * Ambil ringkasan kondisi untuk seluruh ruas jalan di database (Global)
     */
    public function getGlobalSummary(): array
    {
        $stmt = $this->db->query(
            'SELECT
                SUM(panjang)       as total_panjang,
                SUM(baik)          as total_baik,
                SUM(sedang)        as total_sedang,
                SUM(rusak_ringan)  as total_rusak_ringan,
                SUM(rusak_berat)   as total_rusak_berat
             FROM stripmap'
        );
        return $stmt->fetch() ?: [
            'total_panjang'       => 0,
            'total_baik'          => 0,
            'total_sedang'        => 0,
            'total_rusak_ringan'  => 0,
            'total_rusak_berat'   => 0,
        ];
    }
}
