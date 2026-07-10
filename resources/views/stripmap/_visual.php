<!-- ============================================================ -->
<!-- Komponen Strip Map Visual (Reusable Partial) -->
<!-- Digunakan di: stripmap/index, stripmap/preview, ruas/show -->
<!-- ============================================================ -->

<?php
    $totalPanjang = $summary['total_panjang'] ?? 0;
    $totalBaik    = $summary['total_baik'] ?? 0;
    $totalSedang  = $summary['total_sedang'] ?? 0;
    $totalRR      = $summary['total_rusak_ringan'] ?? 0;
    $totalRB      = $summary['total_rusak_berat'] ?? 0;

    $pctBaik   = $totalPanjang > 0 ? ($totalBaik / $totalPanjang) * 100 : 0;
    $pctSedang = $totalPanjang > 0 ? ($totalSedang / $totalPanjang) * 100 : 0;
    $pctRR     = $totalPanjang > 0 ? ($totalRR / $totalPanjang) * 100 : 0;
    $pctRB     = $totalPanjang > 0 ? ($totalRB / $totalPanjang) * 100 : 0;

    $totalMantap      = $totalBaik + $totalSedang;
    $totalTidakMantap = $totalRR + $totalRB;

    $pctMantap      = $totalPanjang > 0 ? ($totalMantap / $totalPanjang) * 100 : 0;
    $pctTidakMantap = $totalPanjang > 0 ? ($totalTidakMantap / $totalPanjang) * 100 : 0;

    // -----------------------------------------------------------------
    // LOGIKA SLICING STRIPMAP MENJADI CHUNKS MAKSIMAL 5KM (TASK 7)
    // -----------------------------------------------------------------
    $staBase = (float)$ruas['sta_awal'];
    $staEnd  = (float)$ruas['sta_akhir'];
    
    // 1. Ekstrak data segmen database menjadi condition runs kontinu
    $runs = [];
    foreach ($stripmaps as $sm) {
        $currentMeter = (float)$sm['sta_awal'];
        
        $conditions = [
            'baik'         => (float)$sm['baik'],
            'sedang'       => (float)$sm['sedang'],
            'rusak_ringan' => (float)$sm['rusak_ringan'],
            'rusak_berat'  => (float)$sm['rusak_berat']
        ];
        
        foreach ($conditions as $condKey => $value) {
            if ($value > 0) {
                $runs[] = [
                    'sta_awal'  => $currentMeter,
                    'sta_akhir' => $currentMeter + $value,
                    'panjang'   => $value,
                    'baik'         => $condKey === 'baik' ? $value : 0.0,
                    'sedang'       => $condKey === 'sedang' ? $value : 0.0,
                    'rusak_ringan' => $condKey === 'rusak_ringan' ? $value : 0.0,
                    'rusak_berat'  => $condKey === 'rusak_berat' ? $value : 0.0,
                ];
                $currentMeter += $value;
            }
        }
    }

    // 2. Bagi range total ruas menjadi beberapa chunk berukuran max 5km (5000 meter)
    $chunkSize = 5000.0;
    $chunks = [];
    $current = $staBase;
    
    while ($current < $staEnd) {
        $chunkEnd = min($current + $chunkSize, $staEnd);
        
        // Distribusikan runs ke dalam chunk ini
        $overlappingRuns = [];
        foreach ($runs as $run) {
            $overlapStart = max($run['sta_awal'], $current);
            $overlapEnd   = min($run['sta_akhir'], $chunkEnd);
            
            if ($overlapStart < $overlapEnd) {
                $overlapLen = $overlapEnd - $overlapStart;
                $overlappingRuns[] = [
                    'sta_awal'     => $overlapStart,
                    'sta_akhir'    => $overlapEnd,
                    'panjang'      => $overlapLen,
                    'baik'         => $run['baik'] > 0 ? $overlapLen : 0.0,
                    'sedang'       => $run['sedang'] > 0 ? $overlapLen : 0.0,
                    'rusak_ringan' => $run['rusak_ringan'] > 0 ? $overlapLen : 0.0,
                    'rusak_berat'  => $run['rusak_berat'] > 0 ? $overlapLen : 0.0,
                ];
            }
        }
        
        // Urutkan overlapping runs berdasarkan sta_awal
        usort($overlappingRuns, fn($a, $b) => $a['sta_awal'] <=> $b['sta_awal']);
        
        // Gabungkan dan isi gap (celah tanpa data) agar rentang dari $current sampai $chunkEnd terisi penuh secara kontinu
        $chunkStripmaps = [];
        $currentPos = $current;
        
        foreach ($overlappingRuns as $run) {
            if ($run['sta_awal'] > $currentPos) {
                // Ada celah (gap) sebelum run ini
                $gapLen = $run['sta_awal'] - $currentPos;
                $chunkStripmaps[] = [
                    'sta_awal'     => $currentPos,
                    'sta_akhir'    => $run['sta_awal'],
                    'panjang'      => $gapLen,
                    'baik'         => 0.0,
                    'sedang'       => 0.0,
                    'rusak_ringan' => 0.0,
                    'rusak_berat'  => 0.0,
                    'is_gap'       => true
                ];
            }
            $chunkStripmaps[] = $run;
            $currentPos = $run['sta_akhir'];
        }
        
        if ($currentPos < $chunkEnd) {
            // Ada celah (gap) di akhir chunk
            $gapLen = $chunkEnd - $currentPos;
            $chunkStripmaps[] = [
                'sta_awal'     => $currentPos,
                'sta_akhir'    => $chunkEnd,
                'panjang'      => $gapLen,
                'baik'         => 0.0,
                'sedang'       => 0.0,
                'rusak_ringan' => 0.0,
                'rusak_berat'  => 0.0,
                'is_gap'       => true
            ];
        }
        
        $chunks[] = [
            'start'      => $current,
            'end'        => $chunkEnd,
            'stripmaps'  => $chunkStripmaps
        ];
        
        $current = $chunkEnd;
    }
