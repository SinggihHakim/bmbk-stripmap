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

<!-- Load Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<?php if ($totalPanjang > 0): ?>
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Visualisasi Strip Map</h3>
        <p class="text-xs text-gray-500 mt-0.5">Total panjang: <?= format_number($totalPanjang) ?> m — Representasi urutan segmen kondisi konkrit jalan.</p>
    </div>
    
    <div class="p-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Kolom Kiri & Tengah: Linear Strip Map + Stats -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Linear Strip Map Bar (Berdasarkan Urutan Segmen Nyata) -->
                <div class="space-y-1">
                    <!-- Container Bar Jalur Linear -->
                    <div class="flex h-14 rounded-xl overflow-hidden shadow-md bg-gray-100 border border-gray-200">
                        <?php foreach ($stripmaps as $sm): ?>
                            <?php
                                $smTotal = $sm['panjang'] > 0 ? $sm['panjang'] : 1;
                                $smPct   = $totalPanjang > 0 ? ($sm['panjang'] / $totalPanjang) * 100 : 0;
                            ?>
                            <div class="flex h-full flex-shrink-0" style="width: <?= number_format($smPct, 4, '.', '') ?>%">
                                <?php
                                    $segConditions = [
                                        ['label' => 'Baik',         'value' => $sm['baik'],         'color' => '#22c55e'],
                                        ['label' => 'Sedang',       'value' => $sm['sedang'],       'color' => '#eab308'],
                                        ['label' => 'Rusak Ringan', 'value' => $sm['rusak_ringan'], 'color' => '#f97316'],
                                        ['label' => 'Rusak Berat',  'value' => $sm['rusak_berat'],  'color' => '#ef4444'],
                                    ];
                                    // Saring kondisi aktif
                                    $activeSeg = array_values(array_filter($segConditions, fn($c) => $c['value'] > 0));
                                ?>
                                <?php foreach ($activeSeg as $sc): ?>
                                    <?php $scPct = ($sc['value'] / $smTotal) * 100; ?>
                                    <div class="h-full flex-shrink-0 relative group transition-all duration-300 hover:scale-y-105 hover:z-10"
                                         style="width: <?= number_format($scPct, 4, '.', '') ?>%; background-color: <?= $sc['color'] ?>;">
                                         
                                         <!-- Tooltip Hover -->
                                         <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-2 bg-gray-900 text-white text-xs rounded-xl opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-10 shadow-lg">
                                             <p class="font-bold text-sm"><?= meter_to_sta($sm['sta_awal']) ?> — <?= meter_to_sta($sm['sta_akhir']) ?></p>
                                             <p class="mt-1">Kondisi: <span class="font-semibold"><?= $sc['label'] ?></span></p>
                                             <p>Panjang Kondisi: <?= format_number($sc['value']) ?> m</p>
                                             <p>Panjang Segmen: <?= format_number($sm['panjang']) ?> m</p>
                                             <div class="absolute top-full left-1/2 -translate-x-1/2 -mt-1 border-4 border-transparent border-t-gray-900"></div>
                                         </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Skala Penggaris STA -->
                    <?php
                        $ticks = [];
                        $currentMeter = $ruas['sta_awal'];

                        // Tambahkan tick awal
                        $ticks[] = [
                            'meter' => $currentMeter,
                            'pct'   => 0
                        ];

                        foreach ($stripmaps as $sm) {
                            $segConditions = [
                                ['label' => 'Baik',         'value' => (float)$sm['baik']],
                                ['label' => 'Sedang',       'value' => (float)$sm['sedang']],
                                ['label' => 'Rusak Ringan', 'value' => (float)$sm['rusak_ringan']],
                                ['label' => 'Rusak Berat',  'value' => (float)$sm['rusak_berat']],
                            ];
                            // Ambil kondisi yang bernilai > 0
                            $activeSeg = array_values(array_filter($segConditions, fn($c) => $c['value'] > 0));
                            
                            // Kita telusuri kondisi aktif di segmen ini untuk mendapatkan titik transisi
                            foreach ($activeSeg as $sc) {
                                $currentMeter += $sc['value'];
                                $pct = $totalPanjang > 0 ? (($currentMeter - $ruas['sta_awal']) / $totalPanjang) * 100 : 0;
                                
                                $ticks[] = [
                                    'meter' => $currentMeter,
                                    'pct'   => $pct
                                ];
                            }
                        }

                        // Filter tick agar tidak tumpang tindih secara visual
                        $filteredTicks = [];
                        $lastPct = -100;
                        $totalTicksCount = count($ticks);

                        foreach ($ticks as $index => $tick) {
                            // Selalu masukkan tick pertama dan terakhir
                            if ($index === 0 || $index === $totalTicksCount - 1) {
                                $filteredTicks[] = $tick;
                                if ($index === 0) {
                                    $lastPct = $tick['pct'];
                                }
                                continue;
                            }
                            
                            // Pastikan ada jarak minimal 4% secara visual agar label tidak menumpuk
                            $pctFromStart = $tick['pct'] - $lastPct;
                            $pctFromEnd = 100 - $tick['pct'];
                            
                            if ($pctFromStart >= 4.0 && $pctFromEnd >= 4.0) {
                                // Hindari duplikasi meter yang sama
                                $exists = false;
                                foreach ($filteredTicks as $ft) {
                                    if (abs($ft['meter'] - $tick['meter']) < 0.1) {
                                        $exists = true;
                                        break;
                                    }
                                }
                                if (!$exists) {
                                    $filteredTicks[] = $tick;
                                    $lastPct = $tick['pct'];
                                }
                            }
                        }
                    ?>
                    <div class="relative w-full h-10 mt-1">
                        <!-- Garis horizontal penggaris -->
                        <div class="absolute top-0 left-0 right-0 h-px bg-gray-300"></div>
                        
                        <!-- Poin Tanda Skala -->
                        <?php foreach ($filteredTicks as $tick): ?>
                            <div class="absolute top-0 -translate-x-1/2 flex flex-col items-center" style="left: <?= number_format($tick['pct'], 4, '.', '') ?>%">
                                <div class="w-px h-2 bg-gray-400"></div>
                                <span class="text-[10px] font-mono font-semibold text-gray-500 mt-1"><?= meter_to_sta($tick['meter']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Legend + Stats -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <!-- Baik -->
                    <div class="p-4 rounded-xl bg-green-50 border border-green-100">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="w-3 h-3 rounded-full bg-green-500"></span>
                            <span class="text-xs font-semibold text-green-800">Baik</span>
                        </div>
                        <p class="text-xl font-bold text-green-700"><?= format_number($totalBaik) ?> <span class="text-xs font-normal">m</span></p>
                        <p class="text-xs text-green-600 mt-1"><?= number_format($pctBaik, 1) ?>%</p>
                    </div>
                    <!-- Sedang -->
                    <div class="p-4 rounded-xl bg-yellow-50 border border-yellow-100">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="w-3 h-3 rounded-full bg-yellow-500"></span>
                            <span class="text-xs font-semibold text-yellow-800">Sedang</span>
                        </div>
                        <p class="text-xl font-bold text-yellow-700"><?= format_number($totalSedang) ?> <span class="text-xs font-normal">m</span></p>
                        <p class="text-xs text-yellow-600 mt-1"><?= number_format($pctSedang, 1) ?>%</p>
                    </div>
                    <!-- Rusak Ringan -->
                    <div class="p-4 rounded-xl bg-orange-50 border border-orange-100">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="w-3 h-3 rounded-full bg-orange-500"></span>
                            <span class="text-xs font-semibold text-orange-800">Rusak Ringan</span>
                        </div>
                        <p class="text-xl font-bold text-orange-700"><?= format_number($totalRR) ?> <span class="text-xs font-normal">m</span></p>
                        <p class="text-xs text-orange-600 mt-1"><?= number_format($pctRR, 1) ?>%</p>
                    </div>
                    <!-- Rusak Berat -->
                    <div class="p-4 rounded-xl bg-red-50 border border-red-100">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="w-3 h-3 rounded-full bg-red-500"></span>
                            <span class="text-xs font-semibold text-red-800">Rusak Berat</span>
                        </div>
                        <p class="text-xl font-bold text-red-700"><?= format_number($totalRB) ?> <span class="text-xs font-normal">m</span></p>
                        <p class="text-xs text-red-600 mt-1"><?= number_format($pctRB, 1) ?>%</p>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Pie Chart -->
            <div class="flex flex-col items-center justify-center bg-gray-50/60 rounded-2xl p-5 border border-gray-100 min-h-[220px]">
                <h4 class="text-[13px] font-semibold text-gray-500 uppercase tracking-wider mb-4">Proporsi Kondisi Jalan</h4>
                <style>
                    @keyframes pie-spin-in {
                        from { transform: scale(0) rotate(-90deg); opacity: 0; }
                        to   { transform: scale(1) rotate(0deg);   opacity: 1; }
                    }
                    #pieChartWrapper canvas {
                        animation: pie-spin-in 0.8s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
                    }
                    #pieChartWrapper {
                        filter: drop-shadow(0 2px 8px rgba(0,0,0,0.06));
                    }
                </style>
                <div id="pieChartWrapper" class="w-full max-w-[280px] aspect-square relative">
                    <canvas id="conditionPieChart"></canvas>
                </div>
                <!-- Modern Legend -->
                <div class="flex flex-wrap justify-center gap-x-4 gap-y-2 mt-5">
                    <?php
                        $legendItems = [
                            ['label' => 'Baik',         'color' => '#22c55e', 'pct' => $pctBaik,   'val' => $totalBaik],
                            ['label' => 'Sedang',       'color' => '#eab308', 'pct' => $pctSedang, 'val' => $totalSedang],
                            ['label' => 'Rusak Ringan', 'color' => '#f97316', 'pct' => $pctRR,     'val' => $totalRR],
                            ['label' => 'Rusak Berat',  'color' => '#ef4444', 'pct' => $pctRB,     'val' => $totalRB],
                        ];
                    ?>
                    <?php foreach ($legendItems as $li): ?>
                        <?php if ($li['val'] > 0): ?>
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full" style="background: <?= $li['color'] ?>"></span>
                            <span class="text-[11px] font-medium text-gray-600"><?= $li['label'] ?></span>
                            <span class="text-[10px] text-gray-400"><?= number_format($li['pct'], 1) ?>%</span>
                        </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const ctx = document.getElementById('conditionPieChart').getContext('2d');

    const chartColors = ['#22c55e', '#eab308', '#f97316', '#ef4444'];
    const chartLabels = ['Baik', 'Sedang', 'Rusak Ringan', 'Rusak Berat'];
    const chartData   = [
        <?= (float)$totalBaik ?>,
        <?= (float)$totalSedang ?>,
        <?= (float)$totalRR ?>,
        <?= (float)$totalRB ?>
    ];

    // Filter out zero values
    const filtered = chartLabels.reduce((acc, label, i) => {
        if (chartData[i] > 0) {
            acc.labels.push(label);
            acc.data.push(chartData[i]);
            acc.colors.push(chartColors[i]);
        }
        return acc;
    }, { labels: [], data: [], colors: [] });

    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: filtered.labels,
            datasets: [{
                data: filtered.data,
                backgroundColor: filtered.colors,
                borderWidth: 2.5,
                borderColor: '#ffffff',
                hoverBorderWidth: 3,
                hoverBorderColor: '#ffffff',
                hoverOffset: 12
            }]
        },
        plugins: [{
            id: 'modernLabels',
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

                    // Thin connector line
                    ctx2.strokeStyle = 'rgba(156,163,175,0.4)';
                    ctx2.lineWidth = 0.8;
                    ctx2.beginPath();
                    ctx2.moveTo(innerPt.x, innerPt.y);
                    ctx2.lineTo(outerPt.x, outerPt.y);
                    ctx2.stroke();

                    // Percentage text (no black circle)
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
            layout: {
                padding: 40
            },
            responsive: true,
            maintainAspectRatio: true,
            animation: {
                animateRotate: true,
                animateScale: true,
                duration: 1000,
                easing: 'easeOutQuart'
            },
            plugins: {
                legend: {
                    display: false
                },
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
