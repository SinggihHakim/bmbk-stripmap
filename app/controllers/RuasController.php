<?php

/**
 * ============================================================
 * Controller: RuasController
 * ============================================================
 * CRUD untuk data ruas jalan.
 */

class RuasController
{
    private RuasService $service;

    public function __construct()
    {
        $this->service = new RuasService();
    }

    /**
     * Tampilkan daftar ruas jalan
     */
    public function index(): void
    {
        $data = [
            'title'    => 'Data Ruas Jalan',
            'ruasList' => $this->service->getAll(),
        ];
        view('layouts.app', array_merge($data, ['content' => 'ruas.index']));
    }

    /**
     * Form tambah ruas
     */
    public function create(): void
    {
        $data = [
            'title' => 'Tambah Ruas Jalan',
        ];
        view('layouts.app', array_merge($data, ['content' => 'ruas.form']));
    }

    /**
     * Proses simpan ruas baru beserta stripmap nya
     */
    public function store(): void
    {
        // Mulai proses penyimpanan ruas
        $result = $this->service->create($_POST);

        if ($result['success']) {
            // Jika berhasil simpan ruas, cek apakah ada input array baris stripmap (batch input)
            if (isset($_POST['rows']) && is_array($_POST['rows'])) {
                // Filter baris kosong
                $rows = array_filter($_POST['rows'], function($row) {
                    return !empty(trim($row['sta_awal'] ?? '')) || !empty(trim($row['sta_akhir'] ?? ''));
                });

                if (!empty($rows)) {
                    $stripmapService = new StripmapService();

                    // Gunakan fungsi batchCreate dari StripmapService
                    $stripmapResult = $stripmapService->batchCreate($result['id'], array_values($rows));

                    if (!$stripmapResult['success']) {
                        flash('error', "Ruas berhasil dibuat, tapi gagal menyimpan strip map: " . $stripmapResult['message']);
                        redirect(base_url('ruas'));
                        return;
                    }

                    // Sinkronisasi STA & panjang ruas dari data stripmap
                    $this->service->syncStaFromStripmap($result['id']);
                }
            }

            flash('success', $result['message']);
            redirect(base_url('ruas'));
        } else {
            flash('error', $result['message']);
            // Simpan old input baris stripmap kalau form gagal
            if (isset($_POST['rows'])) {
                $_SESSION['old_rows'] = $_POST['rows'];
            }
            redirect(base_url('ruas/create'));
        }
    }

    /**
     * Form edit ruas
     */
    public function edit(int $id): void
    {
        $ruas = $this->service->findById($id);
        if (!$ruas) {
            flash('error', 'Ruas jalan tidak ditemukan.');
            redirect(base_url('ruas'));
            return;
        }

        $stripmapService = new StripmapService();
        $stripmaps = $stripmapService->getByRuasId($id);

        $data = [
            'title'     => 'Edit Ruas Jalan',
            'ruas'      => $ruas,
            'stripmaps' => $stripmaps,
        ];
        view('layouts.app', array_merge($data, ['content' => 'ruas.form']));
    }

    /**
     * Proses update ruas
     */
    public function update(int $id): void
    {
        $result = $this->service->update($id, $_POST);

        if ($result['success']) {
            $stripmapService = new StripmapService();

            // Hapus segmen lama terlebih dahulu untuk digantikan dengan baris yang baru disubmit
            $stripmapService->deleteByRuasId($id);

            if (isset($_POST['rows']) && is_array($_POST['rows'])) {
                // Filter baris kosong
                $rows = array_filter($_POST['rows'], function($row) {
                    return !empty(trim($row['sta_awal'] ?? '')) || !empty(trim($row['sta_akhir'] ?? ''));
                });

                if (!empty($rows)) {
                    $stripmapResult = $stripmapService->batchCreate($id, array_values($rows));

                    if (!$stripmapResult['success']) {
                        flash('error', "Ruas berhasil diperbarui, tetapi gagal menyimpan strip map: " . $stripmapResult['message']);
                        $_SESSION['old_rows'] = $_POST['rows'];
                        redirect(base_url('ruas/edit/' . $id));
                        return;
                    }
                }
            }

            // Sinkronisasi STA & panjang ruas dari data stripmap
            $this->service->syncStaFromStripmap($id);

            flash('success', 'Data ruas jalan dan strip map berhasil diperbarui.');
            redirect(base_url('ruas'));
        } else {
            flash('error', $result['message']);
            if (isset($_POST['rows'])) {
                $_SESSION['old_rows'] = $_POST['rows'];
            }
            redirect(base_url('ruas/edit/' . $id));
        }
    }

    /**
     * Proses hapus ruas
     */
    public function delete(int $id): void
    {
        $result = $this->service->delete($id);

        if ($result['success']) {
            flash('success', $result['message']);
        } else {
            flash('error', $result['message']);
        }
        redirect(base_url('ruas'));
    }

    /**
     * Tampilkan detail ruas (dengan strip map)
     */
    public function show(int $id): void
    {
        $ruas = $this->service->findById($id);
        if (!$ruas) {
            flash('error', 'Ruas jalan tidak ditemukan.');
            redirect(base_url('ruas'));
            return;
        }

        $stripmapService = new StripmapService();

        $data = [
            'title'     => 'Detail Ruas: ' . $ruas['nama_ruas'],
            'ruas'      => $ruas,
            'stripmaps' => $stripmapService->getByRuasId($id),
            'summary'   => $stripmapService->getSummary($id),
        ];
        view('layouts.app', array_merge($data, ['content' => 'ruas.show']));
    }
}
