<!-- ============================================================ -->
<!-- Halaman Daftar Ruas Jalan -->
<!-- ============================================================ -->

<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Data Ruas Jalan</h1>
            <p class="mt-1 text-sm text-gray-500">Kelola semua data ruas jalan Anda.</p>
        </div>
    </div>

    <!-- Table Card & Filters -->
    <?php if (!empty($ruasList)): ?>
    
    <?php
    // Siapkan data ruas untuk AlpineJS
    $ruasJsonData = [];
    foreach ($ruasList as $ruas) {
        $ruasJsonData[] = [
            'id'             => (int)$ruas['id'],
            'kode_ruas'      => $ruas['kode_ruas'],
            'nama_ruas'      => $ruas['nama_ruas'],
            'sta_awal'       => (float)$ruas['sta_awal'],
            'sta_akhir'      => (float)$ruas['sta_akhir'],
            'sta_awal_str'   => meter_to_sta($ruas['sta_awal']),
            'sta_akhir_str'  => meter_to_sta($ruas['sta_akhir']),
            'panjang'        => (float)$ruas['panjang'],
            'koridor'        => $ruas['koridor'] ?? '',
            'kabupaten_kota' => $ruas['kabupaten_kota'] ?? '',
            'url_stripmap'   => base_url('stripmap/' . $ruas['id']),
            'url_edit'       => base_url('ruas/edit/' . $ruas['id']),
            'url_delete'     => base_url('ruas/delete/' . $ruas['id']),
        ];
    }
    ?>

    <div x-data="ruasTable(<?= htmlspecialchars(json_encode($ruasJsonData), ENT_QUOTES, 'UTF-8') ?>)" class="space-y-6">
        
        <!-- Filters & Search Panel -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex flex-col md:flex-row gap-4 items-stretch md:items-end">
                
                <!-- Pencarian -->
                <div class="flex-1 min-w-0">
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">Pencarian</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
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
                <div class="w-full md:w-64">
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">Filter Koridor</label>
                    <select x-model="selectedKoridor" 
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white">
                        <option value="">Semua Koridor</option>
                        <template x-for="koridor in getUniqueKoridor()" :key="koridor">
                            <option :value="koridor" x-text="koridor"></option>
                        </template>
                    </select>
                </div>

                <!-- Filter Kabupaten / Kota -->
                <div class="w-full md:w-64">
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">Filter Wilayah</label>
                    <select x-model="selectedKabupaten" 
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white">
                        <option value="">Semua Kabupaten/Kota</option>
                        <template x-for="kab in getUniqueKabupaten()" :key="kab">
                            <option :value="kab" x-text="kab"></option>
                        </template>
                    </select>
                </div>

                <!-- Reset Button -->
                <div class="w-full md:w-auto">
                    <button type="button" 
                            @click="resetFilters()" 
                            title="Reset Filter"
                            class="w-full md:w-auto inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl border border-gray-200 bg-gray-50 text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition-colors text-sm font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        Reset
                    </button>
                </div>

            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 text-xs font-bold text-gray-500 uppercase tracking-wider">
                            <th class="px-4 py-3.5 w-12 text-center">No</th>
                            <th class="px-5 py-3.5">
                                <button type="button" @click="sortByCol('nama_ruas')" class="flex items-center gap-1.5 hover:text-gray-900 focus:outline-none">
                                    Ruas Jalan
                                    <template x-if="sortBy === 'nama_ruas' || sortBy === 'kode_ruas'">
                                        <svg x-show="sortOrder === 'asc'" class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7l4-4m0 0l4 4m-4-4v18"/></svg>
                                    </template>
                                </button>
                            </th>
                            <th class="px-5 py-3.5 hidden md:table-cell">
                                <button type="button" @click="sortByCol('kabupaten_kota')" class="flex items-center gap-1.5 hover:text-gray-900 focus:outline-none">
                                    Lokasi & Koridor
                                </button>
                            </th>
                            <th class="px-5 py-3.5 text-center">
                                <button type="button" @click="sortByCol('panjang')" class="mx-auto flex items-center gap-1.5 hover:text-gray-900 focus:outline-none">
                                    Segmen (STA & Panjang)
                                </button>
                            </th>
                            <th class="px-5 py-3.5 text-right w-48">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        <template x-for="(ruas, index) in paginatedRuas()" :key="ruas.id">
                            <tr class="hover:bg-blue-50/30 transition-colors">
                                <!-- No -->
                                <td class="px-4 py-3.5 text-xs font-semibold text-gray-400 text-center" x-text="(currentPage - 1) * perPage + index + 1"></td>
                                
                                <!-- Ruas Jalan (Kode + Nama stacked) -->
                                <td class="px-5 py-3.5">
                                    <div class="flex flex-col gap-1">
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded bg-blue-50 text-blue-700 text-[11px] font-bold border border-blue-100" x-text="ruas.kode_ruas"></span>
                                            <span class="text-sm font-bold text-gray-900" x-text="ruas.nama_ruas"></span>
                                        </div>
                                        <!-- Mobile only location subtitle -->
                                        <div class="text-xs text-gray-500 md:hidden flex items-center gap-1">
                                            <span x-text="ruas.kabupaten_kota || '-'"></span>
                                            <span>•</span>
                                            <span x-text="ruas.koridor || '-'"></span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Lokasi & Koridor (Desktop) -->
                                <td class="px-5 py-3.5 hidden md:table-cell">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-medium text-gray-800" x-text="ruas.kabupaten_kota || '-'"></span>
                                        <span class="text-xs font-medium text-gray-400" x-text="ruas.koridor ? 'Koridor: ' + ruas.koridor : '-'"></span>
                                    </div>
                                </td>

                                <!-- STA & Panjang -->
                                <td class="px-5 py-3.5 text-center">
                                    <div class="flex flex-col items-center gap-0.5">
                                        <span class="text-xs font-mono font-semibold text-gray-700 bg-gray-100 px-2 py-0.5 rounded" x-text="ruas.sta_awal_str + ' s/d ' + ruas.sta_akhir_str"></span>
                                        <span class="text-[11px] font-bold text-emerald-700" x-text="formatNumber(ruas.panjang) + ' m (' + formatNumber(ruas.panjang / 1000) + ' km)'"></span>
                                    </div>
                                </td>

                                <!-- Action Buttons -->
                                <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- Primary Action: Visual Stripmap -->
                                        <a :href="ruas.url_stripmap"
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 text-white text-xs font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-sm"
                                           title="Buka Visualisasi Strip Map">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2z"/>
                                            </svg>
                                            <span>Strip Map</span>
                                        </a>

                                        <!-- Edit Icon Button -->
                                        <a :href="ruas.url_edit"
                                           class="p-1.5 text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200/60 rounded-lg transition-colors"
                                           title="Edit Ruas Jalan">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>

                                        <!-- Delete Icon Button -->
                                        <a :href="ruas.url_delete"
                                           @click="confirmDelete($event, ruas.url_delete, 'Yakin ingin menghapus ruas ini? Semua data strip map terkait juga akan dihapus.')"
                                           class="p-1.5 text-red-700 bg-red-50 hover:bg-red-100 border border-red-200/60 rounded-lg transition-colors"
                                           title="Hapus Ruas Jalan">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <!-- Bila Filter Tidak Menemukan Apapun -->
                        <tr x-show="filteredRuas().length === 0">
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-400">
                                    <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                                    </svg>
                                    <p class="text-sm font-medium">Tidak ada data ruas jalan yang cocok dengan pencarian/filter.</p>
                                    <button type="button" @click="resetFilters()" class="mt-2 text-xs font-semibold text-blue-600 hover:text-blue-800 hover:underline">Reset Filter & Pencarian</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Pagination Controls -->
            <div x-show="totalPages() > 1" 
                 class="bg-white border-t border-gray-200 px-5 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 no-export">
                <!-- Info Text -->
                <div class="text-xs text-gray-500 font-medium">
                    Menampilkan <span class="font-bold text-gray-900" x-text="(currentPage - 1) * perPage + 1"></span> sampai 
                    <span class="font-bold text-gray-900" x-text="Math.min(currentPage * perPage, filteredRuas().length)"></span> dari 
                    <span class="font-bold text-gray-900" x-text="filteredRuas().length"></span> ruas jalan
                </div>
                
                <!-- Page buttons -->
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

                    <!-- Page numbers -->
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
    function ruasTable(initialRuasList) {
        return {
            ruasList: initialRuasList,
            currentPage: 1,
            perPage: 10,
            searchQuery: '',
            selectedKoridor: '',
            selectedKabupaten: '',
            sortBy: 'kode_ruas',
            sortOrder: 'asc',

            init() {
                this.$watch('searchQuery', () => this.currentPage = 1);
                this.$watch('selectedKoridor', () => this.currentPage = 1);
                this.$watch('selectedKabupaten', () => this.currentPage = 1);
            },

            totalPages() {
                return Math.ceil(this.filteredRuas().length / this.perPage);
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
                const pages = [];
                pages.push(1);
                
                let start = Math.max(2, current - 1);
                let end = Math.min(total - 1, current + 1);
                
                if (current <= 2) {
                    end = 3;
                } else if (current >= total - 1) {
                    start = total - 2;
                }
                
                if (start > 2) {
                    pages.push('...');
                }
                
                for (let i = start; i <= end; i++) {
                    pages.push(i);
                }
                
                if (end < total - 1) {
                    pages.push('...');
                }
                
                pages.push(total);
                return pages;
            },

            getUniqueKoridor() {
                const list = this.ruasList.map(r => r.koridor).filter(val => val !== null && val !== '');
                return [...new Set(list)].sort();
            },

            getUniqueKabupaten() {
                const list = this.ruasList.map(r => r.kabupaten_kota).filter(val => val !== null && val !== '');
                return [...new Set(list)].sort();
            },

            sortByCol(col) {
                if (this.sortBy === col) {
                    this.sortOrder = this.sortOrder === 'asc' ? 'desc' : 'asc';
                } else {
                    this.sortBy = col;
                    this.sortOrder = 'asc';
                }
            },

            resetFilters() {
                this.searchQuery = '';
                this.selectedKoridor = '';
                this.selectedKabupaten = '';
                this.sortBy = 'kode_ruas';
                this.sortOrder = 'asc';
                this.currentPage = 1;
            },

            formatNumber(num) {
                return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(num);
            },

            filteredRuas() {
                let result = [...this.ruasList];

                // 1. Search Query
                if (this.searchQuery.trim() !== '') {
                    const query = this.searchQuery.toLowerCase().trim();
                    result = result.filter(r => 
                        r.nama_ruas.toLowerCase().includes(query) || 
                        r.kode_ruas.toLowerCase().includes(query)
                    );
                }

                // 2. Filter Koridor
                if (this.selectedKoridor !== '') {
                    result = result.filter(r => r.koridor === this.selectedKoridor);
                }

                // 3. Filter Kabupaten/Kota
                if (this.selectedKabupaten !== '') {
                    result = result.filter(r => r.kabupaten_kota === this.selectedKabupaten);
                }

                // 4. Sorting
                result.sort((a, b) => {
                    let valA = a[this.sortBy] ? a[this.sortBy].toString().toLowerCase() : '';
                    let valB = b[this.sortBy] ? b[this.sortBy].toString().toLowerCase() : '';

                    if (this.sortBy === 'panjang' || this.sortBy === 'sta_awal' || this.sortBy === 'sta_akhir') {
                        valA = parseFloat(a[this.sortBy]) || 0;
                        valB = parseFloat(b[this.sortBy]) || 0;
                    }

                    if (valA < valB) return this.sortOrder === 'asc' ? -1 : 1;
                    if (valA > valB) return this.sortOrder === 'asc' ? 1 : -1;
                    return 0;
                });

                return result;
            }
        };
    }
    </script>
    <?php else: ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
        </svg>
        <h3 class="text-lg font-semibold text-gray-600 mb-2">Belum ada data ruas jalan</h3>
        <p class="text-sm text-gray-500">Silakan tambahkan ruas jalan melalui menu Tambah Ruas Baru atau Import Excel.</p>
    </div>
    <?php endif; ?>

</div>
