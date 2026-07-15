<?php

/**
 * ============================================================
 * Model: RuasJalan
 * ============================================================
 * Mengelola query ke tabel `ruas_jalan`.
 */

class RuasJalan
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Ambil semua data ruas jalan
     */
    public function getAll(): array
    {
        $stmt = $this->db->query('SELECT * FROM ruas_jalan ORDER BY kode_ruas ASC');
        return $stmt->fetchAll();
    }

    /**
     * Ambil satu ruas berdasarkan ID
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM ruas_jalan WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Simpan ruas baru
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO ruas_jalan (kode_ruas, nama_ruas, sta_awal, sta_akhir, panjang, koridor, kabupaten_kota)
             VALUES (:kode_ruas, :nama_ruas, :sta_awal, :sta_akhir, :panjang, :koridor, :kabupaten_kota)'
        );
        $stmt->execute([
            'kode_ruas' => $data['kode_ruas'],
            'nama_ruas' => $data['nama_ruas'],
            'sta_awal'  => $data['sta_awal'],
            'sta_akhir' => $data['sta_akhir'],
            'panjang'   => $data['panjang'],
            'koridor'   => $data['koridor'] ?? null,
            'kabupaten_kota' => $data['kabupaten_kota'] ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }


    /**
     * Update ruas berdasarkan ID (field dinamis)
     */
    public function update(int $id, array $data): bool
    {
        $sets   = [];
        $params = ['id' => $id];
        $allowedColumns = ['kode_ruas', 'nama_ruas', 'sta_awal', 'sta_akhir', 'panjang', 'koridor', 'kabupaten_kota'];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowedColumns, true)) {
                $sets[]       = "$key = :$key";
                $params[$key] = $value;
            }
        }

        if (empty($sets)) {
            return false;
        }

        $sql = 'UPDATE ruas_jalan SET ' . implode(', ', $sets) . ' WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Update STA awal, akhir, panjang ruas berdasarkan data stripmap
     */
    public function updateStaFromStripmap(int $ruasId): bool
    {
        $sql = 'UPDATE ruas_jalan r
                JOIN (
                    SELECT 
                        COALESCE(MIN(sta_awal), 0) as min_awal,
                        COALESCE(MAX(sta_akhir), 0) as max_akhir,
                        COALESCE(SUM(panjang), 0) as total_panjang
                    FROM stripmap
                    WHERE ruas_id = :ruas_id
                ) s
                SET r.sta_awal  = LEAST(r.sta_awal, s.min_awal),
                    r.sta_akhir = GREATEST(r.sta_akhir, s.max_akhir),
                    r.panjang   = GREATEST(r.panjang, s.max_akhir - s.min_awal)
                WHERE r.id = :id';

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'ruas_id' => $ruasId,
            'id'      => $ruasId,
        ]);
    }

    /**
     * Hapus ruas berdasarkan ID
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM ruas_jalan WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Hitung total ruas jalan
     */
    public function count(): int
    {
        $stmt = $this->db->query('SELECT COUNT(*) as total FROM ruas_jalan');
        return (int) $stmt->fetch()['total'];
    }
}
