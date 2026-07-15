<!-- ============================================================ -->
<!-- Form Import Data Ruas, Kondisi & Perkerasan (Excel / CSV)     -->
<!-- ============================================================ -->

<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header & Breadcrumb -->
    <div class="flex items-center gap-4">
        <a href="<?= base_url('ruas') ?>" 
           class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-white border border-gray-200 hover:bg-gray-50 transition-colors shadow-sm">
            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Import Data Rekapitulasi Excel / CSV</h1>
            <p class="mt-1 text-sm text-gray-500">Unggah file Excel Rekapitulasi Kondisi & Perkerasan Jalan untuk menyimpan/memperbarui data secara otomatis.</p>
        </div>
    </div>

    <!-- Upload Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden p-8"
         x-data="{ 
            isDragging: false, 
            selectedFile: null,
            handleFileSelect(e) {
                const files = e.target.files || e.dataTransfer.files;
                if (files.length > 0) {
                    this.selectedFile = files[0];
                }
            }
         }">
        
        <form action="<?= base_url('ruas/import') ?>" method="POST" enctype="multipart/form-data" class="space-y-6">

            <!-- Drag & Drop Zone -->
            <div class="relative border-2 border-dashed rounded-2xl p-8 text-center transition-all duration-200"
                 :class="isDragging ? 'border-blue-500 bg-blue-50/50' : (selectedFile ? 'border-emerald-400 bg-emerald-50/30' : 'border-gray-300 hover:border-gray-400 bg-gray-50/50')"
                 @dragover.prevent="isDragging = true"
                 @dragleave.prevent="isDragging = false"
                 @drop.prevent="isDragging = false; handleFileSelect($event)">
                
                <input type="file" 
                       name="file_excel" 
                       id="file_excel" 
                       accept=".xlsx, .xls, .csv"
                       required
                       class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                       @change="handleFileSelect($event)">

                <div class="space-y-4 pointer-events-none">
                    <!-- Icon State -->
                    <div class="w-16 h-16 mx-auto rounded-full flex items-center justify-center transition-transform duration-200"
                         :class="selectedFile ? 'bg-emerald-100 text-emerald-600 scale-110' : 'bg-blue-100 text-blue-600'">
                        <template x-if="!selectedFile">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                        </template>
                        <template x-if="selectedFile">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </template>
                    </div>

                    <!-- Text Instructions -->
                    <template x-if="!selectedFile">
                        <div>
                            <p class="text-base font-semibold text-gray-800">
                                Drag & drop file Excel di sini, atau <span class="text-blue-600 underline">Pilih File</span>
                            </p>
                            <p class="text-xs text-gray-500 mt-1">Mendukung format: <span class="font-mono font-medium text-gray-700">.xlsx</span>, <span class="font-mono font-medium text-gray-700">.xls</span>, <span class="font-mono font-medium text-gray-700">.csv</span> (Maks. 10MB)</p>
                        </div>
                    </template>

                    <!-- Selected File Preview -->
                    <template x-if="selectedFile">
                        <div class="space-y-1">
                            <p class="text-base font-bold text-emerald-800" x-text="selectedFile.name"></p>
                            <p class="text-xs text-emerald-600" x-text="Math.round(selectedFile.size / 1024) + ' KB'"></p>
                            <p class="text-xs font-medium text-gray-500 pt-1">Klik atau drag file lain jika ingin mengganti.</p>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Guide Box -->
            <div class="bg-blue-50/60 border border-blue-200/80 rounded-xl p-5 space-y-3">
                <div class="flex items-center gap-2 text-blue-900 font-bold text-sm">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Struktur Kolom File Excel Rekapitulasi yang Didukung:
                </div>
                <ul class="text-xs text-blue-800 space-y-1.5 list-disc list-inside">
                    <li><span class="font-semibold">Nama Koridor:</span> Baris berisi kata <code class="bg-white px-1.5 py-0.5 rounded border font-mono">KORIDOR 1</code>, <code class="bg-white px-1.5 py-0.5 rounded border font-mono">KORIDOR 2</code>, dsb. otomatis terdeteksi sebagai grup koridor.</li>
                    <li><span class="font-semibold">Kode & Nama Ruas:</span> Kolom <code class="bg-white px-1.5 py-0.5 rounded border font-mono">NMR RUAS</code> dan <code class="bg-white px-1.5 py-0.5 rounded border font-mono">NAMA RUAS</code>.</li>
                    <li><span class="font-semibold">Panjang SK:</span> Kolom <code class="bg-white px-1.5 py-0.5 rounded border font-mono">PANJANG SK (Km)</code> (dalam satuan Km, otomatis dikonversi ke meter).</li>
                    <li><span class="font-semibold">Tipe Perkerasan:</span> Kolom <code class="bg-white px-1.5 py-0.5 rounded border font-mono">RIGID</code>, <code class="bg-white px-1.5 py-0.5 rounded border font-mono">ASPAL / LAPEN</code>, <code class="bg-white px-1.5 py-0.5 rounded border font-mono">TELFORD / KERIKIL</code>, <code class="bg-white px-1.5 py-0.5 rounded border font-mono">TANAH</code>.</li>
                    <li><span class="font-semibold">Tipe Kondisi:</span> Kolom <code class="bg-white px-1.5 py-0.5 rounded border font-mono">BAIK</code>, <code class="bg-white px-1.5 py-0.5 rounded border font-mono">SEDANG</code>, <code class="bg-white px-1.5 py-0.5 rounded border font-mono">RUSAK RINGAN</code>, <code class="bg-white px-1.5 py-0.5 rounded border font-mono">RUSAK BERAT</code>.</li>
                </ul>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="<?= base_url('ruas') ?>" 
                   class="px-5 py-2.5 bg-white text-gray-700 text-sm font-semibold rounded-xl border border-gray-300 hover:bg-gray-50 transition-colors">
                    Batal
                </a>
                <button type="submit" 
                        :disabled="!selectedFile"
                        :class="selectedFile ? 'bg-blue-600 hover:bg-blue-700 shadow-md' : 'bg-gray-300 cursor-not-allowed'"
                        class="inline-flex items-center gap-2 px-6 py-2.5 text-white text-sm font-semibold rounded-xl transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Proses Import Excel
                </button>
            </div>

        </form>

    </div>

</div>
