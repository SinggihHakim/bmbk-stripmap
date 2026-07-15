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
        $ruasService = new RuasService();
        $ruasList    = $ruasService->getAll();
        
        $stripmapService   = new StripmapService();
        $globalSummary     = $stripmapService->getGlobalSummary();

        $perkerasanService = new PerkerasanService();
        $perkerasanSummary = $perkerasanService->getGlobalSummary();

        // Hitung total panjang jalan (dari meter ke kilometer)
        $totalPanjangM  = array_sum(array_column($ruasList, 'panjang'));
        $totalPanjangKm = $totalPanjangM / 1000;

        // Ambil nilai kondisi global dalam km
        $baikKm        = ($globalSummary['total_baik'] ?? 0) / 1000;
        $sedangKm      = ($globalSummary['total_sedang'] ?? 0) / 1000;
        $rusakRinganKm = ($globalSummary['total_rusak_ringan'] ?? 0) / 1000;
        $rusakBeratKm  = ($globalSummary['total_rusak_berat'] ?? 0) / 1000;

        $mantapKm      = $baikKm + $sedangKm;
        $tidakMantapKm = $rusakRinganKm + $rusakBeratKm;

        // Ambil nilai perkerasan global dalam km
        $rigidKm        = ($perkerasanSummary['total_rigid'] ?? 0) / 1000;
        $aspalKm        = ($perkerasanSummary['total_aspal'] ?? 0) / 1000;
        $agregatTanahKm = ($perkerasanSummary['total_agregat_tanah'] ?? 0) / 1000;
        $belumTembusKm  = ($perkerasanSummary['total_belum_tembus'] ?? 0) / 1000;

        // Hitung persentase untuk Pie Chart (berdasarkan total panjang kondisi stripmap terisi)
        $totalKondisiM  = $globalSummary['total_panjang'] ?? 0;
        $pctBaik        = $totalKondisiM > 0 ? (($globalSummary['total_baik'] ?? 0) / $totalKondisiM) * 100 : 0;
        $pctSedang      = $totalKondisiM > 0 ? (($globalSummary['total_sedang'] ?? 0) / $totalKondisiM) * 100 : 0;
        $pctRusakRingan = $totalKondisiM > 0 ? (($globalSummary['total_rusak_ringan'] ?? 0) / $totalKondisiM) * 100 : 0;
        $pctRusakBerat  = $totalKondisiM > 0 ? (($globalSummary['total_rusak_berat'] ?? 0) / $totalKondisiM) * 100 : 0;

        $pctMantap      = $totalKondisiM > 0 ? ((($globalSummary['total_baik'] ?? 0) + ($globalSummary['total_sedang'] ?? 0)) / $totalKondisiM) * 100 : 0;
        $pctTidakMantap = $totalKondisiM > 0 ? ((($globalSummary['total_rusak_ringan'] ?? 0) + ($globalSummary['total_rusak_berat'] ?? 0)) / $totalKondisiM) * 100 : 0;

        // Hitung persentase perkerasan
        $totalPerkerasanM = $perkerasanSummary['total_panjang'] ?? 0;
        $pctRigid        = $totalPerkerasanM > 0 ? (($perkerasanSummary['total_rigid'] ?? 0) / $totalPerkerasanM) * 100 : 0;
        $pctAspal        = $totalPerkerasanM > 0 ? (($perkerasanSummary['total_aspal'] ?? 0) / $totalPerkerasanM) * 100 : 0;
        $pctAgregatTanah = $totalPerkerasanM > 0 ? (($perkerasanSummary['total_agregat_tanah'] ?? 0) / $totalPerkerasanM) * 100 : 0;
        $pctBelumTembus  = $totalPerkerasanM > 0 ? (($perkerasanSummary['total_belum_tembus'] ?? 0) / $totalPerkerasanM) * 100 : 0;

        $data = [
            'title'           => 'Dashboard',
            'totalRuas'       => count($ruasList),
            'ruasList'        => $ruasList,
            'totalPanjang'    => $totalPanjangKm,
            'baikKm'          => $baikKm,
            'sedangKm'        => $sedangKm,
            'rusakRinganKm'   => $rusakRinganKm,
            'rusakBeratKm'    => $rusakBeratKm,
            'mantapKm'        => $mantapKm,
            'tidakMantapKm'   => $tidakMantapKm,
            'rigidKm'         => $rigidKm,
            'aspalKm'         => $aspalKm,
            'agregatTanahKm'  => $agregatTanahKm,
            'belumTembusKm'   => $belumTembusKm,
            'pctBaik'         => $pctBaik,
            'pctSedang'       => $pctSedang,
            'pctRusakRingan'  => $pctRusakRingan,
            'pctRusakBerat'   => $pctRusakBerat,
            'pctMantap'       => $pctMantap,
            'pctTidakMantap'  => $pctTidakMantap,
            'pctRigid'        => $pctRigid,
            'pctAspal'        => $pctAspal,
            'pctAgregatTanah' => $pctAgregatTanah,
            'pctBelumTembus'  => $pctBelumTembus,
        ];

        view('layouts.app', array_merge($data, ['content' => 'dashboard.index']));
    }
}
