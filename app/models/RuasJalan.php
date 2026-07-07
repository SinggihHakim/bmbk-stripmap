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
            'INSERT INTO ruas_jalan (kode_ruas, nama_ruas, sta_awal, sta_akhir, panjang)
             VALUES (:kode_ruas, :nama_ruas, :sta_awal, :sta_akhir, :panjang)'
        );
        $stmt->execute([
            'kode_ruas' => $data['kode_ruas'],
            'nama_ruas' => $data['nama_ruas'],
            'sta_awal'  => $data['sta_awal'],
            'sta_akhir' => $data['sta_akhir'],
            'panjang'   => $data['panjang'],
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

        foreach ($data as $key => $value) {
            $sets[]       = "$key = :$key";
            $params[$key] = $value;
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
                SET r.sta_awal  = COALESCE((SELECT MIN(s.sta_awal) FROM stripmap s WHERE s.ruas_id = :id1), 0),
                    r.sta_akhir = COALESCE((SELECT MAX(s.sta_akhir) FROM stripmap s WHERE s.ruas_id = :id2), 0),
                    r.panjang   = COALESCE((SELECT SUM(s.panjang) FROM stripmap s WHERE s.ruas_id = :id3), 0)
                WHERE r.id = :id4';

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id1' => $ruasId,
            'id2' => $ruasId,
            'id3' => $ruasId,
            'id4' => $ruasId,
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
