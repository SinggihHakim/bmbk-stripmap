<!-- ============================================================ -->
<!-- Komponen Strip Map Visual & Perkerasan (Reusable Partial)    -->
<!-- Digunakan di: stripmap/index, stripmap/preview, ruas/show    -->
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

    // Data Perkerasan
    $perkerasans           = $perkerasans ?? [];
    $summaryPerkerasan     = $summaryPerkerasan ?? [];
    $totalRigid            = $summaryPerkerasan['total_rigid'] ?? 0;
    $totalAspal            = $summaryPerkerasan['total_aspal'] ?? 0;
    $totalAgregatTanah     = $summaryPerkerasan['total_agregat_tanah'] ?? 0;
    $totalBelumTembus      = $summaryPerkerasan['total_belum_tembus'] ?? 0;
    $totalPanjangPerkerasan= $summaryPerkerasan['total_panjang'] ?? 0;

    $pctRigid        = $totalPanjangPerkerasan > 0 ? ($totalRigid / $totalPanjangPerkerasan) * 100 : 0;
    $pctAspal        = $totalPanjangPerkerasan > 0 ? ($totalAspal / $totalPanjangPerkerasan) * 100 : 0;
    $pctAgregatTanah = $totalPanjangPerkerasan > 0 ? ($totalAgregatTanah / $totalPanjangPerkerasan) * 100 : 0;
    $pctBelumTembus  = $totalPanjangPerkerasan > 0 ? ($totalBelumTembus / $totalPanjangPerkerasan) * 100 : 0;

    // -----------------------------------------------------------------
    // LOGIKA SLICING CHUNKS MAKSIMAL 5KM (5000 METER)
    // -----------------------------------------------------------------
    $staBase = (float)$ruas['sta_awal'];
    $staEnd  = (float)$ruas['sta_akhir'];
    foreach ($stripmaps as $sm) {
        if ((float)$sm['sta_akhir'] > $staEnd) {
            $staEnd = (float)$sm['sta_akhir'];
        }
    }
    foreach ($perkerasans as $pk) {
        if ((float)$pk['sta_akhir'] > $staEnd) {
            $staEnd = (float)$pk['sta_akhir'];
        }
    }
    
    // 1. Ekstrak data stripmap (Kondisi)
    $smRuns = [];
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
                $smRuns[] = [
                    'sta_awal'     => $currentMeter,
                    'sta_akhir'    => $currentMeter + $value,
                    'panjang'      => $value,
                    'baik'         => $condKey === 'baik' ? $value : 0.0,
                    'sedang'       => $condKey === 'sedang' ? $value : 0.0,
                    'rusak_ringan' => $condKey === 'rusak_ringan' ? $value : 0.0,
                    'rusak_berat'  => $condKey === 'rusak_berat' ? $value : 0.0,
                ];
                $currentMeter += $value;
            }
        }
    }

    // 2. Ekstrak data perkerasan (Jenis Perkerasan)
    $pkRuns = [];
    foreach ($perkerasans as $pk) {
        $currentMeter = (float)$pk['sta_awal'];
        $pavements = [
            'rigid'         => (float)$pk['rigid'],
            'aspal'         => (float)$pk['aspal'],
            'agregat_tanah' => (float)$pk['agregat_tanah'],
            'belum_tembus'  => (float)$pk['belum_tembus']
        ];
        foreach ($pavements as $paveKey => $value) {
            if ($value > 0) {
                $pkRuns[] = [
                    'sta_awal'      => $currentMeter,
                    'sta_akhir'     => $currentMeter + $value,
                    'panjang'       => $value,
                    'rigid'         => $paveKey === 'rigid' ? $value : 0.0,
                    'aspal'         => $paveKey === 'aspal' ? $value : 0.0,
                    'agregat_tanah' => $paveKey === 'agregat_tanah' ? $value : 0.0,
                    'belum_tembus'  => $paveKey === 'belum_tembus' ? $value : 0.0,
                ];
                $currentMeter += $value;
            }
        }
    }

    // 3. Bagi range total ruas menjadi beberapa chunk berukuran max 5km (5000 meter)
    $chunkSize = 5000.0;
    $chunks = [];
    $current = $staBase;
    
    while ($current < $staEnd) {
        $chunkEnd = $current + $chunkSize;
        
        // A. Distribusikan Stripmap Runs ke chunk ini
        $overlappingSmRuns = [];
        foreach ($smRuns as $run) {
            $overlapStart = max($run['sta_awal'], $current);
            $overlapEnd   = min($run['sta_akhir'], $chunkEnd);
            if ($overlapStart < $overlapEnd) {
                $overlapLen = $overlapEnd - $overlapStart;
                $overlappingSmRuns[] = [
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
        usort($overlappingSmRuns, fn($a, $b) => $a['sta_awal'] <=> $b['sta_awal']);
        
        $chunkStripmaps = [];
        $currentPos = $current;
        foreach ($overlappingSmRuns as $run) {
            if ($run['sta_awal'] > $currentPos) {
                $gapLen = $run['sta_awal'] - $currentPos;
                $chunkStripmaps[] = [
                    'sta_awal'  => $currentPos,
                    'sta_akhir' => $run['sta_awal'],
                    'panjang'   => $gapLen,
                    'is_gap'    => true
                ];
            }
            $chunkStripmaps[] = $run;
            $currentPos = $run['sta_akhir'];
        }
        if ($currentPos < $chunkEnd) {
            $gapLen = $chunkEnd - $currentPos;
            $chunkStripmaps[] = [
                'sta_awal'  => $currentPos,
                'sta_akhir' => $chunkEnd,
                'panjang'   => $gapLen,
                'is_gap'    => true
            ];
        }

        // B. Distribusikan Perkerasan Runs ke chunk ini
        $overlappingPkRuns = [];
        foreach ($pkRuns as $run) {
            $overlapStart = max($run['sta_awal'], $current);
            $overlapEnd   = min($run['sta_akhir'], $chunkEnd);
            if ($overlapStart < $overlapEnd) {
                $overlapLen = $overlapEnd - $overlapStart;
                $overlappingPkRuns[] = [
                    'sta_awal'      => $overlapStart,
                    'sta_akhir'     => $overlapEnd,
                    'panjang'       => $overlapLen,
                    'rigid'         => $run['rigid'] > 0 ? $overlapLen : 0.0,
                    'aspal'         => $run['aspal'] > 0 ? $overlapLen : 0.0,
                    'agregat_tanah' => $run['agregat_tanah'] > 0 ? $overlapLen : 0.0,
                    'belum_tembus'  => $run['belum_tembus'] > 0 ? $overlapLen : 0.0,
                ];
            }
        }
        usort($overlappingPkRuns, fn($a, $b) => $a['sta_awal'] <=> $b['sta_awal']);
        
        $chunkPerkerasans = [];
        $currentPos = $current;
        foreach ($overlappingPkRuns as $run) {
            if ($run['sta_awal'] > $currentPos) {
                $gapLen = $run['sta_awal'] - $currentPos;
                $chunkPerkerasans[] = [
                    'sta_awal'  => $currentPos,
                    'sta_akhir' => $run['sta_awal'],
                    'panjang'   => $gapLen,
                    'is_gap'    => true
                ];
            }
            $chunkPerkerasans[] = $run;
            $currentPos = $run['sta_akhir'];
        }
        if ($currentPos < $chunkEnd) {
            $gapLen = $chunkEnd - $currentPos;
            $chunkPerkerasans[] = [
                'sta_awal'  => $currentPos,
                'sta_akhir' => $chunkEnd,
                'panjang'   => $gapLen,
                'is_gap'    => true
            ];
        }
        
        $chunks[] = [
            'start'       => $current,
            'end'         => $chunkEnd,
            'stripmaps'   => $chunkStripmaps,
            'perkerasans' => $chunkPerkerasans
        ];
        
        $current = $chunkEnd;
    }
?>

<!-- Load Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<?php if ($totalPanjang > 0 || $totalPanjangPerkerasan > 0): ?>
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden" style="background-color: #ffffff; border-color: #e5e7eb;"
     x-data="{ 
        activeLabel: null, 
        activePct: 0, 
        activeChunk: null,
        showPerkerasan: (function() {
            try {
                let saved = localStorage.getItem('show_perkerasan_line');
                if (saved !== null) return saved === '1';
            } catch(e) {}
            return true;
        })(),
        tickInterval: (function() {
            try {
                let saved = localStorage.getItem('sta_tick_interval');
                if (saved) return parseInt(saved);
            } catch (e) {}
            return <?= max($totalPanjang, $totalPanjangPerkerasan) < 1500 ? 100 : (max($totalPanjang, $totalPanjangPerkerasan) < 4000 ? 250 : (max($totalPanjang, $totalPanjangPerkerasan) < 10000 ? 500 : 1000)) ?>;
        })(),
        init() {
            this.$watch('tickInterval', value => {
                try {
                    localStorage.setItem('sta_tick_interval', value);
                } catch (e) {}
            });
            this.$watch('showPerkerasan', value => {
                try {
                    localStorage.setItem('show_perkerasan_line', value ? '1' : '0');
                } catch (e) {}
            });
        },
        meterToSta(meter) {
            let km = Math.floor(meter / 1000);
            let m = Math.round(meter - (km * 1000));
            return km + '+' + String(m).padStart(3, '0');
        },
        getTicks(start, end) {
            let ticks = [];
            ticks.push({ meter: start, pct: 0 });
            
            let interval = this.tickInterval;
            let firstMultiple = Math.ceil(start / interval) * interval;
            if (firstMultiple === start) {
                firstMultiple += interval;
            }
            
            let chunkTotal = end - start;
            if (chunkTotal <= 0) return ticks;

            for (let m = firstMultiple; m < end; m += interval) {
                let pct = ((m - start) / chunkTotal) * 100;
                let minTolerance = Math.min(50, interval * 0.2);
                if ((m - start) >= minTolerance && (end - m) >= minTolerance) {
                    ticks.push({
                        meter: m,
                        pct: pct
                    });
                }
            }
            
            if (end > start) {
                ticks.push({ meter: end, pct: 100 });
            }
            return ticks;
        }
     }"
     @click.outside="activeLabel = null; activeChunk = null">
     
    <!-- Header Controls -->
    <div class="px-6 py-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h3 class="text-lg font-semibold text-gray-900">Visualisasi Strip Map & Perkerasan</h3>
            <p class="text-xs text-gray-500 mt-0.5">Panjang ruas: <?= format_number(max($totalPanjang, $totalPanjangPerkerasan)) ?> m — Klik atau hover segmen untuk melihat detail data.</p>
        </div>
        <div class="flex items-center gap-4 print:hidden no-export">
            <!-- Toggle Hide/Unhide Perkerasan Line Dropdown -->
            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold text-gray-600 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    Perkerasan Line:
                </span>
                <select :value="showPerkerasan ? 'true' : 'false'" @change="showPerkerasan = ($event.target.value === 'true')" class="text-xs rounded-lg border border-gray-300 bg-gray-50 px-2.5 py-1.5 font-medium text-gray-700 hover:bg-gray-100 focus:border-blue-500 focus:outline-none transition-colors">
                    <option value="true">Tampilkan</option>
                    <option value="false">Sembunyikan</option>
                </select>
            </div>

            <!-- STA Label Scale Select -->
            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold text-gray-600">Skala Label STA:</span>
                <select x-model.number="tickInterval" class="text-xs rounded-lg border border-gray-300 bg-gray-50 px-2.5 py-1.5 font-medium text-gray-700 hover:bg-gray-100 focus:border-blue-500 focus:outline-none transition-colors">
                    <option value="100">100 m</option>
                    <option value="200">200 m</option>
                    <option value="250">250 m</option>
                    <option value="500">500 m</option>
                    <option value="1000">1 km</option>
                </select>
            </div>
        </div>
    </div>

    <div class="p-6">
        <!-- Main Layout Grid: Kiri Pie Charts, Kanan Line Chart & Stats -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- Kiri: Pie Charts (lg:col-span-4) -->
            <div class="lg:col-span-4 flex flex-col gap-6">
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
                            <div class="flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full inline-block" style="background-color: <?= $li['color'] ?>;"></span>
                                <span class="text-[11px] font-medium text-gray-600"><?= $li['label'] ?></span>
                                <span class="text-[10px] text-gray-400"><?= number_format($li['pct'], 1) ?>%</span>
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
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block"></span>
                            <span class="text-[11px] font-medium text-gray-600">Mantap</span>
                            <span class="text-[10px] text-gray-400"><?= number_format($pctMantap, 1) ?>%</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-red-500 inline-block"></span>
                            <span class="text-[11px] font-medium text-gray-600">Tidak Mantap</span>
                            <span class="text-[10px] text-gray-400"><?= number_format($pctTidakMantap, 1) ?>%</span>
                        </div>
                    </div>
                </div>

                <!-- Pie Chart 3: Proporsi Jenis Perkerasan -->
                <?php if ($totalPanjangPerkerasan > 0): ?>
                <div x-show="showPerkerasan" class="flex flex-col items-center justify-center rounded-2xl p-5 border min-h-[220px]" style="background-color: rgba(249, 250, 251, 0.6); border-color: #e5e7eb;">
                    <h4 class="text-[13px] font-semibold text-gray-500 uppercase tracking-wider mb-4">Jenis Perkerasan</h4>
                    <div class="pie-chart-container w-full max-w-[180px] aspect-square relative">
                        <canvas id="pavementPieChart"></canvas>
                    </div>
                    <!-- Legend -->
                    <div class="flex flex-wrap justify-center gap-x-3 gap-y-1.5 mt-5">
                        <?php
                            $pkLegendItems = [
                                ['label' => 'Rigid',          'color' => '#6b7280', 'pct' => $pctRigid,        'val' => $totalRigid],
                                ['label' => 'Aspal',          'color' => '#1f2937', 'pct' => $pctAspal,        'val' => $totalAspal],
                                ['label' => 'Agregat / Tanah','color' => '#92400e', 'pct' => $pctAgregatTanah, 'val' => $totalAgregatTanah],
                                ['label' => 'Belum Tembus',   'color' => '#7c3aed', 'pct' => $pctBelumTembus,  'val' => $totalBelumTembus],
                            ];
                        ?>
                        <?php foreach ($pkLegendItems as $li): ?>
                            <?php if ($li['val'] > 0): ?>
                            <div class="flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full inline-block" style="background-color: <?= $li['color'] ?>;"></span>
                                <span class="text-[11px] font-medium text-gray-600"><?= $li['label'] ?></span>
                                <span class="text-[10px] text-gray-400"><?= number_format($li['pct'], 1) ?>%</span>
                            </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

            </div>

            <!-- Kanan: Line Chart & Stats (lg:col-span-8) -->
            <div class="lg:col-span-8 flex flex-col space-y-6">
                <!-- Linear Strip Map & Perkerasan Chunks (Tiap 5000 STA) -->
                <div class="space-y-6">
                    <?php foreach ($chunks as $chunkIdx => $chunk): ?>
                        <?php 
                            $chunkTotalPanjang = $chunk['end'] - $chunk['start']; 
                            if ($chunkTotalPanjang <= 0) continue;
                        ?>
                        <div class="space-y-3 border border-gray-200 rounded-xl p-4 bg-gray-50/50 shadow-sm">

                            <!-- 1. Container Bar Kondisi Jalan (Strip Map) -->
                            <div class="space-y-1">
                                <div class="flex h-5 rounded-lg overflow-hidden shadow-sm bg-gray-100 border border-gray-200">
                                    <?php $cumulativePct = 0; ?>
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
                                                <div class="h-full w-full flex-shrink-0 relative group transition-all duration-300 cursor-pointer bg-white"
                                                     @mouseenter="if (window.matchMedia('(hover: hover)').matches) { activeLabel = { panjang: '<?= format_number($sm['panjang']) ?>', kondisi: 'Belum Ada Data Kondisi', sta: '<?= $staLabelStr ?>', color: '#9ca3af' }; activePct = <?= round($subMidPct, 2) ?>; activeChunk = 'sm_<?= $chunkIdx ?>' }"
                                                     @mouseleave="if (window.matchMedia('(hover: hover)').matches) { activeLabel = null; activeChunk = null }"
                                                     @click.stop="activeLabel = (activeLabel && activeLabel.sta === '<?= $staLabelStr ?>' && activeLabel.kondisi === 'Belum Ada Data Kondisi') ? null : { panjang: '<?= format_number($sm['panjang']) ?>', kondisi: 'Belum Ada Data Kondisi', sta: '<?= $staLabelStr ?>', color: '#9ca3af' }; activePct = <?= round($subMidPct, 2) ?>; activeChunk = 'sm_<?= $chunkIdx ?>'">
                                                     <div class="absolute inset-0 ring-2 ring-gray-300 ring-inset opacity-0 group-hover:opacity-100 transition-opacity"></div>
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
                                                        $subWidthGlobal = ($sc['value'] / $chunkTotalPanjang) * 100;
                                                        $subMidPct = $cumulativePct + ($subWidthGlobal / 2);
                                                        $cumulativePct += $subWidthGlobal;
                                                        $staLabelStr = meter_to_sta($sm['sta_awal']) . ' — ' . meter_to_sta($sm['sta_akhir']);
                                                    ?>
                                                    <div class="h-full flex-shrink-0 relative group transition-all duration-300 cursor-pointer"
                                                         style="width: <?= number_format($scPct, 4, '.', '') ?>%; background-color: <?= $sc['color'] ?>;"
                                                         @mouseenter="if (window.matchMedia('(hover: hover)').matches) { activeLabel = { panjang: '<?= format_number($sc['value']) ?>', kondisi: '<?= $sc['label'] ?>', sta: '<?= $staLabelStr ?>', color: '<?= $sc['color'] ?>' }; activePct = <?= round($subMidPct, 2) ?>; activeChunk = 'sm_<?= $chunkIdx ?>' }"
                                                         @mouseleave="if (window.matchMedia('(hover: hover)').matches) { activeLabel = null; activeChunk = null }"
                                                         @click.stop="activeLabel = (activeLabel && activeLabel.sta === '<?= $staLabelStr ?>' && activeLabel.kondisi === '<?= $sc['label'] ?>') ? null : { panjang: '<?= format_number($sc['value']) ?>', kondisi: '<?= $sc['label'] ?>', sta: '<?= $staLabelStr ?>', color: '<?= $sc['color'] ?>' }; activePct = <?= round($subMidPct, 2) ?>; activeChunk = 'sm_<?= $chunkIdx ?>'">
                                                         <div class="absolute inset-0 ring-2 ring-white/60 ring-inset opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <!-- Label Keterangan Strip Map Hover -->
                                <div class="relative w-full h-0 z-20">
                                    <template x-if="activeLabel && activeChunk === 'sm_<?= $chunkIdx ?>'">
                                        <div class="absolute top-1 flex flex-col items-center -translate-x-1/2 transition-all duration-150 ease-out"
                                             :style="'left:' + activePct + '%'">
                                            <div class="w-px h-3" :style="'background-color:' + activeLabel.color"></div>
                                            <div class="mt-0.5 px-2.5 py-1.5 rounded-lg border shadow-md text-center whitespace-nowrap bg-white"
                                                 :style="'border-color:' + activeLabel.color">
                                                <p class="text-xs font-bold" :style="'color:' + activeLabel.color" x-text="activeLabel.panjang + ' m'"></p>
                                                <p class="text-[10px] font-semibold text-gray-700" x-text="activeLabel.kondisi"></p>
                                                <p class="text-[9px] font-mono text-gray-500" x-text="activeLabel.sta"></p>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- 2. Container Bar Perkerasan Jalan (Rigid, Aspal, Agregat/Tanah, Belum Tembus) -->
                            <div x-show="showPerkerasan" x-transition.opacity class="space-y-1 pt-1">
                                <div class="flex h-5 rounded-lg overflow-hidden shadow-sm bg-gray-100 border border-gray-200">
                                    <?php $cumulativePkPct = 0; ?>
                                    <?php foreach ($chunk['perkerasans'] as $pk): ?>
                                        <?php
                                            $pkTotal = $pk['panjang'] > 0 ? $pk['panjang'] : 1;
                                            $pkPct   = $chunkTotalPanjang > 0 ? ($pk['panjang'] / $chunkTotalPanjang) * 100 : 0;
                                        ?>
                                        <div class="flex h-full flex-shrink-0" style="width: <?= number_format($pkPct, 4, '.', '') ?>%">
                                            <?php if (!empty($pk['is_gap'])): ?>
                                                <?php
                                                    $subWidthGlobal = ($pk['panjang'] / $chunkTotalPanjang) * 100;
                                                    $subMidPct = $cumulativePkPct + ($subWidthGlobal / 2);
                                                    $cumulativePkPct += $subWidthGlobal;
                                                    $staLabelStr = meter_to_sta($pk['sta_awal']) . ' — ' . meter_to_sta($pk['sta_akhir']);
                                                ?>
                                                <div class="h-full w-full flex-shrink-0 relative group transition-all duration-300 cursor-pointer bg-white"
                                                     @mouseenter="if (window.matchMedia('(hover: hover)').matches) { activeLabel = { panjang: '<?= format_number($pk['panjang']) ?>', kondisi: 'Belum Ada Data Perkerasan', sta: '<?= $staLabelStr ?>', color: '#9ca3af' }; activePct = <?= round($subMidPct, 2) ?>; activeChunk = 'pk_<?= $chunkIdx ?>' }"
                                                     @mouseleave="if (window.matchMedia('(hover: hover)').matches) { activeLabel = null; activeChunk = null }"
                                                     @click.stop="activeLabel = (activeLabel && activeLabel.sta === '<?= $staLabelStr ?>' && activeLabel.kondisi === 'Belum Ada Data Perkerasan') ? null : { panjang: '<?= format_number($pk['panjang']) ?>', kondisi: 'Belum Ada Data Perkerasan', sta: '<?= $staLabelStr ?>', color: '#9ca3af' }; activePct = <?= round($subMidPct, 2) ?>; activeChunk = 'pk_<?= $chunkIdx ?>'">
                                                     <div class="absolute inset-0 ring-2 ring-gray-300 ring-inset opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                                </div>
                                            <?php else: ?>
                                                <?php
                                                    $paveTypes = [
                                                        ['label' => 'Rigid',           'value' => $pk['rigid'],         'color' => '#6b7280'],
                                                        ['label' => 'Aspal',           'value' => $pk['aspal'],         'color' => '#111827'],
                                                        ['label' => 'Agregat / Tanah', 'value' => $pk['agregat_tanah'], 'color' => '#92400e'],
                                                        ['label' => 'Belum Tembus',    'value' => $pk['belum_tembus'],  'color' => '#7c3aed'],
                                                    ];
                                                    $activePave = array_values(array_filter($paveTypes, fn($c) => $c['value'] > 0));
                                                ?>
                                                <?php foreach ($activePave as $pt): ?>
                                                    <?php
                                                        $ptPct = ($pt['value'] / $pkTotal) * 100;
                                                        $subWidthGlobal = ($pt['value'] / $chunkTotalPanjang) * 100;
                                                        $subMidPct = $cumulativePkPct + ($subWidthGlobal / 2);
                                                        $cumulativePkPct += $subWidthGlobal;
                                                        $staLabelStr = meter_to_sta($pk['sta_awal']) . ' — ' . meter_to_sta($pk['sta_akhir']);
                                                    ?>
                                                    <div class="h-full flex-shrink-0 relative group transition-all duration-300 cursor-pointer"
                                                         style="width: <?= number_format($ptPct, 4, '.', '') ?>%; background-color: <?= $pt['color'] ?>;"
                                                         @mouseenter="if (window.matchMedia('(hover: hover)').matches) { activeLabel = { panjang: '<?= format_number($pt['value']) ?>', kondisi: '<?= $pt['label'] ?>', sta: '<?= $staLabelStr ?>', color: '<?= $pt['color'] ?>' }; activePct = <?= round($subMidPct, 2) ?>; activeChunk = 'pk_<?= $chunkIdx ?>' }"
                                                         @mouseleave="if (window.matchMedia('(hover: hover)').matches) { activeLabel = null; activeChunk = null }"
                                                         @click.stop="activeLabel = (activeLabel && activeLabel.sta === '<?= $staLabelStr ?>' && activeLabel.kondisi === '<?= $pt['label'] ?>') ? null : { panjang: '<?= format_number($pt['value']) ?>', kondisi: '<?= $pt['label'] ?>', sta: '<?= $staLabelStr ?>', color: '<?= $pt['color'] ?>' }; activePct = <?= round($subMidPct, 2) ?>; activeChunk = 'pk_<?= $chunkIdx ?>'">
                                                         <div class="absolute inset-0 ring-2 ring-white/60 ring-inset opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <!-- Label Keterangan Perkerasan Hover -->
                                <div class="relative w-full h-0 z-20">
                                    <template x-if="activeLabel && activeChunk === 'pk_<?= $chunkIdx ?>'">
                                        <div class="absolute top-1 flex flex-col items-center -translate-x-1/2 transition-all duration-150 ease-out"
                                             :style="'left:' + activePct + '%'">
                                            <div class="w-px h-3" :style="'background-color:' + activeLabel.color"></div>
                                            <div class="mt-0.5 px-2.5 py-1.5 rounded-lg border shadow-md text-center whitespace-nowrap bg-white"
                                                 :style="'border-color:' + activeLabel.color">
                                                <p class="text-xs font-bold" :style="'color:' + activeLabel.color" x-text="activeLabel.panjang + ' m'"></p>
                                                <p class="text-[10px] font-semibold text-gray-700" x-text="activeLabel.kondisi"></p>
                                                <p class="text-[9px] font-mono text-gray-500" x-text="activeLabel.sta"></p>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Skala Penggaris STA Dinamis -->
                            <div class="relative w-full h-6 pt-1">
                                <div class="absolute top-1 left-0 right-0 h-px bg-gray-300"></div>
                                <template x-for="tick in getTicks(<?= $chunk['start'] ?>, <?= $chunk['end'] ?>)" :key="tick.meter">
                                    <div class="absolute top-1 -translate-x-1/2 flex flex-col items-center" :style="'left: ' + tick.pct + '%'">
                                        <div class="w-px h-2 bg-gray-400"></div>
                                        <span class="text-[10px] font-mono font-semibold text-gray-500 mt-0.5" x-text="meterToSta(tick.meter)"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Stats Grid (Sesuai Layout Dashboard: 4 Kondisi - 2 Kemantapan - 4 Perkerasan) -->
                <div class="pt-6 border-t border-gray-100 space-y-6">
                    
                    <!-- Row 1: 4 Detail Kondisi Jalan (Baik, Sedang, Rusak Ringan, Rusak Berat) -->
                    <div>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                            <!-- Baik -->
                            <div class="p-4 rounded-xl border shadow-sm hover:shadow-md transition-shadow" style="background-color: #f0fdf4; border-color: #d1fae5;">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-2.5 h-2.5 rounded-full inline-block" style="background-color: #10b981;"></span>
                                        <span class="text-xs font-semibold text-emerald-800">Baik</span>
                                    </div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[10px] font-bold">
                                        <?= number_format($pctBaik, 1) ?>%
                                    </span>
                                </div>
                                <h3 class="text-xl font-bold text-emerald-700"><?= format_number($totalBaik) ?> <span class="text-xs font-normal text-emerald-600">m</span></h3>
                                <p class="text-[11px] font-medium text-emerald-600 mt-0.5">Kondisi Baik</p>
                            </div>

                            <!-- Sedang -->
                            <div class="p-4 rounded-xl border shadow-sm hover:shadow-md transition-shadow" style="background-color: #fefce8; border-color: #fef08a;">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-2.5 h-2.5 rounded-full inline-block" style="background-color: #facc15;"></span>
                                        <span class="text-xs font-semibold text-yellow-800">Sedang</span>
                                    </div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-yellow-100 text-yellow-800 text-[10px] font-bold">
                                        <?= number_format($pctSedang, 1) ?>%
                                    </span>
                                </div>
                                <h3 class="text-xl font-bold text-yellow-700"><?= format_number($totalSedang) ?> <span class="text-xs font-normal text-yellow-600">m</span></h3>
                                <p class="text-[11px] font-medium text-yellow-600 mt-0.5">Kondisi Sedang</p>
                            </div>

                            <!-- Rusak Ringan -->
                            <div class="p-4 rounded-xl border shadow-sm hover:shadow-md transition-shadow" style="background-color: #fff7ed; border-color: #ffedd5;">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-2.5 h-2.5 rounded-full inline-block" style="background-color: #f97316;"></span>
                                        <span class="text-xs font-semibold text-orange-800">Rusak Ringan</span>
                                    </div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-orange-100 text-orange-800 text-[10px] font-bold">
                                        <?= number_format($pctRR, 1) ?>%
                                    </span>
                                </div>
                                <h3 class="text-xl font-bold text-orange-700"><?= format_number($totalRR) ?> <span class="text-xs font-normal text-orange-600">m</span></h3>
                                <p class="text-[11px] font-medium text-orange-600 mt-0.5">Rusak Ringan</p>
                            </div>

                            <!-- Rusak Berat -->
                            <div class="p-4 rounded-xl border shadow-sm hover:shadow-md transition-shadow" style="background-color: #fef2f2; border-color: #fee2e2;">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-2.5 h-2.5 rounded-full inline-block" style="background-color: #ef4444;"></span>
                                        <span class="text-xs font-semibold text-red-800">Rusak Berat</span>
                                    </div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-red-100 text-red-800 text-[10px] font-bold">
                                        <?= number_format($pctRB, 1) ?>%
                                    </span>
                                </div>
                                <h3 class="text-xl font-bold text-red-700"><?= format_number($totalRB) ?> <span class="text-xs font-normal text-red-600">m</span></h3>
                                <p class="text-[11px] font-medium text-red-600 mt-0.5">Rusak Berat</p>
                            </div>
                        </div>
                    </div>

                    <!-- Row 2: 2 Kemantapan Jalan (Mantap vs Tidak Mantap) -->
                    <div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Card Mantap -->
                            <div class="p-4 rounded-xl border shadow-sm hover:shadow-md transition-shadow" style="background-color: #f0fdf4; border-color: #d1fae5;">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full inline-block" style="background-color: #10b981;"></span>
                                        <span class="text-xs font-semibold text-emerald-800">Mantap <span class="font-normal text-emerald-600">(Baik + Sedang)</span></span>
                                    </div>
                                    <span class="text-xs font-bold text-emerald-700"><?= number_format($pctMantap, 1) ?>%</span>
                                </div>
                                <h3 class="text-2xl font-bold text-emerald-700"><?= format_number($totalMantap) ?> <span class="text-xs font-semibold text-emerald-600">m</span></h3>
                                <div class="mt-2.5 w-full rounded-full h-2" style="background-color: rgba(16, 185, 129, 0.2);">
                                    <div class="h-2 rounded-full" style="width: <?= number_format($pctMantap, 4, '.', '') ?>%; background-color: #10b981;"></div>
                                </div>
                            </div>

                            <!-- Card Tidak Mantap -->
                            <div class="p-4 rounded-xl border shadow-sm hover:shadow-md transition-shadow" style="background-color: #fff1f2; border-color: #ffe4e6;">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full inline-block" style="background-color: #ef4444;"></span>
                                        <span class="text-xs font-semibold text-rose-800">Tidak Mantap <span class="font-normal text-rose-600">(R. Ringan + R. Berat)</span></span>
                                    </div>
                                    <span class="text-xs font-bold text-rose-700"><?= number_format($pctTidakMantap, 1) ?>%</span>
                                </div>
                                <h3 class="text-2xl font-bold text-rose-700"><?= format_number($totalTidakMantap) ?> <span class="text-xs font-semibold text-rose-600">m</span></h3>
                                <div class="mt-2.5 w-full rounded-full h-2" style="background-color: rgba(239, 68, 68, 0.2);">
                                    <div class="h-2 rounded-full" style="width: <?= number_format($pctTidakMantap, 4, '.', '') ?>%; background-color: #ef4444;"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Row 3: 4 Detail Jenis Perkerasan Jalan -->
                    <div x-show="showPerkerasan">
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                            <!-- Rigid -->
                            <div class="p-4 rounded-xl border shadow-sm hover:shadow-md transition-shadow bg-gray-50 border-gray-200">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-2.5 h-2.5 rounded-full inline-block" style="background-color: #6b7280;"></span>
                                        <span class="text-xs font-semibold text-gray-800">Rigid</span>
                                    </div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-gray-200 text-gray-800 text-[10px] font-bold">
                                        <?= number_format($pctRigid, 1) ?>%
                                    </span>
                                </div>
                                <h3 class="text-xl font-bold text-gray-800"><?= format_number($totalRigid) ?> <span class="text-xs font-normal text-gray-600">m</span></h3>
                                <p class="text-[11px] font-medium text-gray-500 mt-0.5">Beton / Rigid</p>
                            </div>

                            <!-- Aspal -->
                            <div class="p-4 rounded-xl border shadow-sm hover:shadow-md transition-shadow bg-slate-900 border-slate-950 text-white">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-2.5 h-2.5 rounded-full inline-block bg-white"></span>
                                        <span class="text-xs font-semibold text-slate-100">Aspal</span>
                                    </div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-slate-800 text-slate-100 text-[10px] font-bold">
                                        <?= number_format($pctAspal, 1) ?>%
                                    </span>
                                </div>
                                <h3 class="text-xl font-bold text-white"><?= format_number($totalAspal) ?> <span class="text-xs font-normal text-slate-300">m</span></h3>
                                <p class="text-[11px] font-medium text-slate-300 mt-0.5">Flexible / Aspal</p>
                            </div>

                            <!-- Agregat / Tanah -->
                            <div class="p-4 rounded-xl border shadow-sm hover:shadow-md transition-shadow" style="background-color: #fef3c7; border-color: #fde68a;">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-2.5 h-2.5 rounded-full inline-block" style="background-color: #92400e;"></span>
                                        <span class="text-xs font-semibold" style="color: #78350f;">Agregat / Tanah</span>
                                    </div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-amber-200 text-amber-900 text-[10px] font-bold">
                                        <?= number_format($pctAgregatTanah, 1) ?>%
                                    </span>
                                </div>
                                <h3 class="text-xl font-bold" style="color: #78350f;"><?= format_number($totalAgregatTanah) ?> <span class="text-xs font-normal opacity-80">m</span></h3>
                                <p class="text-[11px] font-medium mt-0.5" style="color: #92400e;">Kerikil / Tanah</p>
                            </div>

                            <!-- Belum Tembus -->
                            <div class="p-4 rounded-xl border shadow-sm hover:shadow-md transition-shadow" style="background-color: #f3e8ff; border-color: #e9d5ff;">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-2.5 h-2.5 rounded-full inline-block" style="background-color: #7c3aed;"></span>
                                        <span class="text-xs font-semibold text-purple-900">Belum Tembus</span>
                                    </div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-purple-200 text-purple-900 text-[10px] font-bold">
                                        <?= number_format($pctBelumTembus, 1) ?>%
                                    </span>
                                </div>
                                <h3 class="text-xl font-bold text-purple-800"><?= format_number($totalBelumTembus) ?> <span class="text-xs font-normal text-purple-600">m</span></h3>
                                <p class="text-[11px] font-medium text-purple-600 mt-0.5">Belum Tembus</p>
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
    
    // 1. Chart Kondisi Jalan
    const ctx1 = document.getElementById('conditionPieChart')?.getContext('2d');
    if (ctx1) {
        const chartColors1 = ['#10b981', '#facc15', '#f97316', '#ef4444'];
        const chartLabels1 = ['Baik', 'Sedang', 'Rusak Ringan', 'Rusak Berat'];
        const chartData1   = [<?= (float)$totalBaik ?>, <?= (float)$totalSedang ?>, <?= (float)$totalRR ?>, <?= (float)$totalRB ?>];

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
                    hoverOffset: 12
                }]
            },
            options: {
                layout: { padding: 30 },
                responsive: true,
                maintainAspectRatio: true,
                plugins: { legend: { display: false } }
            }
        });
    }

    // 2. Chart Kemantapan Jalan
    const ctx2 = document.getElementById('stabilityPieChart')?.getContext('2d');
    if (ctx2) {
        const chartColors2 = ['#10b981', '#ef4444'];
        const chartLabels2 = ['Mantap', 'Tidak Mantap'];
        const chartData2   = [<?= (float)$totalMantap ?>, <?= (float)$totalTidakMantap ?>];

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
                    hoverOffset: 12
                }]
            },
            options: {
                layout: { padding: 30 },
                responsive: true,
                maintainAspectRatio: true,
                plugins: { legend: { display: false } }
            }
        });
    }

    // 3. Chart Jenis Perkerasan
    const ctx3 = document.getElementById('pavementPieChart')?.getContext('2d');
    if (ctx3) {
        const chartColors3 = ['#6b7280', '#111827', '#92400e', '#7c3aed'];
        const chartLabels3 = ['Rigid', 'Aspal', 'Agregat / Tanah', 'Belum Tembus'];
        const chartData3   = [<?= (float)$totalRigid ?>, <?= (float)$totalAspal ?>, <?= (float)$totalAgregatTanah ?>, <?= (float)$totalBelumTembus ?>];

        const filtered3 = chartLabels3.reduce((acc, label, i) => {
            if (chartData3[i] > 0) {
                acc.labels.push(label);
                acc.data.push(chartData3[i]);
                acc.colors.push(chartColors3[i]);
            }
            return acc;
        }, { labels: [], data: [], colors: [] });

        new Chart(ctx3, {
            type: 'pie',
            data: {
                labels: filtered3.labels,
                datasets: [{
                    data: filtered3.data,
                    backgroundColor: filtered3.colors,
                    borderWidth: 2.5,
                    borderColor: '#ffffff',
                    hoverOffset: 12
                }]
            },
            options: {
                layout: { padding: 30 },
                responsive: true,
                maintainAspectRatio: true,
                plugins: { legend: { display: false } }
            }
        });
    }
});
</script>
<?php endif; ?>
