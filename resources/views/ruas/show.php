<!-- ============================================================ -->
<!-- Detail Ruas Jalan + Strip Map Preview -->
<!-- ============================================================ -->

<div class="space-y-6">

    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="<?= base_url('ruas') ?>"
           class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-white border border-gray-200 hover:bg-gray-50 transition-colors shadow-sm">
            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Detail Ruas Jalan</h1>
            <p class="text-sm text-gray-500">Visualisasi kondisi strip map dan informasi data teknis.</p>
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

    <!-- Strip Map Visual -->
    <?php if (!empty($stripmaps)): ?>
        <?php view('stripmap._visual', ['stripmaps' => $stripmaps, 'summary' => $summary, 'ruas' => $ruas]); ?>
    <?php endif; ?>

    <!-- Aksi -->
    <div class="flex gap-3">
        <a href="<?= base_url('stripmap/' . $ruas['id']) ?>"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-xl hover:bg-blue-700 transition-colors shadow-sm">
            Kelola Strip Map
        </a>
        <a href="<?= base_url('ruas/edit/' . $ruas['id']) ?>"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-white text-gray-700 text-sm font-medium rounded-xl border border-gray-300 hover:bg-gray-50 transition-colors">
            Edit Ruas
        </a>
    </div>

</div>
