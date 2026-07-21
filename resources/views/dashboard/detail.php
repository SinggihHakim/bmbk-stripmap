<!-- ============================================================ -->
<!-- Dashboard Detail: Detail Kondisi Ruas Jalan -->
<!-- ============================================================ -->

<?php
// Siapkan data ruas untuk AlpineJS
$ruasDetailJsonData = [];
foreach ($summaryPerRuas as $row) {
    $ruasDetailJsonData[] = [
        'id'             => (int)$row['id'],
        'kode_ruas'      => $row['kode_ruas'],
        'nama_ruas'      => $row['nama_ruas'],
        'sta_awal'       => (float)$row['sta_awal'],
        'sta_akhir'      => (float)$row['sta_akhir'],
        'sta_awal_str'   => meter_to_sta($row['sta_awal']),
        'sta_akhir_str'  => meter_to_sta($row['sta_akhir']),
        'total_panjang'  => (float)$row['total_panjang'],
        'koridor'        => $row['koridor'] ?? '',
        'kabupaten_kota' => $row['kabupaten_kota'] ?? '',
        'baik'           => (float)$row['baik'],
        'sedang'         => (float)$row['sedang'],
        'rusak_ringan'   => (float)$row['rusak_ringan'],
        'rusak_berat'    => (float)$row['rusak_berat'],
        'mantap'         => (float)$row['mantap'],
        'tidak_mantap'   => (float)$row['tidak_mantap'],
        'total_terisi'   => (float)$row['total_terisi'],
        'url_stripmap'   => base_url('stripmap/' . $row['id']),
    ];
}

$curMeta = $kondisiMeta[$selectedKondisi] ?? $kondisiMeta['rusak_ringan'];
?>

