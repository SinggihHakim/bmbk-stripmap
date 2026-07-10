<?php

/**
 * ============================================================
 * Controller: Dashboard
 * ============================================================
 */

class DashboardController
{
    public function index(): void
    {
        require_once BASE_PATH . '/app/services/RuasService.php';
        require_once BASE_PATH . '/app/services/StripmapService.php';

        $ruasService = new RuasService();
        $ruasList    = $ruasService->getAll();
        
        $stripmapService = new StripmapService();
        $globalSummary = $stripmapService->getGlobalSummary();

        // Hitung total panjang jalan (dari meter ke kilometer)
        $totalPanjangM = array_sum(array_column($ruasList, 'panjang'));
        $totalPanjangKm = $totalPanjangM / 1000;

        // Ambil nilai kondisi global dalam km
        $baikKm = ($globalSummary['total_baik'] ?? 0) / 1000;
        $sedangKm = ($globalSummary['total_sedang'] ?? 0) / 1000;
        $rusakRinganKm = ($globalSummary['total_rusak_ringan'] ?? 0) / 1000;
        $rusakBeratKm = ($globalSummary['total_rusak_berat'] ?? 0) / 1000;

        $mantapKm = $baikKm + $sedangKm;
        $tidakMantapKm = $rusakRinganKm + $rusakBeratKm;

        // Hitung persentase untuk Pie Chart (berdasarkan total panjang kondisi stripmap terisi)
        $totalKondisiM = $globalSummary['total_panjang'] ?? 0;
        $pctBaik = $totalKondisiM > 0 ? (($globalSummary['total_baik'] ?? 0) / $totalKondisiM) * 100 : 0;
        $pctSedang = $totalKondisiM > 0 ? (($globalSummary['total_sedang'] ?? 0) / $totalKondisiM) * 100 : 0;
        $pctRusakRingan = $totalKondisiM > 0 ? (($globalSummary['total_rusak_ringan'] ?? 0) / $totalKondisiM) * 100 : 0;
        $pctRusakBerat = $totalKondisiM > 0 ? (($globalSummary['total_rusak_berat'] ?? 0) / $totalKondisiM) * 100 : 0;

        $pctMantap = $totalKondisiM > 0 ? ((($globalSummary['total_baik'] ?? 0) + ($globalSummary['total_sedang'] ?? 0)) / $totalKondisiM) * 100 : 0;
        $pctTidakMantap = $totalKondisiM > 0 ? ((($globalSummary['total_rusak_ringan'] ?? 0) + ($globalSummary['total_rusak_berat'] ?? 0)) / $totalKondisiM) * 100 : 0;

        $data = [
            'title'          => 'Dashboard',
            'totalRuas'      => $ruasService->count(),
            'ruasList'       => $ruasList,
            'totalPanjang'   => $totalPanjangKm,
            'baikKm'         => $baikKm,
            'sedangKm'       => $sedangKm,
            'rusakRinganKm'  => $rusakRinganKm,
            'rusakBeratKm'   => $rusakBeratKm,
            'mantapKm'       => $mantapKm,
            'tidakMantapKm'  => $tidakMantapKm,
            'pctBaik'        => $pctBaik,
            'pctSedang'      => $pctSedang,
            'pctRusakRingan' => $pctRusakRingan,
            'pctRusakBerat'  => $pctRusakBerat,
            'pctMantap'      => $pctMantap,
            'pctTidakMantap' => $pctTidakMantap,
        ];

        view('layouts.app', array_merge($data, ['content' => 'dashboard.index']));
    }
}
