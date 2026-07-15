<!-- ============================================================ -->
<!-- Form Tambah / Edit Strip Map (Batch Insert Support) -->
<!-- ============================================================ -->

<?php
    $isEdit  = isset($stripmap);
    $action  = $isEdit ? base_url('stripmap/update/' . $stripmap['id']) : base_url('stripmap/store/' . $ruas['id']);
    $heading = $isEdit ? 'Edit Segmen Strip Map' : 'Tambah Segmen Strip Map';

    // Ambil old input jika ada error validasi
    $oldInput = $_SESSION['old_input'] ?? null;
    if ($oldInput) {
        unset($_SESSION['old_input']);
    }
?>

<div class="space-y-6">

    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="<?= base_url('stripmap/' . $ruas['id']) ?>"
           class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-white border border-gray-200 hover:bg-gray-50 transition-colors shadow-sm">
            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= $heading ?></h1>
            <p class="mt-1 text-sm text-gray-500">
                Ruas: <span class="font-semibold"><?= e($ruas['nama_ruas']) ?></span>
                (<span class="font-mono"><?= e($ruas['kode_ruas']) ?></span>)
            </p>
        </div>
    </div>

    <!-- Form Card + Realtime Preview -->
    <div x-data="stripmapForm()" class="space-y-6">

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <form action="<?= $action ?>" method="POST" class="p-6 space-y-6" @submit="validateForm($event)">

                <div class="overflow-x-auto">
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
                                <?php if (!$isEdit): ?>
                                <th class="px-3 py-3 w-16 text-center">Aksi</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(row, index) in rows" :key="row.id">
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="p-2">
                                        <input type="text" :name="`rows[${index}][sta_awal]`" x-model="row.staAwal" @blur="row.staAwal = formatStaValue(row.staAwal); calculateRow(row)" @input="onStaInput($event, row, 'awal')" placeholder="0+000" class="w-full px-2 py-1.5 rounded border border-gray-300 font-mono text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                                    </td>
                                    <td class="p-2">
                                        <input type="text" :name="`rows[${index}][sta_akhir]`" x-model="row.staAkhir" @blur="row.staAkhir = formatStaValue(row.staAkhir); calculateRow(row)" @input="onStaInput($event, row, 'akhir')" placeholder="1+000" class="w-full px-2 py-1.5 rounded border border-gray-300 font-mono text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                                    </td>
                                    <td class="p-2">
                                        <div class="font-mono font-semibold px-2" :class="row.error ? 'text-red-600' : 'text-gray-700'" x-text="row.panjang > 0 ? formatNumber(row.panjang) : '-'"></div>
                                    </td>
                                    <td class="p-2">
                                        <input type="number" :name="`rows[${index}][baik]`" x-model.number="row.baik" @input="calculateRow(row)" min="0" step="0.01" class="w-full px-2 py-1.5 rounded border border-gray-300 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500" required>
                                    </td>
                                    <td class="p-2">
                                        <input type="number" :name="`rows[${index}][sedang]`" x-model.number="row.sedang" @input="calculateRow(row)" min="0" step="0.01" class="w-full px-2 py-1.5 rounded border border-gray-300 text-sm focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500" required>
                                    </td>
                                    <td class="p-2">
                                        <input type="number" :name="`rows[${index}][rusak_ringan]`" x-model.number="row.rusakRingan" @input="calculateRow(row)" min="0" step="0.01" class="w-full px-2 py-1.5 rounded border border-gray-300 text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500" required>
                                    </td>
                                    <td class="p-2">
                                        <input type="number" :name="`rows[${index}][rusak_berat]`" x-model.number="row.rusakBerat" @input="calculateRow(row)" min="0" step="0.01" class="w-full px-2 py-1.5 rounded border border-gray-300 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500" required>
                                    </td>
                                    <td class="p-2 text-center">
                                        <div class="flex justify-center" :title="row.error || 'Valid'">
                                            <svg x-show="row.isValid" class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            <svg x-show="!row.isValid && (row.panjang > 0 || row.error)" class="w-5 h-5 text-red-500 cursor-help" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                    </td>
                                    <?php if (!$isEdit): ?>
                                    <td class="p-2 text-center">
                                        <button type="button" @click="removeRow(index)" x-show="rows.length > 1" class="text-red-500 hover:text-red-700 p-1 rounded hover:bg-red-50 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <?php if (!$isEdit): ?>
                <div class="flex justify-start">
                    <button type="button" @click="addRow()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-blue-700 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        Tambah Baris
                    </button>
                </div>
                <?php endif; ?>

                <!-- Error Messages Summary -->
                <div x-show="formErrors.length > 0" class="p-4 rounded-xl bg-red-50 border border-red-200">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-red-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <h4 class="text-sm font-medium text-red-800">Terdapat error pada input:</h4>
                            <ul class="mt-1 text-sm text-red-700 list-disc list-inside">
                                <template x-for="err in formErrors" :key="err">
                                    <li x-text="err"></li>
                                </template>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                    <button type="submit"
                            :disabled="!isFormValid"
                            :class="isFormValid ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-300 cursor-not-allowed'"
                            class="inline-flex items-center gap-2 px-6 py-2.5 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        <?= $isEdit ? 'Perbarui' : 'Simpan Semua' ?>
                    </button>
                    <a href="<?= base_url('stripmap/' . $ruas['id']) ?>"
                       class="inline-flex items-center gap-2 px-6 py-2.5 bg-white text-gray-700 text-sm font-medium rounded-xl border border-gray-300 hover:bg-gray-50 transition-colors">
                        Batal
                    </a>
                </div>

            </form>
        </div>

        <!-- Realtime Strip Map Preview for All Rows -->
        <div x-show="validRows.length > 0" x-cloak
             class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Preview Strip Map (Realtime)</h3>
                <p class="text-xs text-gray-500 mt-0.5">Hover atau klik segmen warna untuk lihat detail kondisi.</p>
            </div>
            <div class="p-6 space-y-6">
                <template x-for="(row, rIdx) in validRows" :key="row.id">
                    <div x-data="{ activeLabel: null, activePct: 0 }">
                        <div class="flex justify-between items-center mb-1 text-sm">
                            <span class="font-medium text-gray-700">STA <span x-text="row.staAwal"></span> - <span x-text="row.staAkhir"></span></span>
                            <span class="text-gray-500" x-text="formatNumber(row.panjang) + ' m'"></span>
                        </div>
                        <!-- Strip Bar -->
                        <div class="flex h-10 rounded-lg overflow-hidden shadow-sm">
                            <template x-for="(segment, idx) in getSegments(row)" :key="segment.key">
                                <div class="relative transition-all duration-300 ease-out cursor-pointer"
                                     :style="'width:' + segment.percent + '%; ' + segment.bgStyle"
                                     x-show="segment.value > 0"
                                     @mouseenter="activeLabel = { panjang: formatNumber(segment.value), kondisi: segment.label, color: segment.color }; activePct = segment.midPct"
                                     @mouseleave="activeLabel = null"
                                     @click="activeLabel = { panjang: formatNumber(segment.value), kondisi: segment.label, color: segment.color }; activePct = segment.midPct">
                                    <!-- Highlight ring -->
                                    <div class="absolute inset-0 ring-2 ring-white/60 ring-inset opacity-0 hover:opacity-100 transition-opacity"></div>
                                </div>
                            </template>
                        </div>
                        <!-- Label di Bawah Bar -->
                        <div class="relative w-full h-0 z-20">
                            <template x-if="activeLabel">
                                <div class="absolute top-1 flex flex-col items-center -translate-x-1/2 transition-all duration-150 ease-out"
                                     :style="'left:' + activePct + '%'">
                                    <div class="w-px h-2.5" :style="'background-color:' + activeLabel.color"></div>
                                    <div class="mt-0.5 px-2 py-1 rounded-md border shadow-sm text-center whitespace-nowrap backdrop-blur-sm"
                                         :style="'border-color:' + activeLabel.color + '40; background-color:' + activeLabel.color + '15'">
                                        <p class="text-xs font-bold" :style="'color:' + activeLabel.color" x-text="activeLabel.panjang + ' m'"></p>
                                        <p class="text-[10px] font-semibold text-gray-600" x-text="activeLabel.kondisi"></p>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- Legend -->
                <div class="flex flex-wrap gap-4 pt-4 border-t border-gray-100 justify-center">
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full" style="background:#10b981"></span><span class="text-xs text-gray-600">Baik</span></div>
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full" style="background:#eab308"></span><span class="text-xs text-gray-600">Sedang</span></div>
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full" style="background:#f97316"></span><span class="text-xs text-gray-600">Rusak Ringan</span></div>
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full" style="background:#ef4444"></span><span class="text-xs text-gray-600">Rusak Berat</span></div>
                </div>
            </div>
        </div>

    </div>

