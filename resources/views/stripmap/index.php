<!-- ============================================================ -->
<!-- Halaman Daftar Strip Map per Ruas -->
<!-- ============================================================ -->

<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="<?= base_url('ruas') ?>"
               class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-white border border-gray-200 hover:bg-gray-50 transition-colors shadow-sm">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Strip Map Ruas Jalan</h1>
                <p class="text-sm text-gray-500">Manajemen segmen kondisi dan data teknis jalan.</p>
            </div>
        </div>
        <div class="flex gap-2">
            <?php if (!empty($stripmaps)): ?>
            <a href="<?= base_url('stripmap/preview/' . $ruas['id']) ?>"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-xl hover:bg-indigo-700 transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                Preview
            </a>
            <?php endif; ?>
            <a href="<?= base_url('stripmap/create/' . $ruas['id']) ?>"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-xl hover:bg-blue-700 transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Segmen
            </a>
        </div>
    </div>

    <!-- Data Umum Ruas Jalan Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Data Umum Ruas Jalan</h2>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-6">
            <div>
                <span class="block text-xs font-medium text-gray-400 mb-1">Nama Ruas</span>
                <span class="text-sm font-bold text-gray-900"><?= e($ruas['nama_ruas']) ?></span>
            </div>
            <div>
                <span class="block text-xs font-medium text-gray-400 mb-1">Nomor Ruas</span>
                <span class="text-sm font-semibold font-mono text-gray-800"><?= e($ruas['kode_ruas']) ?></span>
            </div>
            <div>
                <span class="block text-xs font-medium text-gray-400 mb-1">Panjang Ruas</span>
                <span class="text-sm font-bold text-gray-900"><?= format_number($ruas['panjang']) ?> m</span>
            </div>
            <div>
                <span class="block text-xs font-medium text-gray-400 mb-1">Koridor</span>
                <span class="text-sm font-semibold text-gray-900"><?= e($ruas['koridor'] ?? '-') ?></span>
            </div>
            <div>
                <span class="block text-xs font-medium text-gray-400 mb-1">Kabupaten / Kota</span>
                <span class="text-sm font-semibold text-gray-900"><?= e($ruas['kabupaten_kota'] ?? '-') ?></span>
            </div>
        </div>
    </div>

    <!-- Strip Map Visual Preview -->
    <?php if (!empty($stripmaps)): ?>
        <?php view('stripmap._visual', ['stripmaps' => $stripmaps, 'summary' => $summary, 'ruas' => $ruas]); ?>
    <?php endif; ?>

    <!-- Table Card -->
    <?php if (!empty($stripmaps)): ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Data Segmen Strip Map</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">No</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">STA Awal</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">STA Akhir</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Panjang</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-green-700 uppercase tracking-wider">Baik</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-yellow-700 uppercase tracking-wider">Sedang</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-orange-700 uppercase tracking-wider">R. Ringan</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-red-700 uppercase tracking-wider">R. Berat</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($stripmaps as $i => $sm): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 text-sm text-gray-500"><?= $i + 1 ?></td>
                        <td class="px-4 py-3 text-sm text-gray-700 text-center font-mono"><?= meter_to_sta($sm['sta_awal']) ?></td>
                        <td class="px-4 py-3 text-sm text-gray-700 text-center font-mono"><?= meter_to_sta($sm['sta_akhir']) ?></td>
                        <td class="px-4 py-3 text-sm text-gray-700 text-center font-semibold"><?= format_number($sm['panjang']) ?></td>
                        <td class="px-4 py-3 text-sm text-center">
                            <span class="inline-flex px-2 py-0.5 rounded-md bg-green-50 text-green-700 text-xs font-semibold"><?= format_number($sm['baik']) ?></span>
                        </td>
                        <td class="px-4 py-3 text-sm text-center">
                            <span class="inline-flex px-2 py-0.5 rounded-md bg-yellow-50 text-yellow-700 text-xs font-semibold"><?= format_number($sm['sedang']) ?></span>
                        </td>
                        <td class="px-4 py-3 text-sm text-center">
                            <span class="inline-flex px-2 py-0.5 rounded-md bg-orange-50 text-orange-700 text-xs font-semibold"><?= format_number($sm['rusak_ringan']) ?></span>
                        </td>
                        <td class="px-4 py-3 text-sm text-center">
                            <span class="inline-flex px-2 py-0.5 rounded-md bg-red-50 text-red-700 text-xs font-semibold"><?= format_number($sm['rusak_berat']) ?></span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <a href="<?= base_url('stripmap/edit/' . $sm['id']) ?>"
                                   class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-amber-700 bg-amber-50 rounded-lg hover:bg-amber-100 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    Edit
                                </a>
                                <a href="<?= base_url('stripmap/delete/' . $sm['id']) ?>"
                                   onclick="confirmDelete(event, this.href, 'Yakin ingin menghapus segmen ini?')"
                                   class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-red-700 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Hapus
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php else: ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2z"/>
        </svg>
        <h3 class="text-lg font-semibold text-gray-600 mb-2">Belum ada data strip map</h3>
        <p class="text-sm text-gray-500 mb-6">Tambahkan segmen pertama untuk ruas ini.</p>
        <a href="<?= base_url('stripmap/create/' . $ruas['id']) ?>"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-xl hover:bg-blue-700 transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Segmen
        </a>
    </div>
    <?php endif; ?>

</div>
