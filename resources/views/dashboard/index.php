<!-- ============================================================ -->
<!-- Dashboard -->
<!-- ============================================================ -->

<div class="space-y-8">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
            <p class="mt-1 text-sm text-gray-500">Ringkasan data ruas jalan, strip map, dan jenis perkerasan.</p>
        </div>
    </div>

    <!-- Load Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Capture Area -->
    <div id="capture-area" class="space-y-8 bg-transparent pb-4">
    <!-- Stats Cards & Pie Charts Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left Panel: Metric Cards (2 - 4 - 2 Grid Layout) -->
        <div class="lg:col-span-2 space-y-4">
            
            <!-- Row 1: 2 Grid (Total Ruas & Total Panjang) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Card 1: Total Ruas Jalan -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                        <span class="text-xs font-semibold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md">General</span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900"><?= $totalRuas ?? 0 ?> <span class="text-xs font-semibold text-gray-400">Ruas</span></h3>
                    <p class="text-[13px] font-semibold text-gray-500 mt-1">Total Ruas Jalan</p>
                </div>

                <!-- Card 2: Total Panjang Jalan -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                            </svg>
                        </div>
                        <span class="text-xs font-semibold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-md">Panjang</span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900"><?= format_number($totalPanjang ?? 0.0, 2) ?> <span class="text-xs font-semibold text-gray-400">km</span></h3>
                    <p class="text-[13px] font-semibold text-gray-500 mt-1">Total Panjang Jalan</p>
                </div>
            </div>

            <!-- Row 2: 4 Grid (Detail Kondisi Segmen: Baik, Sedang, Rusak Ringan, Rusak Berat) -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <!-- Card 3: Baik -->
                <a href="<?= base_url('dashboard/detail?kondisi=baik') ?>" 
                   title="Klik untuk melihat detail ruas jalan kondisi Baik"
                   class="p-4 rounded-xl border shadow-sm hover:shadow-md transition-all transform hover:-translate-y-0.5 group cursor-pointer block" 
                   style="background-color: #f0fdf4; border-color: #d1fae5;">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full" style="background-color: #10b981; display: inline-block; width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0;"></span>
                            <span class="text-xs font-semibold text-emerald-800 group-hover:underline">Baik</span>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[10px] font-bold">
                            <?= format_number($pctBaik ?? 0.0, 1) ?>%
                        </span>
                    </div>
                    <h3 class="text-xl font-bold text-emerald-700"><?= format_number($baikKm ?? 0.0, 2) ?> <span class="text-xs font-normal text-emerald-600">km</span></h3>
                    <p class="text-[11px] font-medium text-emerald-600 mt-0.5 flex items-center justify-between">
                        <span>Kondisi Baik</span>
                        <span class="text-[10px] font-semibold opacity-70 group-hover:opacity-100">Detail &rarr;</span>
                    </p>
                </a>

                <!-- Card 4: Sedang -->
                <a href="<?= base_url('dashboard/detail?kondisi=sedang') ?>" 
                   title="Klik untuk melihat detail ruas jalan kondisi Sedang"
                   class="p-4 rounded-xl border shadow-sm hover:shadow-md transition-all transform hover:-translate-y-0.5 group cursor-pointer block" 
                   style="background-color: #fefce8; border-color: #fef08a;">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full" style="background-color: #facc15; display: inline-block; width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0;"></span>
                            <span class="text-xs font-semibold text-yellow-800 group-hover:underline">Sedang</span>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-yellow-100 text-yellow-800 text-[10px] font-bold">
                            <?= format_number($pctSedang ?? 0.0, 1) ?>%
                        </span>
                    </div>
                    <h3 class="text-xl font-bold text-yellow-700"><?= format_number($sedangKm ?? 0.0, 2) ?> <span class="text-xs font-normal text-yellow-600">km</span></h3>
                    <p class="text-[11px] font-medium text-yellow-600 mt-0.5 flex items-center justify-between">
                        <span>Kondisi Sedang</span>
                        <span class="text-[10px] font-semibold opacity-70 group-hover:opacity-100">Detail &rarr;</span>
                    </p>
                </a>

                <!-- Card 5: Rusak Ringan -->
                <a href="<?= base_url('dashboard/detail?kondisi=rusak_ringan') ?>" 
                   title="Klik untuk melihat detail ruas jalan kondisi Rusak Ringan (Terpanjang ke Terpendek)"
                   class="p-4 rounded-xl border shadow-sm hover:shadow-md transition-all transform hover:-translate-y-0.5 group cursor-pointer block ring-2 ring-orange-400/40" 
                   style="background-color: #fff7ed; border-color: #ffedd5;">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full" style="background-color: #f97316; display: inline-block; width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0;"></span>
                            <span class="text-xs font-bold text-orange-900 group-hover:underline">Rusak Ringan</span>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-orange-100 text-orange-800 text-[10px] font-bold">
                            <?= format_number($pctRusakRingan ?? 0.0, 1) ?>%
                        </span>
                    </div>
                    <h3 class="text-xl font-bold text-orange-700"><?= format_number($rusakRinganKm ?? 0.0, 2) ?> <span class="text-xs font-normal text-orange-600">km</span></h3>
                    <p class="text-[11px] font-semibold text-orange-600 mt-0.5 flex items-center justify-between">
                        <span>Rusak Ringan</span>
                        <span class="text-[10px] font-bold underline opacity-80 group-hover:opacity-100">Detail &rarr;</span>
                    </p>
                </a>

                <!-- Card 6: Rusak Berat -->
                <a href="<?= base_url('dashboard/detail?kondisi=rusak_berat') ?>" 
                   title="Klik untuk melihat detail ruas jalan kondisi Rusak Berat (Terpanjang ke Terpendek)"
                   class="p-4 rounded-xl border shadow-sm hover:shadow-md transition-all transform hover:-translate-y-0.5 group cursor-pointer block ring-2 ring-red-400/40" 
                   style="background-color: #fef2f2; border-color: #fee2e2;">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full" style="background-color: #ef4444; display: inline-block; width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0;"></span>
                            <span class="text-xs font-bold text-red-900 group-hover:underline">Rusak Berat</span>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-red-100 text-red-800 text-[10px] font-bold">
                            <?= format_number($pctRusakBerat ?? 0.0, 1) ?>%
                        </span>
                    </div>
                    <h3 class="text-xl font-bold text-red-700"><?= format_number($rusakBeratKm ?? 0.0, 2) ?> <span class="text-xs font-normal text-red-600">km</span></h3>
                    <p class="text-[11px] font-semibold text-red-600 mt-0.5 flex items-center justify-between">
                        <span>Rusak Berat</span>
                        <span class="text-[10px] font-bold underline opacity-80 group-hover:opacity-100">Detail &rarr;</span>
                    </p>
                </a>
            </div>

            <!-- Row 3: 2 Grid (Kemantapan Jalan: Mantap vs Tidak Mantap) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Card 7: Mantap -->
                <a href="<?= base_url('dashboard/detail?kondisi=mantap') ?>" 
                   title="Klik untuk melihat detail ruas jalan Mantap"
                   class="p-4 rounded-xl border shadow-sm hover:shadow-md transition-all transform hover:-translate-y-0.5 group cursor-pointer block" 
                   style="background-color: #f0fdf4; border-color: #d1fae5;">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full" style="background-color: #10b981; display: inline-block; width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0;"></span>
                            <span class="text-xs font-semibold text-emerald-800 group-hover:underline">Mantap <span class="font-normal text-emerald-600">(Baik + Sedang)</span></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-emerald-700"><?= format_number($pctMantap ?? 0.0, 1) ?>%</span>
                            <span class="text-[10px] font-semibold text-emerald-600 group-hover:underline">Detail &rarr;</span>
                        </div>
                    </div>
                    <h3 class="text-2xl font-bold text-emerald-700"><?= format_number($mantapKm ?? 0.0, 2) ?> <span class="text-xs font-semibold text-emerald-600">km</span></h3>
                    <div class="mt-2.5 w-full rounded-full h-2" style="background-color: rgba(16, 185, 129, 0.2);">
                        <div class="h-2 rounded-full" style="width: <?= number_format($pctMantap ?? 0.0, 4, '.', '') ?>%; background-color: #10b981;"></div>
                    </div>
                </a>

                <!-- Card 8: Tidak Mantap -->
                <a href="<?= base_url('dashboard/detail?kondisi=tidak_mantap') ?>" 
                   title="Klik untuk melihat detail ruas jalan Tidak Mantap"
                   class="p-4 rounded-xl border shadow-sm hover:shadow-md transition-all transform hover:-translate-y-0.5 group cursor-pointer block" 
                   style="background-color: #fff1f2; border-color: #ffe4e6;">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full" style="background-color: #ef4444; display: inline-block; width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0;"></span>
                            <span class="text-xs font-semibold text-rose-800 group-hover:underline">Tidak Mantap <span class="font-normal text-rose-600">(R. Ringan + R. Berat)</span></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-rose-700"><?= format_number($pctTidakMantap ?? 0.0, 1) ?>%</span>
                            <span class="text-[10px] font-semibold text-rose-600 group-hover:underline">Detail &rarr;</span>
                        </div>
                    </div>
                    <h3 class="text-2xl font-bold text-rose-700"><?= format_number($tidakMantapKm ?? 0.0, 2) ?> <span class="text-xs font-semibold text-rose-600">km</span></h3>
                    <div class="mt-2.5 w-full rounded-full h-2" style="background-color: rgba(239, 68, 68, 0.2);">
                        <div class="h-2 rounded-full" style="width: <?= number_format($pctTidakMantap ?? 0.0, 4, '.', '') ?>%; background-color: #ef4444;"></div>
                    </div>
                </a>
            </div>

            <!-- Row 4: 4 Grid (Detail Jenis Perkerasan Jalan: Rigid, Aspal, Agregat/Tanah, Belum Tembus) -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <!-- Card 9: Rigid -->
                <div class="p-4 rounded-xl border shadow-sm hover:shadow-md transition-shadow bg-gray-50 border-gray-200">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full" style="background-color: #6b7280; display: inline-block; width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0;"></span>
                            <span class="text-xs font-semibold text-gray-800">Rigid</span>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-gray-200 text-gray-800 text-[10px] font-bold">
                            <?= format_number($pctRigid ?? 0.0, 1) ?>%
                        </span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-700"><?= format_number($rigidKm ?? 0.0, 2) ?> <span class="text-xs font-normal text-gray-600">km</span></h3>
                    <p class="text-[11px] font-medium text-gray-600 mt-0.5">Rigid / Beton</p>
                </div>

                <!-- Card 10: Aspal -->
                <div class="p-4 rounded-xl border shadow-sm hover:shadow-md transition-shadow bg-slate-900 border-slate-950 text-white">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full" style="background-color: #38bdf8; display: inline-block; width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0;"></span>
                            <span class="text-xs font-semibold text-slate-100">Aspal</span>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-slate-800 text-slate-200 text-[10px] font-bold">
                            <?= format_number($pctAspal ?? 0.0, 1) ?>%
                        </span>
                    </div>
                    <h3 class="text-xl font-bold text-white"><?= format_number($aspalKm ?? 0.0, 2) ?> <span class="text-xs font-normal text-slate-300">km</span></h3>
                    <p class="text-[11px] font-medium text-slate-300 mt-0.5">Flexible / Aspal</p>
                </div>

                <!-- Card 11: Agregat / Tanah -->
                <div class="p-4 rounded-xl border shadow-sm hover:shadow-md transition-shadow" style="background-color: #fefce8; border-color: #fef08a;">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full" style="background-color: #854d0e; display: inline-block; width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0;"></span>
                            <span class="text-xs font-semibold text-amber-900">Agregat/Tanah</span>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-amber-100 text-amber-900 text-[10px] font-bold">
                            <?= format_number($pctAgregatTanah ?? 0.0, 1) ?>%
                        </span>
                    </div>
                    <h3 class="text-xl font-bold text-amber-800"><?= format_number($agregatTanahKm ?? 0.0, 2) ?> <span class="text-xs font-normal text-amber-700">km</span></h3>
                    <p class="text-[11px] font-medium text-amber-700 mt-0.5">Kerikil / Tanah</p>
                </div>

                <!-- Card 12: Belum Tembus -->
                <div class="p-4 rounded-xl border shadow-sm hover:shadow-md transition-shadow" style="background-color: #faf5ff; border-color: #e9d5ff;">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full" style="background-color: #a855f7; display: inline-block; width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0;"></span>
                            <span class="text-xs font-semibold text-purple-900">Belum Tembus</span>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-purple-100 text-purple-900 text-[10px] font-bold">
                            <?= format_number($pctBelumTembus ?? 0.0, 1) ?>%
                        </span>
                    </div>
                    <h3 class="text-xl font-bold text-purple-800"><?= format_number($belumTembusKm ?? 0.0, 2) ?> <span class="text-xs font-normal text-purple-700">km</span></h3>
                    <p class="text-[11px] font-medium text-purple-700 mt-0.5">Hutan / Belum Tembus</p>
                </div>
            </div>

        </div>

        <!-- Right Panel: 2 Pie Charts (Takes 1 Column on LG screens) -->
        <div class="space-y-6">
            
            <!-- Pie Chart 1: Kondisi Jalan -->
            <div class="flex flex-col items-center justify-center rounded-2xl p-5 border min-h-[220px]" style="background-color: rgba(249, 250, 251, 0.6); border-color: #e5e7eb;">
                <h4 class="text-[13px] font-semibold text-gray-500 uppercase tracking-wider mb-4">Kondisi Jalan</h4>
                <style>
                    @keyframes pie-spin-in {
                        from { transform: scale(0) rotate(-90deg); opacity: 0; }
                        to   { transform: scale(1) rotate(0deg);   opacity: 1; }
                    }
                    .pie-chart-container canvas {
                        animation: pie-spin-in 0.8s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
                    }
                    .pie-chart-container {
                        filter: drop-shadow(0 2px 8px rgba(0,0,0,0.06));
                    }
                </style>
                <div class="pie-chart-container w-full max-w-[180px] aspect-square relative">
                    <canvas id="kondisiPieChart"></canvas>
                </div>
                <!-- Legend -->
                <div class="flex flex-wrap justify-center gap-x-3 gap-y-1.5 mt-5">
                    <?php
                        $legendItems = [
                            ['label' => 'Baik',         'color' => '#10b981', 'pct' => $pctBaik,   'val' => $baikKm],
                            ['label' => 'Sedang',       'color' => '#facc15', 'pct' => $pctSedang, 'val' => $sedangKm],
                            ['label' => 'Rusak Ringan', 'color' => '#f97316', 'pct' => $pctRusakRingan, 'val' => $rusakRinganKm],
                            ['label' => 'Rusak Berat',  'color' => '#ef4444', 'pct' => $pctRusakBerat,  'val' => $rusakBeratKm],
                        ];
                    ?>
                    <?php foreach ($legendItems as $li): ?>
                        <?php if ($li['val'] > 0): ?>
                        <div class="flex items-center gap-1.5" style="display: flex; align-items: center; gap: 6px; line-height: 16px;">
                            <span class="w-2.5 h-2.5 rounded-full" style="background-color: <?= $li['color'] ?>; display: inline-block; width: 10px; height: 10px; min-width: 10px; min-height: 10px; border-radius: 50%; flex-shrink: 0; vertical-align: middle; align-self: center;"></span>
                            <span class="text-[11px] font-medium text-gray-600" style="display: inline-block; vertical-align: middle;"><?= $li['label'] ?></span>
                            <span class="text-[10px] text-gray-400" style="display: inline-block; vertical-align: middle;"><?= number_format($li['pct'], 1) ?>%</span>
                        </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Pie Chart 2: Proporsi Kemantapan Jalan -->
            <div class="flex flex-col items-center justify-center rounded-2xl p-5 border min-h-[220px]" style="background-color: rgba(249, 250, 251, 0.6); border-color: #e5e7eb;">
                <h4 class="text-[13px] font-semibold text-gray-500 uppercase tracking-wider mb-4">Kemantapan Jalan</h4>
                <div class="pie-chart-container w-full max-w-[180px] aspect-square relative">
                    <canvas id="kemantapanPieChart"></canvas>
                </div>
                <!-- Legend -->
                <div class="flex flex-wrap justify-center gap-x-4 gap-y-1.5 mt-5">
                    <div class="flex items-center gap-1.5" style="display: flex; align-items: center; gap: 6px; line-height: 16px;">
                        <span class="w-2.5 h-2.5 rounded-full" style="background-color: #10b981; display: inline-block; width: 10px; height: 10px; min-width: 10px; min-height: 10px; border-radius: 50%; flex-shrink: 0; vertical-align: middle; align-self: center;"></span>
                        <span class="text-[11px] font-medium text-gray-600" style="display: inline-block; vertical-align: middle;">Mantap</span>
                        <span class="text-[10px] text-gray-400" style="display: inline-block; vertical-align: middle;"><?= number_format($pctMantap, 1) ?>%</span>
                    </div>
                    <div class="flex items-center gap-1.5" style="display: flex; align-items: center; gap: 6px; line-height: 16px;">
                        <span class="w-2.5 h-2.5 rounded-full" style="background-color: #ef4444; display: inline-block; width: 10px; height: 10px; min-width: 10px; min-height: 10px; border-radius: 50%; flex-shrink: 0; vertical-align: middle; align-self: center;"></span>
                        <span class="text-[11px] font-medium text-gray-600" style="display: inline-block; vertical-align: middle;">Tidak Mantap</span>
                        <span class="text-[10px] text-gray-400" style="display: inline-block; vertical-align: middle;"><?= number_format($pctTidakMantap, 1) ?>%</span>
                    </div>
                </div>
            </div>
            
        </div>
        
    </div>

    <!-- Recent Ruas Table & Filters -->
    <?php if (!empty($ruasList)): ?>
    
    <?php
    // Siapkan data ruas untuk AlpineJS
    $ruasJsonData = [];
    foreach ($ruasList as $ruas) {
        $ruasJsonData[] = [
            'id'             => (int)$ruas['id'],
            'kode_ruas'      => $ruas['kode_ruas'],
            'nama_ruas'      => $ruas['nama_ruas'],
            'sta_awal'       => (float)$ruas['sta_awal'],
            'sta_akhir'      => (float)$ruas['sta_akhir'],
            'sta_awal_str'   => meter_to_sta($ruas['sta_awal']),
            'sta_akhir_str'  => meter_to_sta($ruas['sta_akhir']),
            'panjang'        => (float)$ruas['panjang'],
            'koridor'        => $ruas['koridor'] ?? '',
            'kabupaten_kota' => $ruas['kabupaten_kota'] ?? '',
            'url_stripmap'   => base_url('stripmap/' . $ruas['id']),
            'url_edit'       => base_url('ruas/edit/' . $ruas['id']),
            'url_delete'     => base_url('ruas/delete/' . $ruas['id']),
        ];
    }
    ?>

    <div x-data="dashboardRuasTable(<?= htmlspecialchars(json_encode($ruasJsonData), ENT_QUOTES, 'UTF-8') ?>)" class="space-y-6">
        
        <!-- Filters & Search Panel -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 no-export">
            <div class="flex flex-col md:flex-row gap-4 items-stretch md:items-end">
                
                <!-- Judul Seksi -->
                <div class="md:mb-0 md:mr-4 self-start md:self-center">
                    <h2 class="text-lg font-semibold text-gray-900 whitespace-nowrap">Daftar Ruas Jalan</h2>
                    <p class="text-xs text-gray-500">Filter dan cari data ruas jalan secara langsung.</p>
                </div>

                <!-- Pencarian -->
                <div class="flex-1 min-w-0">
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">Pencarian</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </span>
                        <input type="text" 
                               x-model="searchQuery" 
                               placeholder="Cari nama atau kode ruas..." 
                               class="w-full pl-9 pr-4 py-2 text-sm rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    </div>
                </div>

                <!-- Filter Koridor -->
                <div class="w-full md:w-56">
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">Filter Koridor</label>
                    <select x-model="selectedKoridor" 
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white">
                        <option value="">Semua Koridor</option>
                        <template x-for="koridor in getUniqueKoridor()" :key="koridor">
                            <option :value="koridor" x-text="koridor"></option>
                        </template>
                    </select>
                </div>

                <!-- Filter Kabupaten / Kota -->
                <div class="w-full md:w-56">
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">Filter Wilayah</label>
                    <select x-model="selectedKabupaten" 
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white">
                        <option value="">Semua Kabupaten/Kota</option>
                        <template x-for="kab in getUniqueKabupaten()" :key="kab">
                            <option :value="kab" x-text="kab"></option>
                        </template>
                    </select>
                </div>

                <!-- Reset Button -->
                <div class="w-full md:w-auto">
                    <button type="button" 
                            @click="resetFilters()" 
                            title="Reset Filter"
                            class="w-full md:w-auto inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl border border-gray-200 bg-gray-50 text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition-colors text-sm font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        Reset
                    </button>
                </div>

            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 text-xs font-bold text-gray-500 uppercase tracking-wider">
                            <th class="px-4 py-3.5 w-12 text-center">No</th>
                            <th class="px-5 py-3.5">
                                <button type="button" @click="sortByCol('nama_ruas')" class="flex items-center gap-1.5 hover:text-gray-900 focus:outline-none">
                                    Ruas Jalan
                                    <template x-if="sortBy === 'nama_ruas' || sortBy === 'kode_ruas'">
                                        <svg x-show="sortOrder === 'asc'" class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7l4-4m0 0l4 4m-4-4v18"/></svg>
                                        <svg x-show="sortOrder === 'desc'" class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 17l-4 4m0 0l-4-4m4 4V3"/></svg>
                                    </template>
                                </button>
                            </th>
                            <th class="px-5 py-3.5 hidden md:table-cell">
                                <button type="button" @click="sortByCol('kabupaten_kota')" class="flex items-center gap-1.5 hover:text-gray-900 focus:outline-none">
                                    Lokasi & Koridor
                                </button>
                            </th>
                            <th class="px-5 py-3.5 text-center">
                                <button type="button" @click="sortByCol('panjang')" class="mx-auto flex items-center gap-1.5 hover:text-gray-900 focus:outline-none">
                                    Segmen (STA & Panjang)
                                </button>
                            </th>
                            <th class="px-5 py-3.5 text-right w-48">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        <template x-for="(ruas, index) in paginatedRuas()" :key="ruas.id">
                            <tr class="hover:bg-blue-50/30 transition-colors">
                                <!-- No -->
                                <td class="px-4 py-3.5 text-xs font-semibold text-gray-400 text-center" x-text="(currentPage - 1) * perPage + index + 1"></td>
                                
                                <!-- Ruas Jalan (Kode + Nama stacked) -->
                                <td class="px-5 py-3.5">
                                    <div class="flex flex-col gap-1">
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded bg-blue-50 text-blue-700 text-[11px] font-bold border border-blue-100" x-text="ruas.kode_ruas"></span>
                                            <span class="text-sm font-bold text-gray-900" x-text="ruas.nama_ruas"></span>
                                        </div>
                                        <!-- Mobile only location subtitle -->
                                        <div class="text-xs text-gray-500 md:hidden flex items-center gap-1">
                                            <span x-text="ruas.kabupaten_kota || '-'"></span>
                                            <span>•</span>
                                            <span x-text="ruas.koridor || '-'"></span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Lokasi & Koridor (Desktop) -->
                                <td class="px-5 py-3.5 hidden md:table-cell">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-medium text-gray-800" x-text="ruas.kabupaten_kota || '-'"></span>
                                        <span class="text-xs font-medium text-gray-400" x-text="ruas.koridor ? 'Koridor: ' + ruas.koridor : '-'"></span>
                                    </div>
                                </td>

                                <!-- STA & Panjang -->
                                <td class="px-5 py-3.5 text-center">
                                    <div class="flex flex-col items-center gap-0.5">
                                        <span class="text-xs font-mono font-semibold text-gray-700 bg-gray-100 px-2 py-0.5 rounded" x-text="ruas.sta_awal_str + ' s/d ' + ruas.sta_akhir_str"></span>
                                        <span class="text-[11px] font-bold text-emerald-700" x-text="formatNumber(ruas.panjang) + ' m (' + formatNumber(ruas.panjang / 1000) + ' km)'"></span>
                                    </div>
                                </td>

                                <!-- Action Buttons -->
                                <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- Primary Action: Visual Stripmap -->
                                        <a :href="ruas.url_stripmap"
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 text-white text-xs font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-sm"
                                           title="Buka Visualisasi Strip Map">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2z"/>
                                            </svg>
                                            <span>Strip Map</span>
                                        </a>

                                        <!-- Edit Icon Button -->
                                        <a :href="ruas.url_edit"
                                           class="p-1.5 text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200/60 rounded-lg transition-colors"
                                           title="Edit Ruas Jalan">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>

                                        <!-- Delete Icon Button -->
                                        <a :href="ruas.url_delete"
                                           @click="confirmDelete($event, ruas.url_delete, 'Yakin ingin menghapus ruas ini? Semua data strip map terkait juga akan dihapus.')"
                                           class="p-1.5 text-red-700 bg-red-50 hover:bg-red-100 border border-red-200/60 rounded-lg transition-colors"
                                           title="Hapus Ruas Jalan">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <!-- Bila Filter Tidak Menemukan Apapun -->
                        <tr x-show="filteredRuas().length === 0">
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-400">
                                    <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                                    </svg>
                                    <p class="text-sm font-medium">Tidak ada data ruas jalan yang cocok dengan pencarian/filter.</p>
                                    <button type="button" @click="resetFilters()" class="mt-2 text-xs font-semibold text-blue-600 hover:text-blue-800 hover:underline">Reset Filter & Pencarian</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Pagination Controls -->
            <div x-show="totalPages() > 1" 
                 class="bg-white border-t border-gray-200 px-5 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 no-export">
                <!-- Info Text -->
                <div class="text-xs text-gray-500 font-medium">
                    Menampilkan <span class="font-bold text-gray-900" x-text="(currentPage - 1) * perPage + 1"></span> sampai 
                    <span class="font-bold text-gray-900" x-text="Math.min(currentPage * perPage, filteredRuas().length)"></span> dari 
                    <span class="font-bold text-gray-900" x-text="filteredRuas().length"></span> ruas jalan
                </div>
                
                <!-- Page buttons -->
                <div class="flex items-center gap-1.5 self-center sm:self-auto">
                    <!-- Previous Button -->
                    <button type="button" 
                            @click="currentPage > 1 ? currentPage-- : null"
                            :disabled="currentPage === 1"
                            :class="currentPage === 1 ? 'opacity-40 cursor-not-allowed' : 'hover:bg-gray-50 hover:text-gray-900'"
                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-200 text-gray-500 bg-white transition-colors text-sm font-semibold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>

                    <!-- Page numbers -->
                    <template x-for="p in getPagesToShow()" :key="p">
                        <div class="flex items-center">
                            <template x-if="p === '...'">
                                <span class="px-2 text-gray-400 text-xs font-semibold">...</span>
                            </template>
                            <template x-if="p !== '...'">
                                <button type="button"
                                        @click="currentPage = p"
                                        :class="currentPage === p ? 'bg-blue-600 border-blue-600 text-white shadow-sm' : 'border-gray-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900 bg-white'"
                                        class="inline-flex items-center justify-center min-w-8 h-8 px-2.5 rounded-lg border text-xs font-semibold transition-colors"
                                        x-text="p">
                                </button>
                            </template>
                        </div>
                    </template>

                    <!-- Next Button -->
                    <button type="button" 
                            @click="currentPage < totalPages() ? currentPage++ : null"
                            :disabled="currentPage === totalPages()"
                            :class="currentPage === totalPages() ? 'opacity-40 cursor-not-allowed' : 'hover:bg-gray-50 hover:text-gray-900'"
                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-200 text-gray-500 bg-white transition-colors text-sm font-semibold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

    </div>

    <script>
    function dashboardRuasTable(initialRuasList) {
        return {
            ruasList: initialRuasList,
            currentPage: 1,
            perPage: 10,
            searchQuery: '',
            selectedKoridor: '',
            selectedKabupaten: '',
            sortBy: 'kode_ruas',
            sortOrder: 'asc',

            init() {
                this.$watch('searchQuery', () => this.currentPage = 1);
                this.$watch('selectedKoridor', () => this.currentPage = 1);
                this.$watch('selectedKabupaten', () => this.currentPage = 1);
            },

            totalPages() {
                return Math.ceil(this.filteredRuas().length / this.perPage);
            },

            paginatedRuas() {
                const start = (this.currentPage - 1) * this.perPage;
                const end = start + this.perPage;
                return this.filteredRuas().slice(start, end);
            },

            getPagesToShow() {
                const total = this.totalPages();
                const current = this.currentPage;
                if (total <= 5) {
                    return Array.from({ length: total }, (_, i) => i + 1);
                }
                const pages = [];
                pages.push(1);
                
                let start = Math.max(2, current - 1);
                let end = Math.min(total - 1, current + 1);
                
                if (current <= 2) {
                    end = 3;
                } else if (current >= total - 1) {
                    start = total - 2;
                }
                
                if (start > 2) {
                    pages.push('...');
                }
                
                for (let i = start; i <= end; i++) {
                    pages.push(i);
                }
                
                if (end < total - 1) {
                    pages.push('...');
                }
                
                pages.push(total);
                return pages;
            },

            getUniqueKoridor() {
                const list = this.ruasList.map(r => r.koridor).filter(val => val !== null && val !== '');
                return [...new Set(list)].sort();
            },

            getUniqueKabupaten() {
                const list = this.ruasList.map(r => r.kabupaten_kota).filter(val => val !== null && val !== '');
                return [...new Set(list)].sort();
            },

            sortByCol(col) {
                if (this.sortBy === col) {
                    this.sortOrder = this.sortOrder === 'asc' ? 'desc' : 'asc';
                } else {
                    this.sortBy = col;
                    this.sortOrder = 'asc';
                }
            },

            resetFilters() {
                this.searchQuery = '';
                this.selectedKoridor = '';
                this.selectedKabupaten = '';
                this.sortBy = 'kode_ruas';
                this.sortOrder = 'asc';
                this.currentPage = 1;
            },

            formatNumber(num) {
                return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(num);
            },

            filteredRuas() {
                let result = [...this.ruasList];

                // 1. Search Query
                if (this.searchQuery.trim() !== '') {
                    const query = this.searchQuery.toLowerCase().trim();
                    result = result.filter(r => 
                        r.nama_ruas.toLowerCase().includes(query) || 
                        r.kode_ruas.toLowerCase().includes(query)
                    );
                }

                // 2. Filter Koridor
                if (this.selectedKoridor !== '') {
                    result = result.filter(r => r.koridor === this.selectedKoridor);
                }

                // 3. Filter Kabupaten/Kota
                if (this.selectedKabupaten !== '') {
                    result = result.filter(r => r.kabupaten_kota === this.selectedKabupaten);
                }

                // 4. Sorting
                result.sort((a, b) => {
                    let valA = a[this.sortBy] ? a[this.sortBy].toString().toLowerCase() : '';
                    let valB = b[this.sortBy] ? b[this.sortBy].toString().toLowerCase() : '';

                    if (this.sortBy === 'panjang' || this.sortBy === 'sta_awal' || this.sortBy === 'sta_akhir') {
                        valA = parseFloat(a[this.sortBy]) || 0;
                        valB = parseFloat(b[this.sortBy]) || 0;
                    }

                    if (valA < valB) return this.sortOrder === 'asc' ? -1 : 1;
                    if (valA > valB) return this.sortOrder === 'asc' ? 1 : -1;
                    return 0;
                });

                return result;
            }
        };
    }
    </script>
    <?php else: ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
        </svg>
        <h3 class="text-lg font-semibold text-gray-600 mb-2">Belum ada data ruas jalan</h3>
        <p class="text-sm text-gray-500 mb-6">Mulai dengan menambahkan ruas jalan pertama Anda.</p>
        <a href="<?= base_url('ruas/create') ?>"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-xl hover:bg-blue-700 transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Ruas Jalan
        </a>
    </div>
    <?php endif; ?>
    </div> <!-- End of capture-area -->

    <!-- Inisialisasi Chart.js untuk Pie Charts -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // --------------------------------------------------------
        // Chart 1: Proporsi Kondisi Jalan (Baik, Sedang, RR, RB)
        // --------------------------------------------------------
        const ctx1 = document.getElementById('kondisiPieChart').getContext('2d');

        const chartColors1 = ['#10b981', '#facc15', '#f97316', '#ef4444'];
        const chartLabels1 = ['Baik', 'Sedang', 'Rusak Ringan', 'Rusak Berat'];
        const chartData1   = [
            <?= (float)$baikKm ?>,
            <?= (float)$sedangKm ?>,
            <?= (float)$rusakRinganKm ?>,
            <?= (float)$rusakBeratKm ?>
        ];

        // Filter out zero values
        const filtered1 = chartLabels1.reduce((acc, label, i) => {
            if (chartData1[i] > 0) {
                acc.labels.push(label);
                acc.data.push(chartData1[i]);
                acc.colors.push(chartColors1[i]);
            }
            return acc;
        }, { labels: [], data: [], colors: [] });

        new Chart(ctx1, {
            type: 'pie',
            data: {
                labels: filtered1.labels,
                datasets: [{
                    data: filtered1.data,
                    backgroundColor: filtered1.colors,
                    borderWidth: 2.5,
                    borderColor: '#ffffff',
                    hoverBorderWidth: 3,
                    hoverBorderColor: '#ffffff',
                    hoverOffset: 12
                }]
            },
            plugins: [{
                id: 'modernLabels1',
                afterDraw: (chart) => {
                    const ctxDraw = chart.ctx;
                    const dataset = chart.data.datasets[0];
                    const meta = chart.getDatasetMeta(0);
                    const total = dataset.data.reduce((a, b) => a + b, 0);
                    if (total <= 0) return;

                    meta.data.forEach((element, index) => {
                        const dataVal = dataset.data[index];
                        if (dataVal <= 0) return;

                        const pct = ((dataVal / total) * 100).toFixed(1);
                        const midAngle = element.startAngle + (element.endAngle - element.startAngle) / 2;

                        const innerPt = {
                            x: element.x + Math.cos(midAngle) * (element.outerRadius + 4),
                            y: element.y + Math.sin(midAngle) * (element.outerRadius + 4)
                        };
                        const outerPt = {
                            x: element.x + Math.cos(midAngle) * (element.outerRadius + 16),
                            y: element.y + Math.sin(midAngle) * (element.outerRadius + 16)
                        };

                        ctxDraw.save();
                        ctxDraw.strokeStyle = 'rgba(156,163,175,0.4)';
                        ctxDraw.lineWidth = 0.8;
                        ctxDraw.beginPath();
                        ctxDraw.moveTo(innerPt.x, innerPt.y);
                        ctxDraw.lineTo(outerPt.x, outerPt.y);
                        ctxDraw.stroke();

                        const labelPt = {
                            x: element.x + Math.cos(midAngle) * (element.outerRadius + 24),
                            y: element.y + Math.sin(midAngle) * (element.outerRadius + 24)
                        };

                        ctxDraw.fillStyle = '#374151';
                        ctxDraw.font = '600 10px Inter, system-ui, sans-serif';
                        ctxDraw.textAlign = 'center';
                        ctxDraw.textBaseline = 'middle';
                        ctxDraw.fillText(pct + '%', labelPt.x, labelPt.y);

                        ctxDraw.restore();
                    });
                }
            }],
            options: {
                layout: { padding: 40 },
                responsive: true,
                maintainAspectRatio: true,
                animation: {
                    duration: 1000,
                    easing: 'easeOutQuart'
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(31,41,55,0.95)',
                        titleFont: { family: 'Inter, system-ui, sans-serif', size: 12, weight: '600' },
                        bodyFont: { family: 'Inter, system-ui, sans-serif', size: 11 },
                        padding: { top: 10, bottom: 10, left: 14, right: 14 },
                        cornerRadius: 10,
                        displayColors: true,
                        boxWidth: 10,
                        boxHeight: 10,
                        boxPadding: 4,
                        callbacks: {
                            label: function(context) {
                                const value = context.raw;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return ` ${context.label}: ${new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value)} km (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });

        // --------------------------------------------------------
        // Chart 2: Kemantapan Jalan (Mantap vs Tidak Mantap)
        // --------------------------------------------------------
        const ctx2 = document.getElementById('kemantapanPieChart').getContext('2d');
        const chartData2 = [
            <?= (float)$mantapKm ?>,
            <?= (float)$tidakMantapKm ?>
        ];
        const chartLabels2 = ['Mantap', 'Tidak Mantap'];
        const chartColors2 = ['#10b981', '#ef4444']; // Hijau & Merah

        const filtered2 = chartLabels2.reduce((acc, label, i) => {
            if (chartData2[i] > 0) {
                acc.labels.push(label);
                acc.data.push(chartData2[i]);
                acc.colors.push(chartColors2[i]);
            }
            return acc;
        }, { labels: [], data: [], colors: [] });

        new Chart(ctx2, {
            type: 'pie',
            data: {
                labels: filtered2.labels,
                datasets: [{
                    data: filtered2.data,
                    backgroundColor: filtered2.colors,
                    borderWidth: 2.5,
                    borderColor: '#ffffff',
                    hoverBorderWidth: 3,
                    hoverBorderColor: '#ffffff',
                    hoverOffset: 12
                }]
            },
            plugins: [{
                id: 'modernLabels2',
                afterDraw: (chart) => {
                    const ctxDraw = chart.ctx;
                    const dataset = chart.data.datasets[0];
                    const meta = chart.getDatasetMeta(0);
                    const total = dataset.data.reduce((a, b) => a + b, 0);
                    if (total <= 0) return;

                    meta.data.forEach((element, index) => {
                        const dataVal = dataset.data[index];
                        if (dataVal <= 0) return;

                        const pct = ((dataVal / total) * 100).toFixed(1);
                        const midAngle = element.startAngle + (element.endAngle - element.startAngle) / 2;

                        const innerPt = {
                            x: element.x + Math.cos(midAngle) * (element.outerRadius + 4),
                            y: element.y + Math.sin(midAngle) * (element.outerRadius + 4)
                        };
                        const outerPt = {
                            x: element.x + Math.cos(midAngle) * (element.outerRadius + 16),
                            y: element.y + Math.sin(midAngle) * (element.outerRadius + 16)
                        };

                        ctxDraw.save();
                        ctxDraw.strokeStyle = 'rgba(156,163,175,0.4)';
                        ctxDraw.lineWidth = 0.8;
                        ctxDraw.beginPath();
                        ctxDraw.moveTo(innerPt.x, innerPt.y);
                        ctxDraw.lineTo(outerPt.x, outerPt.y);
                        ctxDraw.stroke();

                        const labelPt = {
                            x: element.x + Math.cos(midAngle) * (element.outerRadius + 24),
                            y: element.y + Math.sin(midAngle) * (element.outerRadius + 24)
                        };

                        ctxDraw.fillStyle = '#374151';
                        ctxDraw.font = '600 10px Inter, system-ui, sans-serif';
                        ctxDraw.textAlign = 'center';
                        ctxDraw.textBaseline = 'middle';
                        ctxDraw.fillText(pct + '%', labelPt.x, labelPt.y);

                        ctxDraw.restore();
                    });
                }
            }],
            options: {
                layout: { padding: 40 },
                responsive: true,
                maintainAspectRatio: true,
                animation: {
                    duration: 1000,
                    easing: 'easeOutQuart'
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(31,41,55,0.95)',
                        titleFont: { family: 'Inter, system-ui, sans-serif', size: 12, weight: '600' },
                        bodyFont: { family: 'Inter, system-ui, sans-serif', size: 11 },
                        padding: { top: 10, bottom: 10, left: 14, right: 14 },
                        cornerRadius: 10,
                        displayColors: true,
                        boxWidth: 10,
                        boxHeight: 10,
                        boxPadding: 4,
                        callbacks: {
                            label: function(context) {
                                const value = context.raw;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return ` ${context.label}: ${new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value)} km (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    });
    </script>

    <!-- html2canvas & jsPDF CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <script>
    function convertCanvasesToImages(container) {
        const canvases = Array.from(container.querySelectorAll('canvas'));
        const replacements = [];

        canvases.forEach(canvas => {
            const dataUrl = canvas.toDataURL('image/png');
            const img = document.createElement('img');
            img.src = dataUrl;
            img.width  = canvas.offsetWidth;
            img.height = canvas.offsetHeight;
            img.style.cssText = `
                display: block;
                width: ${canvas.offsetWidth}px;
                height: ${canvas.offsetHeight}px;
            `;
            canvas.parentNode.insertBefore(img, canvas);
            canvas.style.display = 'none';
            replacements.push({ canvas, img });
        });

        return function restore() {
            replacements.forEach(({ canvas, img }) => {
                canvas.style.display = '';
                img.remove();
            });
        };
    }

    function exportDocument(type) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Mempersiapkan dokumen...',
                text: 'Mohon tunggu sebentar.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }

        const element = document.getElementById('capture-area');
        const restoreCanvases = convertCanvasesToImages(element);

        setTimeout(() => {
            const fileName = 'Dashboard_' + new Date().toISOString().slice(0, 10);

            html2canvas(element, {
                scale: 2, // Menggunakan scale 2 untuk dashboard agar performa terjaga
                useCORS: true,
                allowTaint: false,
                backgroundColor: '#f9fafb',
                logging: false,
                width: element.scrollWidth,
                height: element.scrollHeight,
                windowWidth: document.documentElement.scrollWidth,
                windowHeight: document.documentElement.scrollHeight,
                scrollX: -window.scrollX,
                scrollY: -window.scrollY,
                imageTimeout: 15000,
                onclone: (clonedDoc) => {
                    const clonedEl = clonedDoc.getElementById('capture-area');
                    if (!clonedEl) return;

                    clonedEl.querySelectorAll('.no-export, template, script').forEach(el => {
                        el.remove();
                    });

                    clonedEl.style.borderRadius = '0';
                    clonedEl.style.overflow    = 'visible';

                    clonedEl.querySelectorAll('.flex').forEach(el => {
                        el.style.display = 'flex';
                    });
                    clonedEl.querySelectorAll('.flex-col').forEach(el => {
                        el.style.flexDirection = 'column';
                    });
                    clonedEl.querySelectorAll('.flex-wrap').forEach(el => {
                        el.style.flexWrap = 'wrap';
                    });
                    clonedEl.querySelectorAll('.items-center').forEach(el => {
                        el.style.alignItems = 'center';
                    });
                    clonedEl.querySelectorAll('.items-start').forEach(el => {
                        el.style.alignItems = 'flex-start';
                    });
                    clonedEl.querySelectorAll('.justify-between').forEach(el => {
                        el.style.justifyContent = 'space-between';
                    });
                    clonedEl.querySelectorAll('.justify-center').forEach(el => {
                        el.style.justifyContent = 'center';
                    });

                    clonedEl.querySelectorAll('[class]').forEach(el => {
                        const cls = typeof el.className === 'string' ? el.className : (el.getAttribute('class') || '');
                        const gapMatch = cls.match(/\bgap-(\d+(?:\.\d+)?)\b/);
                        if (gapMatch) {
                            const val = parseFloat(gapMatch[1]) * 4;
                            el.style.gap = val + 'px';
                        }
                        const gapXMatch = cls.match(/\bgap-x-(\d+(?:\.\d+)?)\b/);
                        if (gapXMatch) {
                            const val = parseFloat(gapXMatch[1]) * 4;
                            el.style.columnGap = val + 'px';
                        }
                        const gapYMatch = cls.match(/\bgap-y-(\d+(?:\.\d+)?)\b/);
                        if (gapYMatch) {
                            const val = parseFloat(gapYMatch[1]) * 4;
                            el.style.rowGap = val + 'px';
                        }
                    });

                    clonedEl.querySelectorAll('span.rounded-full').forEach(dot => {
                        dot.style.display    = 'inline-block';
                        dot.style.flexShrink = '0';
                        dot.style.alignSelf  = 'center';
                        dot.style.borderRadius = '50%';

                        const cls = dot.className || '';
                        if (cls.includes('w-2.5') || cls.includes('h-2.5')) {
                            dot.style.width     = '10px';
                            dot.style.height    = '10px';
                            dot.style.minWidth  = '10px';
                            dot.style.minHeight = '10px';
                        } else if (cls.includes('w-3') || cls.includes('h-3')) {
                            dot.style.width     = '12px';
                            dot.style.height    = '12px';
                            dot.style.minWidth  = '12px';
                            dot.style.minHeight = '12px';
                        }

                        dot.style.position = 'relative';
                        dot.style.top      = '6px';
                    });
                }
            }).then(canvas => {
                restoreCanvases();

                const mimeType = type === 'jpeg' ? 'image/jpeg' : 'image/png';
                const quality  = type === 'jpeg' ? 0.95 : 1.0;
                const imgData  = canvas.toDataURL(mimeType, quality);

                if (type === 'pdf') {
                    const { jsPDF } = window.jspdf;
                    const PX_PER_MM = 3.7795275591;
                    const pdfW_mm   = canvas.width  / (2 * PX_PER_MM);
                    const pdfH_mm   = canvas.height / (2 * PX_PER_MM);
                    const orientation = pdfW_mm > pdfH_mm ? 'l' : 'p';

                    const pdf = new jsPDF({
                        orientation: orientation,
                        unit: 'mm',
                        format: [pdfW_mm, pdfH_mm]
                    });

                    pdf.addImage(imgData, 'PNG', 0, 0, pdfW_mm, pdfH_mm, '', 'FAST');
                    pdf.save(fileName + '.pdf');

                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Export Berhasil!',
                            text: 'Dokumen PDF telah diunduh.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                } else {
                    const link = document.createElement('a');
                    link.href = imgData;
                    link.download = fileName + '.' + (type === 'jpeg' ? 'jpg' : 'png');
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);

                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Export Berhasil!',
                            text: 'Gambar telah diunduh.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                }
            }).catch(err => {
                restoreCanvases();
                console.error('html2canvas error:', err);
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Export Gagal',
                        text: 'Terjadi kesalahan saat memproses ekspor. Silakan coba lagi. Error: ' + (err.message || err)
                    });
                }
            });
        }, 300);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const exportParam = urlParams.get('export');
        if (exportParam && ['pdf', 'jpeg', 'png'].includes(exportParam)) {
            const cleanUrl = window.location.pathname;
            window.history.replaceState({}, document.title, cleanUrl);
            setTimeout(function() {
                exportDocument(exportParam);
            }, 400);
        }
    });
    </script>

</div>
