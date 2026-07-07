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
?>

<?php if ($totalPanjang > 0): ?>
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
     x-data="{ hoveredSegment: null }">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Visualisasi Strip Map</h3>
        <p class="text-xs text-gray-500 mt-0.5">Total panjang: <?= format_number($totalPanjang) ?> m — Hover untuk melihat detail.</p>
    </div>
    <div class="p-6 space-y-6">

        <!-- Main Strip Bar -->
        <div class="space-y-2">
            <div class="flex items-center justify-between text-xs text-gray-500 font-mono">
                <span><?= meter_to_sta($ruas['sta_awal']) ?></span>
                <span><?= meter_to_sta($ruas['sta_akhir']) ?></span>
            </div>

            <div class="flex h-14 rounded-xl overflow-hidden shadow-md">
                <?php
                $conditions = [
                    ['label' => 'Baik',         'value' => $totalBaik,   'pct' => $pctBaik,   'color' => '#22c55e'],
                    ['label' => 'Sedang',       'value' => $totalSedang, 'pct' => $pctSedang, 'color' => '#eab308'],
                    ['label' => 'Rusak Ringan', 'value' => $totalRR,     'pct' => $pctRR,     'color' => '#f97316'],
                    ['label' => 'Rusak Berat',  'value' => $totalRB,     'pct' => $pctRB,     'color' => '#ef4444'],
                ];

                // Filter hanya kondisi yang punya nilai > 0
                $activeConditions = array_values(array_filter($conditions, fn($c) => $c['value'] > 0));
                $activeCount = count($activeConditions);
                ?>
                <?php foreach ($activeConditions as $idx => $cond): ?>
                    <?php
                        $currentColor = $cond['color'];
                        $nextColor    = ($idx < $activeCount - 1) ? $activeConditions[$idx + 1]['color'] : $currentColor;
                        // Gradasi: 70% warna sendiri solid, 30% terakhir transisi ke warna berikutnya
                        $bgStyle = "background: linear-gradient(to right, {$currentColor} 60%, {$nextColor} 100%)";
                    ?>
                    <div class="relative group transition-all duration-500 ease-out"
                         style="width: <?= number_format($cond['pct'], 2) ?>%; <?= $bgStyle ?>"
                         @mouseenter="hoveredSegment = '<?= $cond['label'] ?>'"
                         @mouseleave="hoveredSegment = null">

                        <!-- Label di dalam bar (jika cukup lebar) -->
                        <?php if ($cond['pct'] > 10): ?>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="text-white text-xs font-bold drop-shadow-sm"><?= number_format($cond['pct'], 1) ?>%</span>
                        </div>
                        <?php endif; ?>

                        <!-- Tooltip on hover -->
                        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-4 py-2.5 bg-gray-900 text-white text-xs rounded-xl opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-10 shadow-lg">
                            <p class="font-bold text-sm"><?= $cond['label'] ?></p>
                            <p class="mt-1">Panjang: <?= format_number($cond['value']) ?> m</p>
                            <p>Persentase: <?= number_format($cond['pct'], 1) ?>%</p>
                            <div class="absolute top-full left-1/2 -translate-x-1/2 -mt-1 border-4 border-transparent border-t-gray-900"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Per-segment strip bars (if multiple segments) -->
        <?php if (count($stripmaps) > 1): ?>
        <div class="space-y-3">
            <h4 class="text-sm font-semibold text-gray-700">Detail Per Segmen</h4>
            <?php foreach ($stripmaps as $sm): ?>
                <?php
                    $smTotal = $sm['panjang'] > 0 ? $sm['panjang'] : 1;
                    $smPctB  = ($sm['baik'] / $smTotal) * 100;
                    $smPctS  = ($sm['sedang'] / $smTotal) * 100;
                    $smPctRR = ($sm['rusak_ringan'] / $smTotal) * 100;
                    $smPctRB = ($sm['rusak_berat'] / $smTotal) * 100;
                ?>
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-mono text-gray-500"><?= meter_to_sta($sm['sta_awal']) ?> — <?= meter_to_sta($sm['sta_akhir']) ?></span>
                        <span class="text-xs text-gray-400"><?= format_number($sm['panjang']) ?> m</span>
                    </div>
                    <div class="flex h-6 rounded-lg overflow-hidden shadow-sm">
                        <?php
                            $segConditions = [
                                ['value' => $sm['baik'],         'pct' => $smPctB,  'color' => '#22c55e'],
                                ['value' => $sm['sedang'],       'pct' => $smPctS,  'color' => '#eab308'],
                                ['value' => $sm['rusak_ringan'], 'pct' => $smPctRR, 'color' => '#f97316'],
                                ['value' => $sm['rusak_berat'],  'pct' => $smPctRB, 'color' => '#ef4444'],
                            ];
                            $activeSeg = array_values(array_filter($segConditions, fn($c) => $c['value'] > 0));
                            $activeSegCount = count($activeSeg);
                        ?>
                        <?php foreach ($activeSeg as $idx => $sc): ?>
                            <?php
                                $currColor = $sc['color'];
                                $nextColor = ($idx < $activeSegCount - 1) ? $activeSeg[$idx + 1]['color'] : $currColor;
                                $bgStyle = "background: linear-gradient(to right, {$currColor} 60%, {$nextColor} 100%)";
                            ?>
                            <div class="transition-all duration-500" style="width:<?= number_format($sc['pct'], 2) ?>%; <?= $bgStyle ?>"></div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Legend + Stats -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <!-- Baik -->
            <div class="p-4 rounded-xl bg-green-50 border border-green-100">
                <div class="flex items-center gap-2 mb-2">
                    <span class="w-3 h-3 rounded-full" style="background:#22c55e"></span>
                    <span class="text-xs font-semibold text-green-800">Baik</span>
                </div>
                <p class="text-xl font-bold text-green-700"><?= format_number($totalBaik) ?> <span class="text-xs font-normal">m</span></p>
                <p class="text-xs text-green-600 mt-1"><?= number_format($pctBaik, 1) ?>%</p>
            </div>
            <!-- Sedang -->
            <div class="p-4 rounded-xl bg-yellow-50 border border-yellow-100">
                <div class="flex items-center gap-2 mb-2">
                    <span class="w-3 h-3 rounded-full" style="background:#eab308"></span>
                    <span class="text-xs font-semibold text-yellow-800">Sedang</span>
                </div>
                <p class="text-xl font-bold text-yellow-700"><?= format_number($totalSedang) ?> <span class="text-xs font-normal">m</span></p>
                <p class="text-xs text-yellow-600 mt-1"><?= number_format($pctSedang, 1) ?>%</p>
            </div>
            <!-- Rusak Ringan -->
            <div class="p-4 rounded-xl bg-orange-50 border border-orange-100">
                <div class="flex items-center gap-2 mb-2">
                    <span class="w-3 h-3 rounded-full" style="background:#f97316"></span>
                    <span class="text-xs font-semibold text-orange-800">Rusak Ringan</span>
                </div>
                <p class="text-xl font-bold text-orange-700"><?= format_number($totalRR) ?> <span class="text-xs font-normal">m</span></p>
                <p class="text-xs text-orange-600 mt-1"><?= number_format($pctRR, 1) ?>%</p>
            </div>
            <!-- Rusak Berat -->
            <div class="p-4 rounded-xl bg-red-50 border border-red-100">
                <div class="flex items-center gap-2 mb-2">
                    <span class="w-3 h-3 rounded-full" style="background:#ef4444"></span>
                    <span class="text-xs font-semibold text-red-800">Rusak Berat</span>
                </div>
                <p class="text-xl font-bold text-red-700"><?= format_number($totalRB) ?> <span class="text-xs font-normal">m</span></p>
                <p class="text-xs text-red-600 mt-1"><?= number_format($pctRB, 1) ?>%</p>
            </div>
        </div>

    </div>
</div>
<?php endif; ?>
