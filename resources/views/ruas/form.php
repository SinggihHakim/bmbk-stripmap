<!-- ============================================================ -->
<!-- Form Tambah / Edit Ruas Jalan (Beserta Strip Map) -->
<!-- ============================================================ -->

<?php
    $isEdit  = isset($ruas);
    $action  = $isEdit ? base_url('ruas/update/' . $ruas['id']) : base_url('ruas/store');
    $heading = $isEdit ? 'Edit Ruas Jalan' : 'Tambah Ruas Jalan';

    // Ambil old rows data kalau ada form validation error
    $oldRows = $_SESSION['old_rows'] ?? null;
    if ($oldRows) {
        unset($_SESSION['old_rows']);
    }
?>

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
            <h1 class="text-2xl font-bold text-gray-900"><?= $heading ?></h1>
            <p class="mt-1 text-sm text-gray-500">
                <?= $isEdit ? 'Perbarui data ruas jalan.' : 'Isi form berikut untuk menambahkan ruas jalan baru beserta kondisi strip map nya.' ?>
            </p>
        </div>
    </div>

    <!-- Form Card -->
    <div x-data="ruasDanStripmapForm()">
        <form action="<?= $action ?>" method="POST" class="space-y-6" @submit="return validateForm($event)">

            <!-- SECTION 1: DATA RUAS JALAN -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/50">
                    <h2 class="text-lg font-semibold text-gray-800">1. Data Ruas Jalan</h2>
                </div>
                <div class="p-6 space-y-6">
                    <!-- Kode Ruas -->
                    <div>
                        <label for="kode_ruas" class="block text-sm font-medium text-gray-700 mb-1.5">Kode Ruas</label>
                        <input type="text" id="kode_ruas" name="kode_ruas"
                               value="<?= e($isEdit ? $ruas['kode_ruas'] : old('kode_ruas')) ?>"
                               placeholder="Contoh: RJ-001"
                               class="w-full px-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                               required>
                    </div>

                    <!-- Nama Ruas -->
                    <div>
                        <label for="nama_ruas" class="block text-sm font-medium text-gray-700 mb-1.5">Nama Ruas</label>
                        <input type="text" id="nama_ruas" name="nama_ruas"
                               value="<?= e($isEdit ? $ruas['nama_ruas'] : old('nama_ruas')) ?>"
                               placeholder="Contoh: Jl. Ahmad Yani"
                               class="w-full px-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                               required>
                    </div>
                </div>
            </div>

            <!-- SECTION 2: DATA STRIP MAP (Kondisi Jalan) -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/50 flex justify-between items-center">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800">2. Kondisi Jalan (Strip Map)</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Isi detail kondisi jalan secara berurutan. (Minimal 1 baris diisi penuh)</p>
                    </div>
                </div>

                <div class="p-6">
                    <div class="overflow-x-auto border rounded-lg mb-4">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-700 bg-gray-50 border-b">
                                <tr>
                                    <th class="px-3 py-3 w-32">STA Awal</th>
                                    <th class="px-3 py-3 w-32">STA Akhir</th>
                                    <th class="px-3 py-3 w-24">Panjang</th>
                                    <th class="px-3 py-3">Baik (m)</th>
                                    <th class="px-3 py-3">Sedang (m)</th>
                                    <th class="px-3 py-3">R. Ringan (m)</th>
                                    <th class="px-3 py-3">R. Berat (m)</th>
                                    <th class="px-3 py-3 w-16 text-center">Status</th>
                                    <th class="px-3 py-3 w-16 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(row, index) in rows" :key="row.id">
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="p-2">
                                            <input type="text" :name="`rows[${index}][sta_awal]`" x-model="row.staAwal" @blur="row.staAwal = formatStaValue(row.staAwal); calculateRow(row)" @input="onStaInput($event, row, 'awal')" placeholder="0+000" class="w-full px-2 py-1.5 rounded border border-gray-300 font-mono text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        </td>
                                        <td class="p-2">
                                            <input type="text" :name="`rows[${index}][sta_akhir]`" x-model="row.staAkhir" @blur="row.staAkhir = formatStaValue(row.staAkhir); calculateRow(row)" @input="onStaInput($event, row, 'akhir')" placeholder="1+000" class="w-full px-2 py-1.5 rounded border border-gray-300 font-mono text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        </td>
                                        <td class="p-2">
                                            <div class="font-mono font-semibold px-2" :class="row.error ? 'text-red-600' : 'text-gray-700'" x-text="row.panjang > 0 ? formatNumber(row.panjang) : '-'"></div>
                                        </td>
                                        <td class="p-2">
                                            <input type="number" :name="`rows[${index}][baik]`" x-model.number="row.baik" @input="calculateRow(row)" min="0" step="0.01" class="w-full px-2 py-1.5 rounded border border-gray-300 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                        </td>
                                        <td class="p-2">
                                            <input type="number" :name="`rows[${index}][sedang]`" x-model.number="row.sedang" @input="calculateRow(row)" min="0" step="0.01" class="w-full px-2 py-1.5 rounded border border-gray-300 text-sm focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                                        </td>
                                        <td class="p-2">
                                            <input type="number" :name="`rows[${index}][rusak_ringan]`" x-model.number="row.rusakRingan" @input="calculateRow(row)" min="0" step="0.01" class="w-full px-2 py-1.5 rounded border border-gray-300 text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                        </td>
                                        <td class="p-2">
                                            <input type="number" :name="`rows[${index}][rusak_berat]`" x-model.number="row.rusakBerat" @input="calculateRow(row)" min="0" step="0.01" class="w-full px-2 py-1.5 rounded border border-gray-300 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                        </td>
                                        <td class="p-2 text-center">
                                            <div class="flex justify-center" :title="row.error || 'Valid'">
                                                <svg x-show="row.isValid" class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                                <svg x-show="!row.isValid && !isRowEmpty(row)" class="w-5 h-5 text-red-500 cursor-help" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                </svg>
                                            </div>
                                        </td>
                                        <td class="p-2 text-center">
                                            <button type="button" @click="removeRow(index)" x-show="rows.length > 1" class="text-red-500 hover:text-red-700 p-1 rounded hover:bg-red-50 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex justify-start">
                        <button type="button" @click="addRow()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-blue-700 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                            </svg>
                            Tambah Baris Strip Map
                        </button>
                    </div>

                    <!-- Error Messages Summary (Strip Map) -->
                    <div x-show="formErrors.length > 0" class="mt-4 p-4 rounded-xl bg-red-50 border border-red-200">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-red-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <div>
                                <h4 class="text-sm font-medium text-red-800">Terdapat error pada input Strip Map:</h4>
                                <ul class="mt-1 text-sm text-red-700 list-disc list-inside">
                                    <template x-for="err in formErrors" :key="err">
                                        <li x-text="err"></li>
                                    </template>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Preview Visual Baris Aktif -->
                <div x-show="validRows.length > 0" x-cloak class="px-6 py-4 border-t border-gray-100 bg-gray-50/30">
                    <h3 class="text-sm font-semibold text-gray-900 mb-4">Preview Visual</h3>
                    <div class="space-y-4">
                        <template x-for="(row, index) in validRows" :key="row.id">
                            <div>
                                <div class="flex justify-between items-center mb-1 text-xs">
                                    <span class="font-medium text-gray-700">STA <span x-text="row.staAwal"></span> - <span x-text="row.staAkhir"></span></span>
                                    <span class="text-gray-500" x-text="formatNumber(row.panjang) + ' m'"></span>
                                </div>
                                <div class="flex h-5 rounded-md overflow-hidden shadow-sm">
                                    <template x-for="(segment, idx) in getSegments(row)" :key="segment.key">
                                        <div class="relative group transition-all duration-300 ease-out"
                                             :style="'width:' + segment.percent + '%; ' + segment.bgStyle"
                                             x-show="segment.value > 0">
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 flex items-center justify-end gap-3 sticky bottom-4 z-10">
                <a href="<?= base_url('ruas') ?>"
                   class="inline-flex items-center gap-2 px-6 py-2.5 bg-gray-100 text-gray-700 text-sm font-medium rounded-xl hover:bg-gray-200 transition-colors">
                    Batal
                </a>
                <button type="submit"
                        :disabled="!isReadyToSubmit"
                        :class="isReadyToSubmit ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-300 cursor-not-allowed'"
                        class="inline-flex items-center gap-2 px-6 py-2.5 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    <?= $isEdit ? 'Perbarui Ruas' : 'Simpan Data' ?>
                </button>
            </div>

        </form>
    </div>

