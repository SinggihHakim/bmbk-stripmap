<!-- ============================================================ -->
<!-- Preview Strip Map (Full Page) -->
<!-- ============================================================ -->

<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-5">
            <a href="<?= base_url('stripmap/' . $ruas['id']) ?>"
               class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-white border border-gray-200 hover:bg-gray-50 transition-colors shadow-sm">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Preview Strip Map</h1>
                <p class="mt-1 text-sm text-gray-500">
                    <span class="font-semibold"><?= e($ruas['nama_ruas']) ?></span>
                    (<span class="font-mono"><?= e($ruas['kode_ruas']) ?></span>) &middot;
                    STA <?= meter_to_sta($ruas['sta_awal']) ?> — <?= meter_to_sta($ruas['sta_akhir']) ?> &middot;
                    Panjang: <?= format_number($ruas['panjang']) ?> m
                </p>
            </div>
        </div>
        <a href="<?= base_url('stripmap/' . $ruas['id']) ?>"
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-white text-gray-700 text-sm font-medium rounded-xl border border-gray-300 hover:bg-gray-50 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11 17l-5-5m0 0l5-5m-5 5h12"/>
            </svg>
            Kembali ke Data
        </a>
    </div>

    <!-- Visual Strip Map -->
    <?php if (!empty($stripmaps)): ?>
        <?php view('stripmap._visual', ['stripmaps' => $stripmaps, 'summary' => $summary, 'ruas' => $ruas]); ?>
    <?php else: ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2z"/>
            </svg>
            <h3 class="text-lg font-semibold text-gray-600 mb-2">Belum ada data strip map</h3>
            <p class="text-sm text-gray-500 mb-6">Tambahkan segmen terlebih dahulu.</p>
            <a href="<?= base_url('stripmap/create/' . $ruas['id']) ?>"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-xl hover:bg-blue-700 transition-colors shadow-sm">
                Tambah Segmen
            </a>
        </div>
    <?php endif; ?>

    <!-- Data Table Summary -->
    <?php if (!empty($stripmaps)): ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Ringkasan Data</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Segmen</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">STA</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Panjang</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-green-700 uppercase">Baik</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-yellow-700 uppercase">Sedang</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-orange-700 uppercase">R. Ringan</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-red-700 uppercase">R. Berat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($stripmaps as $i => $sm): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 text-sm text-gray-500 text-center"><?= $i + 1 ?></td>
                        <td class="px-4 py-3 text-sm text-gray-700 text-center font-mono"><?= meter_to_sta($sm['sta_awal']) ?> — <?= meter_to_sta($sm['sta_akhir']) ?></td>
                        <td class="px-4 py-3 text-sm text-gray-700 text-center font-semibold"><?= format_number($sm['panjang']) ?></td>
                        <td class="px-4 py-3 text-sm text-center"><span class="px-2 py-0.5 rounded bg-green-50 text-green-700 text-xs font-semibold"><?= format_number($sm['baik']) ?></span></td>
                        <td class="px-4 py-3 text-sm text-center"><span class="px-2 py-0.5 rounded bg-yellow-50 text-yellow-700 text-xs font-semibold"><?= format_number($sm['sedang']) ?></span></td>
                        <td class="px-4 py-3 text-sm text-center"><span class="px-2 py-0.5 rounded bg-orange-50 text-orange-700 text-xs font-semibold"><?= format_number($sm['rusak_ringan']) ?></span></td>
                        <td class="px-4 py-3 text-sm text-center"><span class="px-2 py-0.5 rounded bg-red-50 text-red-700 text-xs font-semibold"><?= format_number($sm['rusak_berat']) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                    <!-- Total Row -->
                    <tr class="bg-gray-50 font-semibold">
                        <td class="px-4 py-3 text-sm text-gray-700 text-center" colspan="2">TOTAL</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-center"><?= format_number($summary['total_panjang']) ?></td>
                        <td class="px-4 py-3 text-sm text-green-700 text-center"><?= format_number($summary['total_baik']) ?></td>
                        <td class="px-4 py-3 text-sm text-yellow-700 text-center"><?= format_number($summary['total_sedang']) ?></td>
                        <td class="px-4 py-3 text-sm text-orange-700 text-center"><?= format_number($summary['total_rusak_ringan']) ?></td>
                        <td class="px-4 py-3 text-sm text-red-700 text-center"><?= format_number($summary['total_rusak_berat']) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

</div>
