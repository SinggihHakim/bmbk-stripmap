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

        // Hitung total panjang jalan (dari meter ke kilometer)
        $totalPanjangM = array_sum(array_column($ruasList, 'panjang'));
        $totalPanjangKm = $totalPanjangM / 1000;

        $data = [
            'title'        => 'Dashboard',
            'totalRuas'    => $ruasService->count(),
            'ruasList'     => $ruasList,
            'totalPanjang' => $totalPanjangKm,
        ];


        view('layouts.app', array_merge($data, ['content' => 'dashboard.index']));
    }
}