</div>

<script>
function ruasDanStripmapForm() {

    const isEdit = <?= $isEdit ? 'true' : 'false' ?>;
    let initialRows = [];

    <?php if ($oldRows): ?>
        // Bila sebelumnya ada input gagal validasi
        initialRows = <?= json_encode(array_values($oldRows)) ?>;
        initialRows = initialRows.map((r, idx) => ({
            id: Date.now() + idx,
            staAwal: r.sta_awal || '',
            staAkhir: r.sta_akhir || '',
            panjang: 0,
            baik: r.baik !== '' ? parseFloat(r.baik) : '',
            sedang: r.sedang !== '' ? parseFloat(r.sedang) : '',
            rusakRingan: r.rusak_ringan !== '' ? parseFloat(r.rusak_ringan) : '',
            rusakBerat: r.rusak_berat !== '' ? parseFloat(r.rusak_berat) : '',
            error: '',
            isValid: false
        }));
    <?php elseif ($isEdit && !empty($stripmaps)): ?>
        // Bila mode edit dan data strip map ada di database
        <?php
            $formattedStripmaps = array_map(function($sm) {
                return [
                    'sta_awal'     => meter_to_sta($sm['sta_awal']),
                    'sta_akhir'    => meter_to_sta($sm['sta_akhir']),
                    'baik'         => (float)$sm['baik'],
                    'sedang'       => (float)$sm['sedang'],
                    'rusak_ringan' => (float)$sm['rusak_ringan'],
                    'rusak_berat'  => (float)$sm['rusak_berat'],
                ];
            }, $stripmaps);
        ?>
        initialRows = <?= json_encode($formattedStripmaps) ?>;
        initialRows = initialRows.map((r, idx) => ({
            id: Date.now() + idx,
            staAwal: r.sta_awal,
            staAkhir: r.sta_akhir,
            panjang: 0,
            baik: r.baik,
            sedang: r.sedang,
            rusakRingan: r.rusak_ringan,
            rusakBerat: r.rusak_berat,
            error: '',
            isValid: false
        }));
    <?php else: ?>
        // Default 3 baris kosong jika create baru
        for(let i=0; i<3; i++) {
            initialRows.push({
                id: Date.now() + i,
                staAwal: '',
                staAkhir: '',
                panjang: 0,
                baik: '',
                sedang: '',
                rusakRingan: '',
                rusakBerat: '',
                error: '',
                isValid: false
            });
        }
    <?php endif; ?>

    return {
        // Data Ruas
        // Data Stripmap
        rows: initialRows,

        init() {
            this.rows.forEach(row => this.calculateRow(row));
        },

        // --- RUAS LOGIC ---
        staToMeter(sta) {
            if(!sta) return 0;
            sta = sta.trim();
            if (sta.includes('+')) {
                const parts = sta.split('+');
                return parseFloat(parts[0]) * 1000 + parseFloat(parts[1] || 0);
            }
            return parseFloat(sta) || 0;
        },

        // --- STRIPMAP LOGIC ---
        addRow() {
            let lastStaAkhir = '';
            if (this.rows.length > 0) {
                lastStaAkhir = this.rows[this.rows.length - 1].staAkhir;
            }

            this.rows.push({
                id: Date.now(),
                staAwal: lastStaAkhir,
                staAkhir: '',
                panjang: 0,
                baik: '',
                sedang: '',
                rusakRingan: '',
                rusakBerat: '',
                error: '',
                isValid: false
            });
        },

        removeRow(index) {
            this.rows.splice(index, 1);
        },

        isRowEmpty(row) {
            return !row.staAwal && !row.staAkhir && row.baik === '' && row.sedang === '' && row.rusakRingan === '' && row.rusakBerat === '';
        },

        calculateRow(row) {
            row.error = '';
            row.isValid = false;

            if (this.isRowEmpty(row)) {
                row.panjang = 0;
                return; // baris kosong dibiarkan validasi pasif
            }

            const baik = parseFloat(row.baik) || 0;
            const sedang = parseFloat(row.sedang) || 0;
            const rRingan = parseFloat(row.rusakRingan) || 0;
            const rBerat = parseFloat(row.rusakBerat) || 0;
            const totalKondisi = baik + sedang + rRingan + rBerat;

            if (row.staAwal && row.staAkhir) {
                const awal  = this.staToMeter(row.staAwal);
                const akhir = this.staToMeter(row.staAkhir);

                if (awal < 0 || akhir < 0) {
                    row.error = 'STA tidak boleh negatif.';
                    row.panjang = 0;
                } else if (akhir <= awal) {
                    row.error = 'STA Akhir harus > STA Awal.';
                    row.panjang = 0;
                } else {
                    row.panjang = akhir - awal;

                    if (row.panjang > 0) {
                        const selisih = Math.abs(totalKondisi - row.panjang);
                        if (selisih > 0.01) {
                            row.error = `Selisih kondisi: ${this.formatNumber(selisih)} m`;
                        } else {
                            row.isValid = true;
                        }
                    }
                }
            } else {
                row.panjang = 0;
                row.error = 'STA harus diisi lengkap.';
            }
        },

        get formErrors() {
            let errors = [];
            this.rows.forEach((row, idx) => {
                if (!this.isRowEmpty(row) && row.error) {
                    errors.push(`Baris ${idx + 1}: ${row.error}`);
                }
                if (!this.isRowEmpty(row) && !row.error && !row.isValid) {
                    errors.push(`Baris ${idx + 1}: Data belum lengkap/valid.`);
                }
            });
            return errors;
        },

        get activeRows() {
            return this.rows.filter(row => !this.isRowEmpty(row));
        },

        get validRows() {
            return this.rows.filter(row => row.isValid);
        },

        // Button submit disabled/enabled rules
        get isReadyToSubmit() {
            // Cek stripmap, kalau ada yg nanggung error gak boleh disubmit
            if (this.formErrors.length > 0) return false;

            // Boleh submit meskipun activeRows 0, tapi kalau ada activeRow dia harus valid semua
            return true;
        },

        formatStaValue(val) {
            if (!val) return '';
            val = val.toString().trim().replace(',', '.');
            
            let totalMeters = 0;
            if (val.includes('+')) {
                const parts = val.split('+');
                const km = parseFloat(parts[0]) || 0;
                const m = parseFloat(parts[1]) || 0;
                totalMeters = km * 1000 + m;
            } else {
                const num = parseFloat(val);
                if (isNaN(num)) return val;
                
                if (val.includes('.')) {
                    totalMeters = num * 1000;
                } else {
                    if (num < 10) {
                        totalMeters = num * 1000;
                    } else {
                        totalMeters = num;
                    }
                }
            }
            
            const km = Math.floor(totalMeters / 1000);
            const m = Math.round(totalMeters % 1000);
            const mStr = String(m).padStart(3, '0');
            return `${km}+${mStr}`;
        },

        onStaInput(event, row, field) {
            let val = event.target.value;
            
            if (event.inputType && event.inputType.startsWith('delete')) {
                if (field === 'awal') {
                    row.staAwal = val;
                } else {
                    row.staAkhir = val;
                }
                this.calculateRow(row);
                return;
            }
            
            val = val.replace(/[^0-9+]/g, '');
            
            if (val.length === 1 && /^\d$/.test(val)) {
                val = val + '+';
            }
            
            if (field === 'awal') {
                row.staAwal = val;
            } else {
                row.staAkhir = val;
            }
            this.calculateRow(row);
        },

        formatNumber(num) {
            return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(num);
        },

        getSegments(row) {
            const baik = parseFloat(row.baik) || 0;
            const sedang = parseFloat(row.sedang) || 0;
            const rRingan = parseFloat(row.rusakRingan) || 0;
            const rBerat = parseFloat(row.rusakBerat) || 0;
            const total = baik + sedang + rRingan + rBerat || 1;

            const conditions = [
                { key: 'baik', value: baik, percent: (baik / total) * 100, color: '#22c55e' },
                { key: 'sedang', value: sedang, percent: (sedang / total) * 100, color: '#eab308' },
                { key: 'rusak_ringan', value: rRingan, percent: (rRingan / total) * 100, color: '#f97316' },
                { key: 'rusak_berat', value: rBerat, percent: (rBerat / total) * 100, color: '#ef4444' }
            ];

            const active = conditions.filter(c => c.value > 0);

            return active.map((c, idx) => {
                c.bgStyle = `background-color: ${c.color}`;
                return c;
            });
        },

        validateForm(event) {
            // hapus input yg benar2 kosong agar tidak ikut dikirim jadi $_POST
            const inputs = event.target.querySelectorAll('input');
            inputs.forEach(input => {
                const rowNameMatch = input.name.match(/rows\[(\d+)\]/);
                if (rowNameMatch) {
                    const index = parseInt(rowNameMatch[1]);
                    if (this.isRowEmpty(this.rows[index])) {
                        input.disabled = true; // prevent submit
                    }
                }
            });

            if (!this.isReadyToSubmit) {
                event.preventDefault();
                alert('Terdapat data yang tidak valid. Periksa form.');
                return false;
            }
            return true;
        }
    }
}
</script>
