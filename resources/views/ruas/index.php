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
        <a href="<?= base_url('ruas/create') ?>"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-xl hover:bg-blue-700 transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Ruas
        </a>
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
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 7.89"/>
                        </svg>
                        Reset
                    </button>
                </div>

            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-16 whitespace-nowrap">No</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-40 whitespace-nowrap">
                                <button type="button" @click="sortByCol('kode_ruas')" class="flex items-center gap-1 hover:text-gray-900 focus:outline-none uppercase">
                                    Kode (Nomor)
                                    <template x-if="sortBy === 'kode_ruas'">
                                        <span>
                                            <svg x-show="sortOrder === 'asc'" class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7l4-4m0 0l4 4m-4-4v18"/></svg>
                                            <svg x-show="sortOrder === 'desc'" class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 17l-4 4m0 0l-4-4m4 4V3"/></svg>
                                        </span>
                                    </template>
                                    <template x-if="sortBy !== 'kode_ruas'">
                                        <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/></svg>
                                    </template>
                                </button>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider min-w-[200px] whitespace-nowrap">
                                <button type="button" @click="sortByCol('nama_ruas')" class="flex items-center gap-1 hover:text-gray-900 focus:outline-none uppercase">
                                    Nama Ruas
                                    <template x-if="sortBy === 'nama_ruas'">
                                        <span>
                                            <svg x-show="sortOrder === 'asc'" class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7l4-4m0 0l4 4m-4-4v18"/></svg>
                                            <svg x-show="sortOrder === 'desc'" class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 17l-4 4m0 0l-4-4m4 4V3"/></svg>
                                        </span>
                                    </template>
                                    <template x-if="sortBy !== 'nama_ruas'">
                                        <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/></svg>
                                    </template>
                                </button>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-44 whitespace-nowrap">
                                <button type="button" @click="sortByCol('koridor')" class="flex items-center gap-1 hover:text-gray-900 focus:outline-none uppercase">
                                    Koridor
                                    <template x-if="sortBy === 'koridor'">
                                        <span>
                                            <svg x-show="sortOrder === 'asc'" class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7l4-4m0 0l4 4m-4-4v18"/></svg>
                                            <svg x-show="sortOrder === 'desc'" class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 17l-4 4m0 0l-4-4m4 4V3"/></svg>
                                        </span>
                                    </template>
                                    <template x-if="sortBy !== 'koridor'">
                                        <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/></svg>
                                    </template>
                                </button>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-52 whitespace-nowrap">
                                <button type="button" @click="sortByCol('kabupaten_kota')" class="flex items-center gap-1 hover:text-gray-900 focus:outline-none uppercase">
                                    Kabupaten / Kota
                                    <template x-if="sortBy === 'kabupaten_kota'">
                                        <span>
                                            <svg x-show="sortOrder === 'asc'" class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7l4-4m0 0l4 4m-4-4v18"/></svg>
                                            <svg x-show="sortOrder === 'desc'" class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 17l-4 4m0 0l-4-4m4 4V3"/></svg>
                                        </span>
                                    </template>
                                    <template x-if="sortBy !== 'kabupaten_kota'">
                                        <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/></svg>
                                    </template>
                                </button>
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-28 whitespace-nowrap">
                                <button type="button" @click="sortByCol('sta_awal')" class="mx-auto flex items-center gap-1 hover:text-gray-900 focus:outline-none uppercase">
                                    STA Awal
                                    <template x-if="sortBy === 'sta_awal'">
                                        <span>
                                            <svg x-show="sortOrder === 'asc'" class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7l4-4m0 0l4 4m-4-4v18"/></svg>
                                            <svg x-show="sortOrder === 'desc'" class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 17l-4 4m0 0l-4-4m4 4V3"/></svg>
                                        </span>
                                    </template>
                                    <template x-if="sortBy !== 'sta_awal'">
                                        <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/></svg>
                                    </template>
                                </button>
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-28 whitespace-nowrap">
                                <button type="button" @click="sortByCol('sta_akhir')" class="mx-auto flex items-center gap-1 hover:text-gray-900 focus:outline-none uppercase">
                                    STA Akhir
                                    <template x-if="sortBy === 'sta_akhir'">
                                        <span>
                                            <svg x-show="sortOrder === 'asc'" class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7l4-4m0 0l4 4m-4-4v18"/></svg>
                                            <svg x-show="sortOrder === 'desc'" class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 17l-4 4m0 0l-4-4m4 4V3"/></svg>
                                        </span>
                                    </template>
                                    <template x-if="sortBy !== 'sta_akhir'">
                                        <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/></svg>
                                    </template>
                                </button>
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-36 whitespace-nowrap">
                                <button type="button" @click="sortByCol('panjang')" class="mx-auto flex items-center gap-1 hover:text-gray-900 focus:outline-none uppercase">
                                    Panjang (m)
                                    <template x-if="sortBy === 'panjang'">
                                        <span>
                                            <svg x-show="sortOrder === 'asc'" class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7l4-4m0 0l4 4m-4-4v18"/></svg>
                                            <svg x-show="sortOrder === 'desc'" class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 17l-4 4m0 0l-4-4m4 4V3"/></svg>
                                        </span>
                                    </template>
                                    <template x-if="sortBy !== 'panjang'">
                                        <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/></svg>
                                    </template>
                                </button>
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-56 whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-for="(ruas, index) in filteredRuas()" :key="ruas.id">
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap" x-text="index + 1"></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg bg-blue-50 text-blue-700 text-xs font-semibold" x-text="ruas.kode_ruas"></span>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900 min-w-[200px]" x-text="ruas.nama_ruas"></td>
                                <td class="px-6 py-4 text-sm text-gray-600 whitespace-nowrap" x-text="ruas.koridor || '-'"></td>
                                <td class="px-6 py-4 text-sm text-gray-600 whitespace-nowrap" x-text="ruas.kabupaten_kota || '-'"></td>
                                <td class="px-6 py-4 text-sm text-gray-600 text-center font-mono whitespace-nowrap" x-text="ruas.sta_awal_str"></td>
                                <td class="px-6 py-4 text-sm text-gray-600 text-center font-mono whitespace-nowrap" x-text="ruas.sta_akhir_str"></td>
                                <td class="px-6 py-4 text-sm text-gray-600 text-center font-semibold whitespace-nowrap" x-text="formatNumber(ruas.panjang)"></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-2">
                                        <a :href="ruas.url_stripmap"
                                           class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-indigo-700 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition-colors"
                                           title="Strip Map">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2z"/>
                                            </svg>
                                            Strip Map
                                        </a>
                                        <a :href="ruas.url_edit"
                                           class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-amber-700 bg-amber-50 rounded-lg hover:bg-amber-100 transition-colors"
                                           title="Edit">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            Edit
                                        </a>
                                        <a :href="ruas.url_delete"
                                           @click="confirmDelete($event, ruas.url_delete, 'Yakin ingin menghapus ruas ini? Semua data strip map terkait juga akan dihapus.')"
                                           class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-red-700 bg-red-50 rounded-lg hover:bg-red-100 transition-colors"
                                           title="Hapus">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <!-- Bila Filter Tidak Menemukan Apapun -->
                        <tr x-show="filteredRuas().length === 0">
                            <td colspan="9" class="px-6 py-12 text-center">
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
        </div>

    </div>

    <script>
    function ruasTable(initialRuasList) {
        return {
            ruasList: initialRuasList,
            searchQuery: '',
            selectedKoridor: '',
            selectedKabupaten: '',
            sortBy: 'kode_ruas',
            sortOrder: 'asc',

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
        <p class="text-sm text-gray-500 mb-6">Mulai dengan menambahkan ruas jalan pertama Anda.</p>
        <a href="<?= base_url('ruas/create') ?>"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-xl hover:bg-blue-700 transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Ruas Jalan
        </a>
    </div>
    <?php endif; ?>

</div>
