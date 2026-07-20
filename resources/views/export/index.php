<!-- ============================================================ -->
<!-- Pusat Export & Cetak Dokumen                                -->
<!-- ============================================================ -->

<div class="space-y-8" x-data="exportCenter(<?= htmlspecialchars(json_encode(array_map(function($r) {
    return [
        'id'             => (int)$r['id'],
        'kode_ruas'      => $r['kode_ruas'],
        'nama_ruas'      => $r['nama_ruas'],
        'sta_awal_str'   => meter_to_sta($r['sta_awal']),
        'sta_akhir_str'  => meter_to_sta($r['sta_akhir']),
        'panjang'        => (float)$r['panjang'],
        'koridor'        => $r['koridor'] ?? '-',
        'kabupaten_kota' => $r['kabupaten_kota'] ?? '-',
        'url_preview'    => base_url('stripmap/preview/' . $r['id']),
    ];
}, $ruasList ?? [])), ENT_QUOTES, 'UTF-8') ?>)">

    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-900 via-indigo-900 to-slate-900 rounded-3xl p-8 text-white shadow-xl relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute right-40 top-0 w-48 h-48 bg-indigo-500/10 rounded-full blur-2xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/20 text-blue-200 border border-blue-400/20 text-xs font-semibold uppercase tracking-wider">
                    <svg class="w-4 h-4 text-blue-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Pusat Cetak & Dokumentasi
                </div>
                <h1 class="text-3xl font-extrabold text-white tracking-tight">Export & Cetak Laporan</h1>
                <p class="text-blue-100/80 text-sm max-w-2xl leading-relaxed">
                    Kelola dan unduh seluruh data preservasi jalan, grafik ringkasan dashboard, serta visualisasi Strip Map dalam berbagai format dokumen (PDF, JPEG, PNG).
                </p>
            </div>

            <!-- Header Quick Stats -->
            <div class="flex items-center gap-3 sm:gap-4 bg-white/10 backdrop-blur-md p-4 rounded-2xl border border-white/10 self-start md:self-auto">
                <div class="text-center px-3 border-r border-white/10">
                    <span class="block text-2xl font-bold text-white"><?= $totalRuas ?? 0 ?></span>
                    <span class="text-[11px] text-blue-200 uppercase font-medium">Total Ruas</span>
                </div>
                <div class="text-center px-3">
                    <span class="block text-2xl font-bold text-emerald-400"><?= format_number($totalPanjang ?? 0.0, 1) ?></span>
                    <span class="text-[11px] text-blue-200 uppercase font-medium">Total KM</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Action Cards (Grid layout) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Card 1: Export Dashboard Summary -->
        <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-sm hover:shadow-md transition-all flex flex-col justify-between group">
            <div>
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-1">Export Ringkasan Dashboard</h3>
                <p class="text-xs text-gray-500 leading-relaxed mb-6">
                    Unduh data statistik, diagram kemantapan jalan, dan proporsi perkerasan dari halaman Dashboard utama.
                </p>
            </div>

            <div class="space-y-2 border-t border-gray-100 pt-4">
                <a href="<?= base_url('?export=pdf') ?>" 
                   class="w-full inline-flex items-center justify-between px-4 py-2.5 bg-red-50 text-red-700 hover:bg-red-100 rounded-xl text-xs font-semibold transition-colors">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        Unduh Dashboard PDF
                    </span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </a>
                <div class="grid grid-cols-2 gap-2">
                    <a href="<?= base_url('?export=jpeg') ?>" 
                       class="inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-xl text-xs font-semibold transition-colors">
                        JPEG (.jpg)
                    </a>
                    <a href="<?= base_url('?export=png') ?>" 
                       class="inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-xl text-xs font-semibold transition-colors">
                        PNG (.png)
                    </a>
                </div>
            </div>
        </div>

        <!-- Card 2: Export Strip Map Per Ruas -->
        <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-sm hover:shadow-md transition-all flex flex-col justify-between group">
            <div>
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-1">Visual Strip Map Ruas</h3>
                <p class="text-xs text-gray-500 leading-relaxed mb-4">
                    Pilih ruas jalan spesifik untuk membuka preview cetak dan mengekspor visualisasi strip map.
                </p>
                <div class="mb-4">
                    <label class="block text-[11px] font-semibold text-gray-400 uppercase mb-1">Pilih Ruas Jalan</label>
                    <select x-model="selectedRuasId" 
                            class="w-full text-xs px-3 py-2 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                        <option value="">-- Pilih Ruas Jalan --</option>
                        <template x-for="r in ruasList" :key="r.id">
                            <option :value="r.id" x-text="r.kode_ruas + ' - ' + r.nama_ruas"></option>
                        </template>
                    </select>
                </div>
            </div>

            <div class="border-t border-gray-100 pt-4">
                <button type="button" 
                        @click="openRuasPreview()" 
                        :disabled="!selectedRuasId"
                        :class="selectedRuasId ? 'bg-emerald-600 hover:bg-emerald-700 text-white cursor-pointer shadow-sm' : 'bg-gray-100 text-gray-400 cursor-not-allowed'"
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    Buka Preview & Export Strip Map
                </button>
            </div>
        </div>

        <!-- Card 3: Import & Rekap Data -->
        <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-sm hover:shadow-md transition-all flex flex-col justify-between group">
            <div>
                <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-1">Import Data Excel</h3>
                <p class="text-xs text-gray-500 leading-relaxed mb-6">
                    Unggah atau perbarui data kolektif ruas jalan dan segmen kondisi via template Excel.
                </p>
            </div>

            <div class="space-y-2 border-t border-gray-100 pt-4">
                <a href="<?= base_url('ruas/import') ?>" 
                   class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-semibold transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Masuk ke Halaman Import Excel
                </a>
            </div>
        </div>

    </div>

    <!-- Table Section: Quick Export All Ruas -->
    <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm overflow-hidden space-y-4 p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-gray-900">Daftar Cetak Per Ruas Jalan</h2>
                <p class="text-xs text-gray-500">Pilih ruas jalan untuk dicetak atau diunduh dokumen visualnya secara langsung.</p>
            </div>
            
            <!-- Search input -->
            <div class="relative w-full md:w-72">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </span>
                <input type="text" 
                       x-model="searchQuery" 
                       placeholder="Cari kode atau nama ruas..." 
                       class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>

        <div class="overflow-x-auto border border-gray-100 rounded-xl">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-gray-500 font-bold uppercase tracking-wider">
                        <th class="px-4 py-3 text-center w-12">No</th>
                        <th class="px-4 py-3">Ruas Jalan</th>
                        <th class="px-4 py-3">Wilayah / Koridor</th>
                        <th class="px-4 py-3 text-center">Panjang</th>
                        <th class="px-4 py-3 text-right">Aksi Cetak</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    <template x-for="(r, idx) in filteredRuas()" :key="r.id">
                        <tr class="hover:bg-blue-50/40 transition-colors">
                            <td class="px-4 py-3 text-center font-semibold text-gray-400" x-text="idx + 1"></td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-700 font-bold text-[10px] border border-blue-100" x-text="r.kode_ruas"></span>
                                    <span class="font-bold text-gray-900" x-text="r.nama_ruas"></span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                <span x-text="r.kabupaten_kota"></span>
                                <span class="text-gray-400 mx-1">•</span>
                                <span class="text-gray-500" x-text="r.koridor"></span>
                            </td>
                            <td class="px-4 py-3 text-center font-mono font-bold text-emerald-700" x-text="formatNumber(r.panjang / 1000) + ' km'"></td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <a :href="r.url_preview" 
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold text-xs transition-colors shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                    </svg>
                                    <span>Export & Cetak</span>
                                </a>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="filteredRuas().length === 0">
                        <td colspan="5" class="px-4 py-8 text-center text-gray-400">
                            Tidak ada ruas jalan yang sesuai pencarian.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
function exportCenter(initialRuas) {
    return {
        ruasList: initialRuas,
        selectedRuasId: '',
        searchQuery: '',

        formatNumber(num) {
            return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(num);
        },

        openRuasPreview() {
            if (!this.selectedRuasId) return;
            window.location.href = '<?= base_url('stripmap/preview/') ?>' + this.selectedRuasId;
        },

        filteredRuas() {
            if (!this.searchQuery.trim()) return this.ruasList;
            const q = this.searchQuery.toLowerCase().trim();
            return this.ruasList.filter(r => 
                r.nama_ruas.toLowerCase().includes(q) || 
                r.kode_ruas.toLowerCase().includes(q) ||
                r.kabupaten_kota.toLowerCase().includes(q)
            );
        }
    };
}
</script>
