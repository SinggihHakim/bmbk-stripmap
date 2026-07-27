<?php

/**
 * ============================================================
 * Controller: Export & Cetak Laporan
 * ============================================================
 */

class ExportController
{
    public function index(): void
    {
        $ruasService = new RuasService();
        $ruasList    = $ruasService->getAll();

        $stripmapService   = new StripmapService();
        $globalSummary     = $stripmapService->getGlobalSummary();

        $perkerasanService = new PerkerasanService();
        $perkerasanSummary = $perkerasanService->getGlobalSummary();

        // Hitung semua statistik km & persentase via helper bersama
        $stats = build_road_summary_stats($ruasList, $globalSummary, $perkerasanSummary);

        $data = array_merge($stats, [
            'title'    => 'Pusat Export & Cetak Laporan',
            'ruasList' => $ruasList,
            'totalRuas' => count($ruasList),
        ]);

        view('layouts.app', array_merge($data, ['content' => 'export.index']));
    }
}