?>

<!-- Load Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<?php if ($totalPanjang > 0): ?>
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden" style="background-color: #ffffff; border-color: #e5e7eb;"
     x-data="{ activeLabel: null, activePct: 0, activeChunk: null }"
     @click.outside="activeLabel = null; activeChunk = null">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Visualisasi Strip Map</h3>
        <p class="text-xs text-gray-500 mt-0.5">Total panjang: <?= format_number($totalPanjang) ?> m — Klik atau hover segmen untuk melihat detail kondisi.</p>
    </div>

    <div class="p-6">
        <!-- Main Layout Grid: Kiri Pie Charts, Kanan Line Chart & Stats -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- Kiri: Pie Charts (lg:col-span-4) -->
            <div class="lg:col-span-4 flex flex-col gap-6">
                <!-- Pie Chart 1: Proporsi Kondisi Jalan -->
                <div class="flex flex-col items-center justify-center rounded-2xl p-5 border min-h-[220px]" style="background-color: rgba(249, 250, 251, 0.6); border-color: #e5e7eb;">
                    <h4 class="text-[13px] font-semibold text-gray-500 uppercase tracking-wider mb-4">Proporsi Kondisi Jalan</h4>
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
                        <canvas id="conditionPieChart"></canvas>
                    </div>
                    <!-- Legend -->
                    <div class="flex flex-wrap justify-center gap-x-3 gap-y-1.5 mt-5">
                        <?php
                            $legendItems = [
                                ['label' => 'Baik',         'color' => '#10b981', 'pct' => $pctBaik,   'val' => $totalBaik],
                                ['label' => 'Sedang',       'color' => '#facc15', 'pct' => $pctSedang, 'val' => $totalSedang],
                                ['label' => 'Rusak Ringan', 'color' => '#f97316', 'pct' => $pctRR,     'val' => $totalRR],
                                ['label' => 'Rusak Berat',  'color' => '#ef4444', 'pct' => $pctRB,     'val' => $totalRB],
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

                <!-- Pie Chart 2: Kemantapan Jalan -->
                <div class="flex flex-col items-center justify-center rounded-2xl p-5 border min-h-[220px]" style="background-color: rgba(249, 250, 251, 0.6); border-color: #e5e7eb;">
                    <h4 class="text-[13px] font-semibold text-gray-500 uppercase tracking-wider mb-4">Kemantapan Jalan</h4>
                    <div class="pie-chart-container w-full max-w-[180px] aspect-square relative">
                        <canvas id="stabilityPieChart"></canvas>
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

            <!-- Kanan: Line Chart & Stats (lg:col-span-8) -->
            <div class="lg:col-span-8 flex flex-col space-y-3">
                <!-- Linear Strip Map Chunks (Max 5 km per bar, sesuai Task 7) -->
                <div class="space-y-1.5">
                    <?php foreach ($chunks as $chunkIdx => $chunk): ?>
                        <?php 
                            $chunkTotalPanjang = $chunk['end'] - $chunk['start']; 
                            if ($chunkTotalPanjang <= 0) continue;
                        ?>
                        <div class="space-y-1 border-b border-gray-100 pb-1.5 last:border-0 last:pb-0">

                            <!-- Container Bar Jalur Linear (Dipertipis ke h-5) -->
                            <div class="flex h-5 rounded-lg overflow-hidden shadow bg-gray-100 border border-gray-200">
                                <?php
                                    $cumulativePct = 0;
                                ?>
                                <?php foreach ($chunk['stripmaps'] as $sm): ?>
                                    <?php
                                        $smTotal = $sm['panjang'] > 0 ? $sm['panjang'] : 1;
                                        $smPct   = $chunkTotalPanjang > 0 ? ($sm['panjang'] / $chunkTotalPanjang) * 100 : 0;
                                    ?>
                                    <div class="flex h-full flex-shrink-0" style="width: <?= number_format($smPct, 4, '.', '') ?>%">
                                        <?php if (!empty($sm['is_gap'])): ?>
                                            <?php
                                                $subWidthGlobal = ($sm['panjang'] / $chunkTotalPanjang) * 100;
                                                $subMidPct = $cumulativePct + ($subWidthGlobal / 2);
                                                $cumulativePct += $subWidthGlobal;
                                                $staLabelStr = meter_to_sta($sm['sta_awal']) . ' — ' . meter_to_sta($sm['sta_akhir']);
                                            ?>
                                            <div class="h-full w-full flex-shrink-0 relative group transition-all duration-300 cursor-pointer bg-gray-50"
                                                 @mouseenter="if (window.matchMedia('(hover: hover)').matches) { activeLabel = { panjang: '<?= format_number($sm['panjang']) ?>', kondisi: 'Belum Ada Data', sta: '<?= $staLabelStr ?>', color: '#9ca3af' }; activePct = <?= round($subMidPct, 2) ?>; activeChunk = <?= $chunkIdx ?> }"
                                                 @mouseleave="if (window.matchMedia('(hover: hover)').matches) { activeLabel = null; activeChunk = null }"
                                                 @click.stop="activeLabel = (activeLabel && activeLabel.sta === '<?= $staLabelStr ?>' && activeLabel.kondisi === 'Belum Ada Data') ? null : { panjang: '<?= format_number($sm['panjang']) ?>', kondisi: 'Belum Ada Data', sta: '<?= $staLabelStr ?>', color: '#9ca3af' }; activePct = <?= round($subMidPct, 2) ?>; activeChunk = <?= $chunkIdx ?>"`,StartLine:254,TargetContent:>
                                                 
                                                 <!-- Diagonal stripes for gap block -->
                                                 <div class="absolute inset-0 bg-[linear-gradient(45deg,#e5e7eb_25%,transparent_25%,transparent_50%,#e5e7eb_50%,#e5e7eb_75%,transparent_75%,transparent)] bg-[length:10px_10px] opacity-40"></div>
                                                 <div class="absolute inset-0 ring-2 ring-white/60 ring-inset opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                            </div>
                                        <?php else: ?>
                                            <?php
                                                $segConditions = [
                                                    ['label' => 'Baik',         'value' => $sm['baik'],         'color' => '#10b981'],
                                                    ['label' => 'Sedang',       'value' => $sm['sedang'],       'color' => '#facc15'],
                                                    ['label' => 'Rusak Ringan', 'value' => $sm['rusak_ringan'], 'color' => '#f97316'],
                                                    ['label' => 'Rusak Berat',  'value' => $sm['rusak_berat'],  'color' => '#ef4444'],
                                                ];
                                                $activeSeg = array_values(array_filter($segConditions, fn($c) => $c['value'] > 0));
                                            ?>
                                            <?php foreach ($activeSeg as $sc): ?>
                                                <?php
                                                    $scPct = ($sc['value'] / $smTotal) * 100;
                                                    // Hitung posisi tengah sub-segmen secara global
                                                    $subWidthGlobal = ($sc['value'] / $chunkTotalPanjang) * 100;
                                                    $subMidPct = $cumulativePct + ($subWidthGlobal / 2);
                                                    $cumulativePct += $subWidthGlobal;
                                                    $staLabelStr = meter_to_sta($sm['sta_awal']) . ' — ' . meter_to_sta($sm['sta_akhir']);
                                                ?>
                                                <div class="h-full flex-shrink-0 relative group transition-all duration-300 cursor-pointer"
                                                     style="width: <?= number_format($scPct, 4, '.', '') ?>%; background-color: <?= $sc['color'] ?>;"
                                                     @mouseenter="if (window.matchMedia('(hover: hover)').matches) { activeLabel = { panjang: '<?= format_number($sc['value']) ?>', kondisi: '<?= $sc['label'] ?>', sta: '<?= $staLabelStr ?>', color: '<?= $sc['color'] ?>' }; activePct = <?= round($subMidPct, 2) ?>; activeChunk = <?= $chunkIdx ?> }"
                                                     @mouseleave="if (window.matchMedia('(hover: hover)').matches) { activeLabel = null; activeChunk = null }"
                                                     @click.stop="activeLabel = (activeLabel && activeLabel.sta === '<?= $staLabelStr ?>' && activeLabel.kondisi === '<?= $sc['label'] ?>') ? null : { panjang: '<?= format_number($sc['value']) ?>', kondisi: '<?= $sc['label'] ?>', sta: '<?= $staLabelStr ?>', color: '<?= $sc['color'] ?>' }; activePct = <?= round($subMidPct, 2) ?>; activeChunk = <?= $chunkIdx ?>">

                                                     <!-- Highlight ring saat aktif -->
                                                     <div class="absolute inset-0 ring-2 ring-white/60 ring-inset opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Label Keterangan -->
                            <div class="relative w-full h-0 z-20">
                                <template x-if="activeLabel && activeChunk === <?= $chunkIdx ?>">
                                    <div class="absolute top-1 flex flex-col items-center -translate-x-1/2 transition-all duration-150 ease-out"
                                         :style="'left:' + activePct + '%'">
                                        <!-- Garis penunjuk vertikal -->
                                        <div class="w-px h-3" :style="'background-color:' + activeLabel.color"></div>
                                        <!-- Kotak info -->
                                        <div class="mt-0.5 px-2.5 py-1.5 rounded-lg border shadow-sm text-center whitespace-nowrap backdrop-blur-sm"
                                             :style="'border-color:' + activeLabel.color + '40; background-color:' + activeLabel.color + '15'">
                                            <p class="text-xs font-bold" :style="'color:' + activeLabel.color" x-text="activeLabel.panjang + ' m'"></p>
                                            <p class="text-[10px] font-semibold text-gray-600" x-text="activeLabel.kondisi"></p>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <!-- Skala Penggaris STA (Setiap 250m) -->
                            <?php
                                $filteredTicks = [];
                                $filteredTicks[] = ['meter' => $chunk['start'], 'pct' => 0];

                                $firstMultiple = ceil($chunk['start'] / 250) * 250;
                                if ($firstMultiple == $chunk['start']) {
                                    $firstMultiple += 250;
                                }

                                for ($m = $firstMultiple; $m < $chunk['end']; $m += 250) {
                                    $pct = (($m - $chunk['start']) / $chunkTotalPanjang) * 100;
                                    // Berikan toleransi jarak minimal (50m) agar tidak bertabrakan dengan ujung
                                    if (($m - $chunk['start']) >= 50 && ($chunk['end'] - $m) >= 50) {
                                        $filteredTicks[] = [
                                            'meter' => $m,
                                            'pct'   => $pct
                                        ];
                                    }
                                }

                                if ($chunk['end'] > $chunk['start']) {
                                    $filteredTicks[] = ['meter' => $chunk['end'], 'pct' => 100];
                                }
                            ?>

                            <div class="relative w-full h-6">
                                <div class="absolute top-0 left-0 right-0 h-px bg-gray-300"></div>
                                <?php foreach ($filteredTicks as $tick): ?>
                                    <div class="absolute top-0 -translate-x-1/2 flex flex-col items-center" style="left: <?= number_format($tick['pct'], 4, '.', '') ?>%">
                                        <div class="w-px h-2 bg-gray-400"></div>
                                        <span class="text-[10px] font-mono font-semibold text-gray-500 mt-0.5"><?= meter_to_sta($tick['meter']) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Stats Grid: Dipindahkan ke dalam kolom kanan agar mengisi space kosong -->
                <div class="pt-6 border-t border-gray-100 space-y-6">
                    <!-- Row 1: 4 Kondisi Jalan -->
                    <div>
                        <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Detail Kondisi Segmen</h4>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                            <!-- Baik -->
                            <div class="p-4 rounded-xl border shadow-sm" style="background-color: #f0fdf4; border-color: #d1fae5;">
                                <div class="flex items-center gap-2 mb-2" style="display: flex; align-items: center; gap: 8px; line-height: 16px;">
                                    <span class="w-2.5 h-2.5 rounded-full" style="background-color: #10b981; display: inline-block; width: 10px; height: 10px; min-width: 10px; min-height: 10px; border-radius: 50%; flex-shrink: 0; vertical-align: middle; align-self: center;"></span>
                                    <span class="text-xs font-semibold text-emerald-800" style="display: inline-block; vertical-align: middle;">Baik</span>
                                </div>
                                <p class="text-xl font-bold text-emerald-700"><?= format_number($totalBaik) ?> <span class="text-xs font-normal text-emerald-600">m</span></p>
                                <p class="text-xs text-emerald-600 mt-1"><?= number_format($pctBaik, 1) ?>%</p>
                            </div>
                            <!-- Sedang -->
                            <div class="p-4 rounded-xl border shadow-sm" style="background-color: #fefce8; border-color: #fef08a;">
                                <div class="flex items-center gap-2 mb-2" style="display: flex; align-items: center; gap: 8px; line-height: 16px;">
                                    <span class="w-2.5 h-2.5 rounded-full" style="background-color: #facc15; display: inline-block; width: 10px; height: 10px; min-width: 10px; min-height: 10px; border-radius: 50%; flex-shrink: 0; vertical-align: middle; align-self: center;"></span>
                                    <span class="text-xs font-semibold text-yellow-800" style="display: inline-block; vertical-align: middle;">Sedang</span>
                                </div>
                                <p class="text-xl font-bold text-yellow-700"><?= format_number($totalSedang) ?> <span class="text-xs font-normal text-yellow-600">m</span></p>
                                <p class="text-xs text-yellow-600 mt-1"><?= number_format($pctSedang, 1) ?>%</p>
                            </div>
                            <!-- Rusak Ringan -->
                            <div class="p-4 rounded-xl border shadow-sm" style="background-color: #fff7ed; border-color: #ffedd5;">
                                <div class="flex items-center gap-2 mb-2" style="display: flex; align-items: center; gap: 8px; line-height: 16px;">
                                    <span class="w-2.5 h-2.5 rounded-full" style="background-color: #f97316; display: inline-block; width: 10px; height: 10px; min-width: 10px; min-height: 10px; border-radius: 50%; flex-shrink: 0; vertical-align: middle; align-self: center;"></span>
                                    <span class="text-xs font-semibold text-orange-800" style="display: inline-block; vertical-align: middle;">Rusak Ringan</span>
                                </div>
                                <p class="text-xl font-bold text-orange-700"><?= format_number($totalRR) ?> <span class="text-xs font-normal text-orange-600">m</span></p>
                                <p class="text-xs text-orange-600 mt-1"><?= number_format($pctRR, 1) ?>%</p>
                            </div>
                            <!-- Rusak Berat -->
                            <div class="p-4 rounded-xl border shadow-sm" style="background-color: #fef2f2; border-color: #fee2e2;">
                                <div class="flex items-center gap-2 mb-2" style="display: flex; align-items: center; gap: 8px; line-height: 16px;">
                                    <span class="w-2.5 h-2.5 rounded-full" style="background-color: #ef4444; display: inline-block; width: 10px; height: 10px; min-width: 10px; min-height: 10px; border-radius: 50%; flex-shrink: 0; vertical-align: middle; align-self: center;"></span>
                                    <span class="text-xs font-semibold text-red-800" style="display: inline-block; vertical-align: middle;">Rusak Berat</span>
                                </div>
                                <p class="text-xl font-bold text-red-700"><?= format_number($totalRB) ?> <span class="text-xs font-normal text-red-600">m</span></p>
                                <p class="text-xs text-red-600 mt-1"><?= number_format($pctRB, 1) ?>%</p>
                            </div>
                        </div>
                    </div>

                    <!-- Row 2: 2 Kemantapan Jalan -->
                    <div>
                        <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Kemantapan Jalan (Stability)</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Mantap -->
                            <div class="p-4 rounded-xl border shadow-sm" style="background-color: #f0fdf4; border-color: #d1fae5;">
                                <div class="flex items-center justify-between mb-2" style="display: flex; align-items: center; justify-content: space-between; line-height: 16px;">
                                    <div class="flex items-center gap-2" style="display: flex; align-items: center; gap: 8px;">
                                        <span class="w-2.5 h-2.5 rounded-full" style="background-color: #10b981; display: inline-block; width: 10px; height: 10px; min-width: 10px; min-height: 10px; border-radius: 50%; flex-shrink: 0; vertical-align: middle; align-self: center;"></span>
                                        <span class="text-xs font-semibold text-emerald-800" style="display: inline-block; vertical-align: middle;">Mantap <span class="font-normal text-emerald-600">(Baik + Sedang)</span></span>
                                    </div>
                                    <span class="text-xs font-bold text-emerald-600"><?= number_format($pctMantap, 1) ?>%</span>
                                </div>
                                <p class="text-2xl font-extrabold text-emerald-700"><?= format_number($totalMantap) ?> <span class="text-sm font-semibold text-emerald-600">m</span></p>
                                <div class="mt-3 w-full rounded-full h-2" style="background-color: rgba(16, 185, 129, 0.2);">
                                    <div class="h-2 rounded-full" style="width: <?= number_format($pctMantap, 4, '.', '') ?>%; background-color: #10b981;"></div>
                                </div>
                            </div>
                            <!-- Tidak Mantap -->
                            <div class="p-4 rounded-xl border shadow-sm" style="background-color: #fff1f2; border-color: #ffe4e6;">
                                <div class="flex items-center justify-between mb-2" style="display: flex; align-items: center; justify-content: space-between; line-height: 16px;">
                                    <div class="flex items-center gap-2" style="display: flex; align-items: center; gap: 8px;">
                                        <span class="w-2.5 h-2.5 rounded-full" style="background-color: #ef4444; display: inline-block; width: 10px; height: 10px; min-width: 10px; min-height: 10px; border-radius: 50%; flex-shrink: 0; vertical-align: middle; align-self: center;"></span>
                                        <span class="text-xs font-semibold text-rose-800" style="display: inline-block; vertical-align: middle;">Tidak Mantap <span class="font-normal text-rose-600">(Rusak Ringan + Rusak Berat)</span></span>
                                    </div>
                                    <span class="text-xs font-bold text-rose-600"><?= number_format($pctTidakMantap, 1) ?>%</span>
                                </div>
                                <p class="text-2xl font-extrabold text-rose-700"><?= format_number($totalTidakMantap) ?> <span class="text-sm font-semibold text-rose-600">m</span></p>
                                <div class="mt-3 w-full rounded-full h-2" style="background-color: rgba(239, 68, 68, 0.2);">
                                    <div class="h-2 rounded-full" style="width: <?= number_format($pctTidakMantap, 4, '.', '') ?>%; background-color: #ef4444;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const isPreview = window.location.pathname.includes('/preview/');
    // --------------------------------------------------------
    // Chart 1: Proporsi Kondisi Jalan (Baik, Sedang, RR, RB)
    // --------------------------------------------------------
    const ctx1 = document.getElementById('conditionPieChart').getContext('2d');

    const chartColors1 = ['#10b981', '#facc15', '#f97316', '#ef4444'];
    const chartLabels1 = ['Baik', 'Sedang', 'Rusak Ringan', 'Rusak Berat'];
    const chartData1   = [
        <?= (float)$totalBaik ?>,
        <?= (float)$totalSedang ?>,
        <?= (float)$totalRR ?>,
        <?= (float)$totalRB ?>
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
                const ctx2 = chart.ctx;
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

                    ctx2.save();
                    ctx2.strokeStyle = 'rgba(156,163,175,0.4)';
                    ctx2.lineWidth = 0.8;
                    ctx2.beginPath();
                    ctx2.moveTo(innerPt.x, innerPt.y);
                    ctx2.lineTo(outerPt.x, outerPt.y);
                    ctx2.stroke();

                    const labelPt = {
                        x: element.x + Math.cos(midAngle) * (element.outerRadius + 24),
                        y: element.y + Math.sin(midAngle) * (element.outerRadius + 24)
                    };

                    ctx2.fillStyle = '#374151';
                    ctx2.font = '600 10px Inter, system-ui, sans-serif';
                    ctx2.textAlign = 'center';
                    ctx2.textBaseline = 'middle';
                    ctx2.fillText(pct + '%', labelPt.x, labelPt.y);

                    ctx2.restore();
                });
            }
        }],
        options: {
            layout: { padding: 40 },
            responsive: true,
            maintainAspectRatio: true,
            animation: {
                animateRotate: !isPreview,
                animateScale: !isPreview,
                duration: isPreview ? 0 : 1000,
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
                            return ` ${context.label}: ${new Intl.NumberFormat('id-ID').format(value)} m (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });

    // --------------------------------------------------------
    // Chart 2: Kemantapan Jalan (Mantap vs Tidak Mantap)
    // --------------------------------------------------------
    const ctx2 = document.getElementById('stabilityPieChart').getContext('2d');
    const chartData2 = [
        <?= (float)$totalMantap ?>,
        <?= (float)$totalTidakMantap ?>
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
                const ctx2Draw = chart.ctx;
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

                    ctx2Draw.save();
                    ctx2Draw.strokeStyle = 'rgba(156,163,175,0.4)';
                    ctx2Draw.lineWidth = 0.8;
                    ctx2Draw.beginPath();
                    ctx2Draw.moveTo(innerPt.x, innerPt.y);
                    ctx2Draw.lineTo(outerPt.x, outerPt.y);
                    ctx2Draw.stroke();

                    const labelPt = {
                        x: element.x + Math.cos(midAngle) * (element.outerRadius + 24),
                        y: element.y + Math.sin(midAngle) * (element.outerRadius + 24)
                    };

                    ctx2Draw.fillStyle = '#374151';
                    ctx2Draw.font = '600 10px Inter, system-ui, sans-serif';
                    ctx2Draw.textAlign = 'center';
                    ctx2Draw.textBaseline = 'middle';
                    ctx2Draw.fillText(pct + '%', labelPt.x, labelPt.y);

                    ctx2Draw.restore();
                });
            }
        }],
        options: {
            layout: { padding: 40 },
            responsive: true,
            maintainAspectRatio: true,
            animation: {
                animateRotate: !isPreview,
                animateScale: !isPreview,
                duration: isPreview ? 0 : 1000,
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
                            return ` ${context.label}: ${new Intl.NumberFormat('id-ID').format(value)} m (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });

});
</script>
<?php endif; ?>
