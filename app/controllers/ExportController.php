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

        // Hitung total panjang jalan (m -> km)
        $totalPanjangM  = array_sum(array_column($ruasList, 'panjang'));
        $totalPanjangKm = $totalPanjangM / 1000;

        $baikKm        = ($globalSummary['total_baik'] ?? 0) / 1000;
        $sedangKm      = ($globalSummary['total_sedang'] ?? 0) / 1000;
        $rusakRinganKm = ($globalSummary['total_rusak_ringan'] ?? 0) / 1000;
        $rusakBeratKm  = ($globalSummary['total_rusak_berat'] ?? 0) / 1000;

        $mantapKm      = $baikKm + $sedangKm;
        $tidakMantapKm = $rusakRinganKm + $rusakBeratKm;

        $rigidKm        = ($perkerasanSummary['total_rigid'] ?? 0) / 1000;
        $aspalKm        = ($perkerasanSummary['total_aspal'] ?? 0) / 1000;
        $agregatTanahKm = ($perkerasanSummary['total_agregat_tanah'] ?? 0) / 1000;
        $belumTembusKm  = ($perkerasanSummary['total_belum_tembus'] ?? 0) / 1000;

        $data = [
            'title'             => 'Pusat Export & Cetak Laporan',
            'ruasList'          => $ruasList,
            'totalRuas'         => count($ruasList),
            'totalPanjang'      => $totalPanjangKm,
            'baikKm'            => $baikKm,
            'sedangKm'          => $sedangKm,
            'rusakRinganKm'     => $rusakRinganKm,
            'rusakBeratKm'      => $rusakBeratKm,
            'mantapKm'          => $mantapKm,
            'tidakMantapKm'     => $tidakMantapKm,
            'rigidKm'           => $rigidKm,
            'aspalKm'           => $aspalKm,
            'agregatTanahKm'    => $agregatTanahKm,
            'belumTembusKm'     => $belumTembusKm,
        ];

        view('layouts.app', array_merge($data, ['content' => 'export.index']));
    }
}
