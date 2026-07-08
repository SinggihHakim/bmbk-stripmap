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
            <div class="flex flex-col items-center justify-center bg-gray-50 rounded-xl p-4 border border-gray-100 min-h-[220px]">
                <h4 class="text-sm font-semibold text-gray-700 mb-4">Proporsi Kondisi Jalan</h4>
                <div class="w-full max-w-[300px] h-[300px] relative">
                    <canvas id="conditionPieChart"></canvas>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const ctx = document.getElementById('conditionPieChart').getContext('2d');
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['Baik', 'Sedang', 'Rusak Ringan', 'Rusak Berat'],
            datasets: [{
                data: [
                    <?= (float)$totalBaik ?>,
                    <?= (float)$totalSedang ?>,
                    <?= (float)$totalRR ?>,
                    <?= (float)$totalRB ?>
                ],
                backgroundColor: ['#22c55e', '#eab308', '#f97316', '#ef4444'],
                borderWidth: 1,
                borderColor: '#ffffff'
            }]
        },
        plugins: [{
            id: 'datalabels',
            afterDraw: (chart) => {
                const ctx = chart.ctx;
                chart.data.datasets.forEach((dataset, i) => {
                    const meta = chart.getDatasetMeta(i);
                    meta.data.forEach((element, index) => {
                        const dataVal = dataset.data[index];
                        if (dataVal <= 0) return;
                        
                        const total = dataset.data.reduce((a, b) => a + b, 0);
                        const pct = total > 0 ? ((dataVal / total) * 100).toFixed(1) + '%' : '0%';
                        
                        // Hitung sudut tengah slice (midAngle)
                        const midAngle = element.startAngle + (element.endAngle - element.startAngle) / 2;
                        
                        // Posisi label di luar outerRadius
                        const radiusLabel = element.outerRadius + 22;
                        const x = element.x + Math.cos(midAngle) * radiusLabel;
                        const y = element.y + Math.sin(midAngle) * radiusLabel;
                        
                        // Posisi awal & akhir untuk garis penunjuk (pointer line)
                        const xStart = element.x + Math.cos(midAngle) * (element.outerRadius - 2);
                        const yStart = element.y + Math.sin(midAngle) * (element.outerRadius - 2);
                        const xEnd = element.x + Math.cos(midAngle) * (element.outerRadius + 10);
                        const yEnd = element.y + Math.sin(midAngle) * (element.outerRadius + 10);
                        
                        ctx.save();
                        
                        // Gambar garis penunjuk tipis abu-abu
                        ctx.strokeStyle = '#9ca3af';
                        ctx.lineWidth = 1;
                        ctx.beginPath();
                        ctx.moveTo(xStart, yStart);
                        ctx.lineTo(xEnd, yEnd);
                        ctx.stroke();
                        
                        // Draw badge background
                        ctx.fillStyle = 'rgba(0, 0, 0, 0.7)';
                        ctx.beginPath();
                        ctx.arc(x, y, 14, 0, 2 * Math.PI);
                        ctx.fill();
                        
                        // Draw white percentage text
                        ctx.fillStyle = '#ffffff';
                        ctx.font = 'bold 9px sans-serif';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        ctx.fillText(pct, x, y);
                        ctx.restore();
                    });
                });
            }
        }],
        options: {
            layout: {
                padding: 35
            },
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const value = context.raw;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return `${context.label}: ${new Intl.NumberFormat('id-ID').format(value)} m (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
});
</script>
<?php endif; ?>

