<?php

/**
 * ============================================================
 * Controller: StripmapController
 * ============================================================
 * CRUD untuk data strip map per ruas jalan.
 */

class StripmapController
{
    private StripmapService $service;
    private RuasService     $ruasService;

    public function __construct()
    {
        $this->service     = new StripmapService();
        $this->ruasService = new RuasService();
    }

    /**
     * Daftar stripmap untuk sebuah ruas
     */
    public function index(int $ruasId): void
    {
        $ruas = $this->ruasService->findById($ruasId);
        if (!$ruas) {
            flash('error', 'Ruas jalan tidak ditemukan.');
            redirect(base_url('ruas'));
            return;
        }

        $data = [
            'title'     => 'Strip Map: ' . $ruas['nama_ruas'],
            'ruas'      => $ruas,
            'stripmaps' => $this->service->getByRuasId($ruasId),
            'summary'   => $this->service->getSummary($ruasId),
        ];
        view('layouts.app', array_merge($data, ['content' => 'stripmap.index']));
    }

    /**
     * Form tambah stripmap
     */
    public function create(int $ruasId): void
    {
        $ruas = $this->ruasService->findById($ruasId);
        if (!$ruas) {
            flash('error', 'Ruas jalan tidak ditemukan.');
            redirect(base_url('ruas'));
            return;
        }

        // Handle insert_after parameter untuk fitur "Sisipkan Segmen"
        $prefillData = null;
        if (isset($_GET['insert_after']) && is_numeric($_GET['insert_after'])) {
            $afterSegmentId = (int) $_GET['insert_after'];
            $afterSegment = $this->service->findById($afterSegmentId);

            if ($afterSegment && $afterSegment['ruas_id'] == $ruasId) {
                // Ambil semua segmen untuk ruas ini, diurutkan by sta_awal
                $allSegments = $this->service->getByRuasId($ruasId);

                // Cari segmen berikutnya (segmen dengan sta_awal terkecil yang lebih besar dari sta_akhir afterSegment)
                $nextSegment = null;
                foreach ($allSegments as $seg) {
                    if ($seg['sta_awal'] > $afterSegment['sta_akhir']) {
                        $nextSegment = $seg;
                        break;
                    }
                }

                // Pre-fill data untuk segmen baru
                $prefillData = [
                    'sta_awal' => meter_to_sta($afterSegment['sta_akhir']),
                    'sta_akhir' => $nextSegment ? meter_to_sta($nextSegment['sta_awal']) : '',
                ];
            }
        }

        $data = [
            'title' => 'Tambah Strip Map',
            'ruas'  => $ruas,
            'prefillData' => $prefillData,
        ];
        view('layouts.app', array_merge($data, ['content' => 'stripmap.form']));
    }

    /**
     * Proses simpan stripmap baru
     */
    public function store(int $ruasId): void
    {
        // Periksa apakah ini batch insert (array of rows)
        if (isset($_POST['rows']) && is_array($_POST['rows'])) {
            $result = $this->service->batchCreate($ruasId, $_POST['rows']);
        } else {
            // Single insert fallback (untuk edit / lama)
            $result = $this->service->create($ruasId, $_POST);
        }

        if ($result['success']) {
            // Sinkronisasi STA ruas dari data stripmap
            $this->ruasService->syncStaFromStripmap($ruasId);
            flash('success', $result['message']);
            redirect(base_url('stripmap/' . $ruasId));
        } else {
            flash('error', $result['message']);
            // Redirect back with old input
            if (isset($_POST['rows'])) {
                $_SESSION['old_input'] = $_POST['rows'];
            }
            redirect(base_url('stripmap/create/' . $ruasId));
        }
    }

    /**
     * Form edit stripmap
     */
    public function edit(int $id): void
    {
        $stripmap = $this->service->findById($id);
        if (!$stripmap) {
            flash('error', 'Data strip map tidak ditemukan.');
            redirect(base_url('ruas'));
            return;
        }

        $ruas = $this->ruasService->findById($stripmap['ruas_id']);

        $data = [
            'title'    => 'Edit Strip Map',
            'ruas'     => $ruas,
            'stripmap' => $stripmap,
        ];
        view('layouts.app', array_merge($data, ['content' => 'stripmap.form']));
    }

    /**
     * Proses update stripmap
     */
    public function update(int $id): void
    {
        $input = $_POST;
        if (isset($_POST['rows']) && is_array($_POST['rows']) && isset($_POST['rows'][0])) {
            $input = $_POST['rows'][0];
        }
        $result = $this->service->update($id, $input);

        if ($result['success']) {
            // Sinkronisasi STA ruas dari data stripmap
            $this->ruasService->syncStaFromStripmap($result['ruas_id']);
            flash('success', $result['message']);
            redirect(base_url('stripmap/' . $result['ruas_id']));
        } else {
            flash('error', $result['message']);
            redirect(base_url('stripmap/edit/' . $id));
        }
    }

    /**
     * Proses hapus stripmap
     */
    public function delete(int $id): void
    {
        $result = $this->service->delete($id);

        if ($result['success']) {
            // Sinkronisasi STA ruas dari data stripmap
            $this->ruasService->syncStaFromStripmap($result['ruas_id']);
            flash('success', $result['message']);
            redirect(base_url('stripmap/' . $result['ruas_id']));
        } else {
            flash('error', $result['message']);
            redirect(base_url('ruas'));
        }
    }

    /**
     * Preview strip map visual untuk sebuah ruas
     */
    public function preview(int $ruasId): void
    {
        $ruas = $this->ruasService->findById($ruasId);
        if (!$ruas) {
            flash('error', 'Ruas jalan tidak ditemukan.');
            redirect(base_url('ruas'));
            return;
        }

        $data = [
            'title'     => 'Preview Strip Map: ' . $ruas['nama_ruas'],
            'ruas'      => $ruas,
            'stripmaps' => $this->service->getByRuasId($ruasId),
            'summary'   => $this->service->getSummary($ruasId),
        ];
        view('layouts.app', array_merge($data, ['content' => 'stripmap.preview']));
    }
}