<div x-data="dashboardDetailTable(<?= htmlspecialchars(json_encode($ruasDetailJsonData), ENT_QUOTES, 'UTF-8') ?>, '<?= $selectedKondisi ?>')" class="space-y-6">

    <!-- Header & Action Row -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-gray-200/80 shadow-sm">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="<?= base_url() ?>" class="text-xs font-semibold text-gray-500 hover:text-blue-600 transition-colors">Dashboard</a>
                <span class="text-xs text-gray-400">/</span>
                <span class="text-xs font-semibold text-blue-600">Detail Kondisi Ruas</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
                <span><?= $curMeta['title'] ?></span>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold <?= $curMeta['badge_bg'] ?> <?= $curMeta['badge_text'] ?> border border-current/20">
                    <?= $curMeta['label'] ?>
                </span>
            </h1>
            <p class="mt-1 text-sm text-gray-500">Daftar ruas jalan yang dikelompokkan berdasarkan kondisi, diurutkan dari yang terpanjang hingga terpendek.</p>
        </div>

        <div>
            <a href="<?= base_url() ?>" 
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gray-300 bg-white text-gray-700 text-sm font-semibold hover:bg-gray-50 hover:text-gray-900 transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali ke Dashboard
            </a>
        </div>
    </div>

    <!-- Condition Selector Tabs -->
    <div class="bg-white p-2 rounded-2xl border border-gray-200 shadow-sm flex flex-wrap gap-1.5 overflow-x-auto">
        <template x-for="(meta, key) in kondisiMetaMap" :key="key">
            <button type="button" 
                    @click="switchKondisi(key)"
                    :class="selectedKondisi === key 
                        ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20 font-bold' 
                        : 'bg-gray-50 text-gray-600 hover:bg-gray-100 hover:text-gray-900 font-semibold'"
                    class="px-4 py-2.5 rounded-xl text-xs transition-all flex items-center gap-2 whitespace-nowrap">
                <span class="w-2 h-2 rounded-full" :style="{ backgroundColor: meta.accent }"></span>
                <span x-text="meta.label"></span>
                <span class="px-1.5 py-0.5 rounded-md text-[10px]" 
                      :class="selectedKondisi === key ? 'bg-white/20 text-white' : 'bg-gray-200 text-gray-700'"
                      x-text="countRuasWithKondisi(key)">
                </span>
            </button>
        </template>
    </div>

    <!-- Summary Metrics Card for Selected Condition -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Metric 1: Total Ruas Jalan Terdeteksi -->
        <div class="p-5 rounded-2xl border bg-white shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider block mb-1">Jumlah Ruas Jalan</span>
                <h3 class="text-2xl font-bold text-gray-900" x-text="filteredRuas().length + ' Ruas'"></h3>
                <p class="text-xs text-gray-500 mt-1">Memiliki segmen <span class="font-bold text-gray-700" x-text="getKondisiLabel()"></span></p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </div>
        </div>

        <!-- Metric 2: Total Panjang Kondisi Terpilih -->
        <div class="p-5 rounded-2xl border bg-white shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider block mb-1">Total Panjang Kondisi</span>
                <h3 class="text-2xl font-bold" :style="{ color: getKondisiAccent() }" x-text="formatNumber(getTotalPanjangKondisiKm()) + ' km'"></h3>
                <p class="text-xs text-gray-500 mt-1" x-text="'Setara ' + formatNumber(getTotalPanjangKondisiM()) + ' meter'"></p>
            </div>
            <div class="w-12 h-12 rounded-xl flex items-center justify-center" :style="{ backgroundColor: getKondisiAccent() + '15', color: getKondisiAccent() }">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
            </div>
        </div>

        <!-- Metric 3: Urutan Sorting Active Status -->
        <div class="p-5 rounded-2xl border bg-white shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider block mb-1">Pengurutan Data</span>
                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <span x-text="sortOrder === 'desc' ? 'Terpanjang \u2192 Terpendek' : 'Terpendek \u2192 Terpanjang'"></span>
                </h3>
                <p class="text-xs text-gray-500 mt-1">Berdasarkan panjang segmen <span class="font-bold text-gray-700" x-text="getKondisiLabel()"></span></p>
            </div>
            <button type="button" 
                    @click="toggleSortOrder()"
                    title="Ubah Urutan"
                    class="px-3 py-2 rounded-xl border border-gray-200 bg-gray-50 hover:bg-gray-100 text-gray-700 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                </svg>
                <span>Balik Urutan</span>
            </button>
        </div>
    </div>

    <!-- Filters & Search Bar Panel -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <div class="flex flex-col md:flex-row gap-4 items-stretch md:items-end">
            
            <!-- Judul & Quick Toggle -->
            <div class="md:mb-0 md:mr-2 self-start md:self-center">
                <h2 class="text-base font-bold text-gray-900">Daftar Ruas Jalan</h2>
                <p class="text-xs text-gray-500">Filter dan urutkan ruas jalan secara langsung.</p>
            </div>

            <!-- Search Query -->
            <div class="flex-1 min-w-0">
                <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">Cari Ruas</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
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
            <div class="w-full md:w-48">
                <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">Koridor</label>
                <select x-model="selectedKoridor" 
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white">
                    <option value="">Semua Koridor</option>
                    <template x-for="koridor in getUniqueKoridor()" :key="koridor">
                        <option :value="koridor" x-text="koridor"></option>
                    </template>
                </select>
            </div>

            <!-- Filter Kabupaten / Kota -->
            <div class="w-full md:w-48">
                <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">Wilayah</label>
                <select x-model="selectedKabupaten" 
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white">
                    <option value="">Semua Wilayah</option>
                    <template x-for="kab in getUniqueKabupaten()" :key="kab">
                        <option :value="kab" x-text="kab"></option>
                    </template>
                </select>
            </div>

            <!-- Toggle Sorting Direction -->
            <div class="w-full md:w-auto">
                <button type="button" 
                        @click="toggleSortOrder()" 
                        class="w-full md:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 transition-colors text-sm font-semibold">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"/>
                    </svg>
                    <span x-text="sortOrder === 'desc' ? 'Terpanjang \u2192 Terpendek' : 'Terpendek \u2192 Terpanjang'"></span>
                </button>
            </div>

            <!-- Reset Button -->
            <div class="w-full md:w-auto">
                <button type="button" 
                        @click="resetFilters()" 
                        title="Reset Filter"
                        class="w-full md:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl border border-gray-200 bg-gray-50 text-gray-600 hover:bg-gray-100 hover:text-gray-800 transition-colors text-sm font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    Reset
                </button>
            </div>

        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-xs font-bold text-gray-500 uppercase tracking-wider">
                        <th class="px-4 py-3.5 w-12 text-center">No</th>
                        <th class="px-5 py-3.5">Ruas Jalan</th>
                        <th class="px-5 py-3.5 hidden md:table-cell">Lokasi & Koridor</th>
                        <th class="px-5 py-3.5 text-center">STA & Panjang Ruas</th>
                        <th class="px-5 py-3.5 text-center bg-blue-50/50">
                            <button type="button" @click="toggleSortOrder()" class="mx-auto flex items-center justify-center gap-1.5 text-blue-900 font-extrabold hover:underline">
                                <span x-text="'Panjang ' + getKondisiLabel()"></span>
                                <svg x-show="sortOrder === 'desc'" class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 17l-4 4m0 0l-4-4m4 4V3"/></svg>
                                <svg x-show="sortOrder === 'asc'" class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7l4-4m0 0l4 4m-4-4v18"/></svg>
                            </button>
                        </th>
                        <th class="px-5 py-3.5 text-center hidden lg:table-cell">Breakdown Kondisi</th>
                        <th class="px-5 py-3.5 text-right w-36">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    <template x-for="(ruas, index) in paginatedRuas()" :key="ruas.id">
                        <tr class="hover:bg-blue-50/30 transition-colors">
                            <!-- No -->
                            <td class="px-4 py-4 text-xs font-semibold text-gray-400 text-center" x-text="(currentPage - 1) * perPage + index + 1"></td>
                            
                            <!-- Ruas Jalan -->
                            <td class="px-5 py-4">
                                <div class="flex flex-col gap-1">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-blue-50 text-blue-700 text-[11px] font-bold border border-blue-100" x-text="ruas.kode_ruas"></span>
                                        <span class="text-sm font-bold text-gray-900" x-text="ruas.nama_ruas"></span>
                                    </div>
                                    <div class="text-xs text-gray-500 md:hidden flex items-center gap-1 mt-0.5">
                                        <span x-text="ruas.kabupaten_kota || '-'"></span>
                                        <span>•</span>
                                        <span x-text="ruas.koridor || '-'"></span>
                                    </div>
                                </div>
                            </td>

                            <!-- Lokasi & Koridor (Desktop) -->
                            <td class="px-5 py-4 hidden md:table-cell">
                                <div class="flex flex-col">
                                    <span class="text-sm font-medium text-gray-800" x-text="ruas.kabupaten_kota || '-'"></span>
                                    <span class="text-xs font-medium text-gray-400" x-text="ruas.koridor ? 'Koridor: ' + ruas.koridor : '-'"></span>
                                </div>
                            </td>

                            <!-- STA & Total Panjang Ruas -->
                            <td class="px-5 py-4 text-center">
                                <div class="flex flex-col items-center gap-0.5">
                                    <span class="text-xs font-mono font-semibold text-gray-700 bg-gray-100 px-2 py-0.5 rounded" x-text="ruas.sta_awal_str + ' s/d ' + ruas.sta_akhir_str"></span>
                                    <span class="text-[11px] font-medium text-gray-500" x-text="formatNumber(ruas.total_panjang / 1000) + ' km (' + formatNumber(ruas.total_panjang) + ' m)'"></span>
                                </div>
                            </td>

                            <!-- Panjang Kondisi Terpilih -->
                            <td class="px-5 py-4 text-center bg-blue-50/20">
                                <div class="flex flex-col items-center gap-1">
                                    <span class="text-sm font-extrabold" 
                                          :style="{ color: getKondisiAccent() }"
                                          x-text="formatNumber(getKondisiVal(ruas) / 1000) + ' km'">
                                    </span>
                                    <span class="text-[11px] font-semibold text-gray-600"
                                          x-text="'(' + formatNumber(getKondisiVal(ruas)) + ' m)'">
                                    </span>
                                    <!-- Proporsi terhadap panjang ruas -->
                                    <div class="w-24 bg-gray-200 h-1.5 rounded-full overflow-hidden mt-0.5">
                                        <div class="h-1.5 rounded-full transition-all duration-300"
                                             :style="{ width: getKondisiPctOfRuas(ruas) + '%', backgroundColor: getKondisiAccent() }">
                                        </div>
                                    </div>
                                    <span class="text-[10px] text-gray-400 font-medium" x-text="getKondisiPctOfRuas(ruas) + '% dari ruas'"></span>
                                </div>
                            </td>

                            <!-- Breakdown Kondisi (Desktop) -->
                            <td class="px-5 py-4 hidden lg:table-cell">
                                <div class="flex items-center justify-center gap-2 text-xs">
                                    <div class="flex flex-col items-center p-1.5 bg-emerald-50 rounded-lg min-w-14 border border-emerald-100">
                                        <span class="text-[10px] text-emerald-800 font-semibold">Baik</span>
                                        <span class="font-bold text-emerald-700" x-text="formatNumber(ruas.baik / 1000) + 'km'"></span>
                                    </div>
                                    <div class="flex flex-col items-center p-1.5 bg-yellow-50 rounded-lg min-w-14 border border-yellow-100">
                                        <span class="text-[10px] text-yellow-800 font-semibold">Sedang</span>
                                        <span class="font-bold text-yellow-700" x-text="formatNumber(ruas.sedang / 1000) + 'km'"></span>
                                    </div>
                                    <div class="flex flex-col items-center p-1.5 bg-orange-50 rounded-lg min-w-14 border border-orange-100">
                                        <span class="text-[10px] text-orange-800 font-semibold">R.Ringan</span>
                                        <span class="font-bold text-orange-700" x-text="formatNumber(ruas.rusak_ringan / 1000) + 'km'"></span>
                                    </div>
                                    <div class="flex flex-col items-center p-1.5 bg-red-50 rounded-lg min-w-14 border border-red-100">
                                        <span class="text-[10px] text-red-800 font-semibold">R.Berat</span>
                                        <span class="font-bold text-red-700" x-text="formatNumber(ruas.rusak_berat / 1000) + 'km'"></span>
                                    </div>
                                </div>
                            </td>

                            <!-- Action Button -->
                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                <a :href="ruas.url_stripmap"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 text-white text-xs font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-sm"
                                   title="Buka Visualisasi Strip Map">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2z"/>
                                    </svg>
                                    <span>Strip Map</span>
                                </a>
                            </td>
                        </tr>
                    </template>

                    <!-- Empty State -->
                    <tr x-show="filteredRuas().length === 0">
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center text-gray-400">
                                <svg class="w-12 h-12 mb-3 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="text-sm font-semibold text-gray-600">Tidak ada ruas jalan yang memiliki kondisi <span x-text="getKondisiLabel()"></span>.</p>
                                <p class="text-xs text-gray-400 mt-1">Coba sesuaikan kata kunci pencarian atau pilih kondisi lainnya.</p>
                                <button type="button" @click="resetFilters()" class="mt-3 text-xs font-semibold text-blue-600 hover:text-blue-800 hover:underline">Reset Filter & Pencarian</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls (Max 10 per page) -->
        <div x-show="totalPages() > 1" 
             class="bg-white border-t border-gray-200 px-5 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <!-- Info Text -->
            <div class="text-xs text-gray-500 font-medium">
                Menampilkan <span class="font-bold text-gray-900" x-text="(currentPage - 1) * perPage + 1"></span> sampai 
                <span class="font-bold text-gray-900" x-text="Math.min(currentPage * perPage, filteredRuas().length)"></span> dari 
                <span class="font-bold text-gray-900" x-text="filteredRuas().length"></span> ruas jalan
            </div>
            
            <!-- Page Buttons -->
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

                <!-- Page Numbers -->
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
function dashboardDetailTable(initialData, initialKondisi) {
    return {
        ruasList: initialData,
        selectedKondisi: initialKondisi,
        currentPage: 1,
        perPage: 10,
        searchQuery: '',
        selectedKoridor: '',
        selectedKabupaten: '',
        sortOrder: 'desc', // Default terpanjang hingga terpendek
        
        kondisiMetaMap: {
            'rusak_ringan': { label: 'Rusak Ringan', accent: '#f97316' },
            'rusak_berat':  { label: 'Rusak Berat',  accent: '#ef4444' },
            'baik':         { label: 'Baik',         accent: '#10b981' },
            'sedang':       { label: 'Sedang',       accent: '#facc15' },
            'mantap':       { label: 'Mantap',       accent: '#10b981' },
            'tidak_mantap': { label: 'Tidak Mantap', accent: '#f43f5e' }
        },

        init() {
            this.$watch('searchQuery', () => this.currentPage = 1);
            this.$watch('selectedKoridor', () => this.currentPage = 1);
            this.$watch('selectedKabupaten', () => this.currentPage = 1);
            this.$watch('selectedKondisi', () => this.currentPage = 1);
        },

        switchKondisi(key) {
            this.selectedKondisi = key;
            this.currentPage = 1;
            // Update URL query param tanpa reload halaman
            const newUrl = window.location.pathname + '?kondisi=' + key;
            window.history.replaceState({}, '', newUrl);
        },

        getKondisiLabel() {
            return (this.kondisiMetaMap[this.selectedKondisi] || {}).label || 'Kondisi';
        },

        getKondisiAccent() {
            return (this.kondisiMetaMap[this.selectedKondisi] || {}).accent || '#3b82f6';
        },

        getKondisiVal(ruas) {
            const key = this.selectedKondisi;
            return parseFloat(ruas[key]) || 0;
        },

        getKondisiPctOfRuas(ruas) {
            const val = this.getKondisiVal(ruas);
            const total = parseFloat(ruas.total_panjang) || 0;
            if (total <= 0) return '0.0';
            return ((val / total) * 100).toFixed(1);
        },

        countRuasWithKondisi(key) {
            return this.ruasList.filter(r => (parseFloat(r[key]) || 0) > 0).length;
        },

        getTotalPanjangKondisiM() {
            return this.filteredRuas().reduce((sum, r) => sum + this.getKondisiVal(r), 0);
        },

        getTotalPanjangKondisiKm() {
            return this.getTotalPanjangKondisiM() / 1000;
        },

        toggleSortOrder() {
            this.sortOrder = this.sortOrder === 'desc' ? 'asc' : 'desc';
        },

        resetFilters() {
            this.searchQuery = '';
            this.selectedKoridor = '';
            this.selectedKabupaten = '';
            this.sortOrder = 'desc';
            this.currentPage = 1;
        },

        getUniqueKoridor() {
            const list = this.ruasList.map(r => r.koridor).filter(val => val !== null && val !== '');
            return [...new Set(list)].sort();
        },

        getUniqueKabupaten() {
            const list = this.ruasList.map(r => r.kabupaten_kota).filter(val => val !== null && val !== '');
            return [...new Set(list)].sort();
        },

        totalPages() {
            return Math.ceil(this.filteredRuas().length / this.perPage) || 1;
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
            const pages = [1];
            let start = Math.max(2, current - 1);
            let end = Math.min(total - 1, current + 1);
            
            if (current <= 2) end = 3;
            else if (current >= total - 1) start = total - 2;
            
            if (start > 2) pages.push('...');
            for (let i = start; i <= end; i++) pages.push(i);
            if (end < total - 1) pages.push('...');
            pages.push(total);
            return pages;
        },

        formatNumber(num) {
            return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(num);
        },

        filteredRuas() {
            let result = this.ruasList.filter(r => this.getKondisiVal(r) > 0);

            // Search Filter
            if (this.searchQuery.trim() !== '') {
                const q = this.searchQuery.toLowerCase().trim();
                result = result.filter(r => 
                    r.nama_ruas.toLowerCase().includes(q) || 
                    r.kode_ruas.toLowerCase().includes(q)
                );
            }

            // Koridor Filter
            if (this.selectedKoridor !== '') {
                result = result.filter(r => r.koridor === this.selectedKoridor);
            }

            // Kabupaten/Wilayah Filter
            if (this.selectedKabupaten !== '') {
                result = result.filter(r => r.kabupaten_kota === this.selectedKabupaten);
            }

            // Sorting: Default Terpanjang ke Terpendek (desc) berdasarkan kondisi terpilih
            result.sort((a, b) => {
                const valA = this.getKondisiVal(a);
                const valB = this.getKondisiVal(b);
                if (valA < valB) return this.sortOrder === 'asc' ? -1 : 1;
                if (valA > valB) return this.sortOrder === 'asc' ? 1 : -1;
                return 0;
            });

            return result;
        }
    };
}
</script>
