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

        $data = [
            'title'      => 'Dashboard',
            'totalRuas'  => $ruasService->count(),
            'ruasList'   => $ruasService->getAll(),
        ];

        view('layouts.app', array_merge($data, ['content' => 'dashboard.index']));
    }
}