</div>

<script>
function stripmapForm() {

    // Inisialisasi data form (mendukung old input jika ada, atau data edit, atau 3 baris kosong)
    const isEdit = <?= $isEdit ? 'true' : 'false' ?>;
    let initialRows = [];

    <?php if ($oldInput): ?>
        initialRows = <?= json_encode(array_values($oldInput)) ?>;
        // Transform the format slightly for JS
        initialRows = initialRows.map((r, idx) => ({
            id: Date.now() + idx,
            staAwal: r.sta_awal,
            staAkhir: r.sta_akhir,
            panjang: 0,
            baik: parseFloat(r.baik) || 0,
            sedang: parseFloat(r.sedang) || 0,
            rusakRingan: parseFloat(r.rusak_ringan) || 0,
            rusakBerat: parseFloat(r.rusak_berat) || 0,
            error: '',
            isValid: false
        }));
    <?php elseif ($isEdit): ?>
        initialRows = [{
            id: Date.now(),
            staAwal: '<?= meter_to_sta($stripmap['sta_awal']) ?>',
            staAkhir: '<?= meter_to_sta($stripmap['sta_akhir']) ?>',
            panjang: <?= $stripmap['panjang'] ?>,
            baik: <?= $stripmap['baik'] ?>,
            sedang: <?= $stripmap['sedang'] ?>,
            rusakRingan: <?= $stripmap['rusak_ringan'] ?>,
            rusakBerat: <?= $stripmap['rusak_berat'] ?>,
            error: '',
            isValid: true
        }];
    <?php elseif (isset($prefillData)): ?>
        // Pre-fill untuk fitur "Sisipkan Segmen"
        initialRows = [{
            id: Date.now(),
            staAwal: '<?= $prefillData['sta_awal'] ?? '' ?>',
            staAkhir: '<?= $prefillData['sta_akhir'] ?? '' ?>',
            panjang: 0,
            baik: 0,
            sedang: 0,
            rusakRingan: 0,
            rusakBerat: 0,
            error: '',
            isValid: false
        }];
    <?php else: ?>
        // Default 3 baris kosong
        for(let i=0; i<3; i++) {
            initialRows.push({
                id: Date.now() + i,
                staAwal: '',
                staAkhir: '',
                panjang: 0,
                baik: 0,
                sedang: 0,
                rusakRingan: 0,
                rusakBerat: 0,
                error: '',
                isValid: false
            });
        }
    <?php endif; ?>

    return {
        rows: initialRows,

        init() {
            // Kalkulasi ulang semua baris saat inisialisasi
            this.rows.forEach(row => this.calculateRow(row));
        },

        addRow() {
            // Salin STA Akhir baris terakhir sebagai STA Awal baris baru (Fitur kenyamanan)
            let lastStaAkhir = '';
            if (this.rows.length > 0) {
                lastStaAkhir = this.rows[this.rows.length - 1].staAkhir;
            }

            this.rows.push({
                id: Date.now(),
                staAwal: lastStaAkhir,
                staAkhir: '',
                panjang: 0,
                baik: 0,
                sedang: 0,
                rusakRingan: 0,
                rusakBerat: 0,
                error: '',
                isValid: false
            });
        },

        removeRow(index) {
            this.rows.splice(index, 1);
        },

        staToMeter(sta) {
            if(!sta) return 0;
            sta = sta.trim();
            if (sta.includes('+')) {
                const parts = sta.split('+');
                return parseFloat(parts[0]) * 1000 + parseFloat(parts[1] || 0);
            }
            return parseFloat(sta) || 0;
        },

        calculateRow(row) {
            row.error = '';
            row.isValid = false;

            // Konversi nilai input ke float agar aman untuk kalkulasi
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

                    // Validasi kondisi
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
                if(row.staAwal || row.staAkhir || totalKondisi > 0) {
                    row.error = 'STA harus diisi lengkap.';
                }
            }
        },

        get formErrors() {
            let errors = [];
            let validSegments = [];

            this.rows.forEach((row, idx) => {
                // Abaikan baris kosong sepenuhnya (kecuali form isEdit)
                const isTotallyEmpty = !row.staAwal && !row.staAkhir && !row.baik && !row.sedang && !row.rusakRingan && !row.rusakBerat;

                if (!isTotallyEmpty) {
                    if (row.error) {
                        errors.push(`Baris ${idx + 1}: ${row.error}`);
                    } else if (!row.isValid) {
                        errors.push(`Baris ${idx + 1}: Data belum valid.`);
                    } else {
                        validSegments.push({
                            index: idx + 1,
                            awal: this.staToMeter(row.staAwal),
                            akhir: this.staToMeter(row.staAkhir),
                            staAwalStr: row.staAwal,
                            staAkhirStr: row.staAkhir
                        });
                    }
                }
            });

            // Deteksi Tumpang Tindih (Overlapping) Segmen
            validSegments.sort((a, b) => a.awal - b.awal);
            for (let i = 1; i < validSegments.length; i++) {
                if (validSegments[i].awal < validSegments[i-1].akhir) {
                    errors.push(`Tumpang tindih terdeteksi antara Baris ${validSegments[i-1].index} (${validSegments[i-1].staAwalStr} s/d ${validSegments[i-1].staAkhirStr}) dan Baris ${validSegments[i].index} (${validSegments[i].staAwalStr} s/d ${validSegments[i].staAkhirStr}).`);
                }
            }

            return errors;
        },

        get activeRows() {
            return this.rows.filter(row => row.staAwal || row.staAkhir || row.baik || row.sedang || row.rusakRingan || row.rusakBerat);
        },

        get validRows() {
            return this.rows.filter(row => row.isValid);
        },

        get isFormValid() {
            if (this.formErrors.length > 0) return false;
            const active = this.activeRows;
            return active.length > 0 && active.every(row => row.isValid);
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
                { key: 'baik', label: 'Baik', value: baik, percent: (baik / total) * 100, color: '#10b981' },
                { key: 'sedang', label: 'Sedang', value: sedang, percent: (sedang / total) * 100, color: '#eab308' },
                { key: 'rusak_ringan', label: 'Rusak Ringan', value: rRingan, percent: (rRingan / total) * 100, color: '#f97316' },
                { key: 'rusak_berat', label: 'Rusak Berat', value: rBerat, percent: (rBerat / total) * 100, color: '#ef4444' }
            ];

            const active = conditions.filter(c => c.value > 0);

            // Hitung posisi tengah kumulatif untuk label pointer
            let cumulative = 0;
            return active.map((c, idx) => {
                const nextColor = idx < active.length - 1 ? active[idx + 1].color : c.color;
                c.bgStyle = `background: linear-gradient(to right, ${c.color} 60%, ${nextColor} 100%)`;
                c.midPct = cumulative + (c.percent / 2);
                cumulative += c.percent;
                return c;
            });
        },

        validateForm(event) {
            // Hapus baris yang kosong sebelum submit
            if (!this.isEdit) {
                this.rows = this.rows.filter(row => row.staAwal || row.staAkhir || row.baik || row.sedang || row.rusakRingan || row.rusakBerat);
            }

            if (!this.isFormValid) {
                event.preventDefault();
                showAlert('Terdapat data yang tidak valid. Periksa pesan error!', 'warning', 'Validasi Gagal');
                return false;
            }
            return true;
        }
    }
}
</script>
