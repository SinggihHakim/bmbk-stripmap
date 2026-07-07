-- ============================================================
-- Strip Map Ruas Jalan - Data Seeder (Contoh)
-- ============================================================

USE `stripmap_db`;

-- Data contoh ruas jalan
INSERT INTO `ruas_jalan` (`kode_ruas`, `nama_ruas`, `sta_awal`, `sta_akhir`, `panjang`) VALUES
('RJ-001', 'Jl. Ahmad Yani',       0.00, 5000.00, 5000.00),
('RJ-002', 'Jl. Sudirman',         0.00, 3500.00, 3500.00),
('RJ-003', 'Jl. Gatot Subroto',    0.00, 7200.00, 7200.00);

-- Data contoh strip map untuk Jl. Ahmad Yani
INSERT INTO `stripmap` (`ruas_id`, `sta_awal`, `sta_akhir`, `panjang`, `baik`, `sedang`, `rusak_ringan`, `rusak_berat`) VALUES
(1, 0.00,    1000.00, 1000.00, 600.00, 200.00, 150.00, 50.00),
(1, 1000.00, 2500.00, 1500.00, 900.00, 300.00, 200.00, 100.00),
(1, 2500.00, 5000.00, 2500.00, 1200.00, 500.00, 500.00, 300.00);

-- Data contoh strip map untuk Jl. Sudirman
INSERT INTO `stripmap` (`ruas_id`, `sta_awal`, `sta_akhir`, `panjang`, `baik`, `sedang`, `rusak_ringan`, `rusak_berat`) VALUES
(2, 0.00,    1500.00, 1500.00, 1000.00, 300.00, 150.00, 50.00),
(2, 1500.00, 3500.00, 2000.00, 800.00, 600.00, 400.00, 200.00);
