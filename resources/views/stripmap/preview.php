<!-- ============================================================ -->
<!-- Preview Strip Map (Full Page) -->
<!-- ============================================================ -->

<div class="space-y-6">

    <!-- Header (Excluding Export/Print Area) -->
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
                    (<span class="font-mono"><?= e($ruas['kode_ruas']) ?></span>)
                </p>
            </div>
        </div>
        
        <!-- Action Buttons with Dropdown -->
        <div class="flex items-center gap-2" x-data="{ openExport: false }">
            <a href="<?= base_url('stripmap/' . $ruas['id']) ?>"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-white text-gray-700 text-sm font-medium rounded-xl border border-gray-300 hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 17l-5-5m0 0l5-5m-5 5h12"/>
                </svg>
                Kembali ke Data
            </a>
            
            <?php if (!empty($stripmaps)): ?>
            <div class="relative">
                <button @click="openExport = !openExport" @click.away="openExport = false"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 text-white text-sm font-medium rounded-xl hover:bg-emerald-700 transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Export / Cetak
                    <svg class="w-3.5 h-3.5 ml-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                
                <!-- Dropdown Menu -->
                <div x-show="openExport"
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="transform opacity-0 scale-95"
                     x-transition:enter-end="transform opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="transform opacity-100 scale-100"
                     x-transition:leave-end="transform opacity-0 scale-95"
                     class="absolute right-0 mt-2 w-48 rounded-xl bg-white border border-gray-200 shadow-lg py-1 z-50 overflow-hidden"
                     style="display: none;">
                    <button @click="exportDocument('pdf'); openExport = false" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        Dokumen PDF (.pdf)
                    </button>
                    <button @click="exportDocument('jpeg'); openExport = false" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Gambar JPEG (.jpg)
                    </button>
                    <button @click="exportDocument('png'); openExport = false" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Gambar PNG (.png)
                    </button>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Capture Area (Hanya area ini yang diexport) -->
    <?php if (!empty($stripmaps)): ?>
    <div id="capture-area" class="bg-white rounded-2xl border border-gray-200 shadow-sm p-8 space-y-6" style="background-color: #ffffff; border-color: #e5e7eb;">
        <!-- Data Umum Ruas Jalan -->
        <div class="border border-gray-200 rounded-xl overflow-hidden">
            <div class="px-5 py-3 bg-gray-50/50 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900">Data Umum Ruas Jalan</h3>
            </div>
            <div class="border-t border-gray-100">
                <table class="w-full text-sm text-left">
                    <tbody class="divide-y divide-gray-100">
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-5 py-2.5 font-semibold text-gray-500 w-1/4">Nama Ruas</td>
                            <td class="px-5 py-2.5 text-gray-900 font-bold"><?= e($ruas['nama_ruas']) ?></td>
                        </tr>
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-5 py-2.5 font-semibold text-gray-500 w-1/4">Nomor Ruas</td>
                            <td class="px-5 py-2.5 text-gray-800 font-semibold font-mono"><?= e($ruas['kode_ruas']) ?></td>
                        </tr>
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-5 py-2.5 font-semibold text-gray-500 w-1/4">Panjang Ruas</td>
                            <td class="px-5 py-2.5 text-gray-900 font-bold"><?= format_number($ruas['panjang']) ?> m</td>
                        </tr>
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-5 py-2.5 font-semibold text-gray-500 w-1/4">Koridor</td>
                            <td class="px-5 py-2.5 text-gray-900 font-semibold"><?= e($ruas['koridor'] ?? '-') ?></td>
                        </tr>
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-5 py-2.5 font-semibold text-gray-500 w-1/4">Kabupaten / Kota</td>
                            <td class="px-5 py-2.5 text-gray-900 font-semibold"><?= e($ruas['kabupaten_kota'] ?? '-') ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Visual Strip Map -->
        <?php view('stripmap._visual', ['stripmaps' => $stripmaps, 'summary' => $summary, 'ruas' => $ruas]); ?>
    </div>
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

</div>

<!-- html2canvas & jsPDF CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<script>
/**
 * Salin computed style elemen ke inline style agar html2canvas bisa membacanya.
 * Ini mengatasi masalah Tailwind CSS yang tidak terbaca oleh html2canvas.
 */
function cloneComputedStyles(sourceEl, targetEl) {
    const computed = window.getComputedStyle(sourceEl);
    for (let prop of computed) {
        try {
            targetEl.style[prop] = computed.getPropertyValue(prop);
        } catch(e) { /* skip read-only props */ }
    }
    for (let i = 0; i < sourceEl.children.length; i++) {
        cloneComputedStyles(sourceEl.children[i], targetEl.children[i]);
    }
}

/**
 * Konversi semua elemen <canvas> di dalam container menjadi <img>
 * sehingga html2canvas bisa menangkapnya dengan benar.
 * Mengembalikan fungsi restore untuk mengembalikan ke keadaan semula.
 */
function convertCanvasesToImages(container) {
    const canvases = Array.from(container.querySelectorAll('canvas'));
    const replacements = [];

    canvases.forEach(canvas => {
        const dataUrl = canvas.toDataURL('image/png');
        const img = document.createElement('img');
        img.src = dataUrl;
        img.width  = canvas.offsetWidth;
        img.height = canvas.offsetHeight;
        img.style.cssText = `
            display: block;
            width: ${canvas.offsetWidth}px;
            height: ${canvas.offsetHeight}px;
        `;
        canvas.parentNode.insertBefore(img, canvas);
        canvas.style.display = 'none';
        replacements.push({ canvas, img });
    });

    // Kembalikan fungsi restore
    return function restore() {
        replacements.forEach(({ canvas, img }) => {
            canvas.style.display = '';
            img.remove();
        });
    };
}

function exportDocument(type) {
    Swal.fire({
        title: 'Mempersiapkan dokumen...',
        text: 'Mohon tunggu sebentar.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    const element = document.getElementById('capture-area');

    // ─── LANGKAH 1: Konversi semua Chart.js <canvas> → <img> ───────────────────
    // html2canvas tidak bisa membaca isi canvas yang digambar library lain (Chart.js).
    // Solusi: tangkap isi canvas sebagai dataURL, buat <img>, lalu sembunyikan canvas asli.
    const restoreCanvases = convertCanvasesToImages(element);

    // ─── LANGKAH 2: Beri jeda singkat agar img src ter-load ─────────────────────
    setTimeout(() => {
        const fileName = 'StripMap_<?= e($ruas['kode_ruas']) ?>_' + new Date().toISOString().slice(0, 10);

        html2canvas(element, {
            scale: 3,                           // 3x resolusi → gambar tajam
            useCORS: true,
            allowTaint: false,
            backgroundColor: '#ffffff',
            logging: false,
            // Gunakan lebar elemen yang sesungguhnya agar layout tidak berubah
            width: element.scrollWidth,
            height: element.scrollHeight,
            windowWidth: document.documentElement.scrollWidth,
            windowHeight: document.documentElement.scrollHeight,
            // Scroll ke atas agar html2canvas menangkap dari posisi 0
            scrollX: -window.scrollX,
            scrollY: -window.scrollY,
            imageTimeout: 15000,
            onclone: (clonedDoc) => {
                const clonedEl = clonedDoc.getElementById('capture-area');
                if (!clonedEl) return;

                clonedEl.style.borderRadius = '0';
                clonedEl.style.overflow    = 'visible';

                // ── Fix: Flex containers (Tailwind tidak terbaca html2canvas) ───
                clonedEl.querySelectorAll('.flex').forEach(el => {
                    el.style.display = 'flex';
                });
                clonedEl.querySelectorAll('.flex-col').forEach(el => {
                    el.style.flexDirection = 'column';
                });
                clonedEl.querySelectorAll('.flex-wrap').forEach(el => {
                    el.style.flexWrap = 'wrap';
                });
                clonedEl.querySelectorAll('.items-center').forEach(el => {
                    el.style.alignItems = 'center';
                });
                clonedEl.querySelectorAll('.items-start').forEach(el => {
                    el.style.alignItems = 'flex-start';
                });
                clonedEl.querySelectorAll('.justify-between').forEach(el => {
                    el.style.justifyContent = 'space-between';
                });
                clonedEl.querySelectorAll('.justify-center').forEach(el => {
                    el.style.justifyContent = 'center';
                });

                // ── Fix: gap utilities ────────────────────────────────────────
                const gapMap = {
                    'gap-1':   '4px',  'gap-1\\.5': '6px', 'gap-2':   '8px',
                    'gap-3':  '12px',  'gap-4':     '16px', 'gap-5':  '20px',
                    'gap-6':  '24px',  'gap-8':     '32px',
                    'gap-x-3': null,   'gap-x-4':   null,  'gap-y-1\\.5': null,
                };
                // Query via classList check (lebih aman untuk class dengan titik)
                clonedEl.querySelectorAll('[class]').forEach(el => {
                    const cls = el.className || '';
                    // gap-X
                    const gapMatch = cls.match(/\bgap-(\d+(?:\.\d+)?)\b/);
                    if (gapMatch) {
                        const val = parseFloat(gapMatch[1]) * 4;
                        el.style.gap = val + 'px';
                    }
                    // gap-x-X
                    const gapXMatch = cls.match(/\bgap-x-(\d+(?:\.\d+)?)\b/);
                    if (gapXMatch) {
                        const val = parseFloat(gapXMatch[1]) * 4;
                        el.style.columnGap = val + 'px';
                    }
                    // gap-y-X
                    const gapYMatch = cls.match(/\bgap-y-(\d+(?:\.\d+)?)\b/);
                    if (gapYMatch) {
                        const val = parseFloat(gapYMatch[1]) * 4;
                        el.style.rowGap = val + 'px';
                    }
                });

                // ── Fix: DOT/CIRCLE (penyebab bulatan tidak center dengan text) ─
                // Tailwind: w-2.5 h-2.5 rounded-full → 10px × 10px, border-radius 50%
                // html2canvas tidak bisa membaca class ini, jadi perlu inline style eksplisit
                clonedEl.querySelectorAll('span.rounded-full').forEach(dot => {
                    dot.style.display    = 'inline-block';
                    dot.style.flexShrink = '0';
                    dot.style.alignSelf  = 'center';   // ← Ini kunci: sejajar vertikal dengan text
                    dot.style.borderRadius = '50%';

                    // Tentukan ukuran dari class Tailwind yang ada
                    const cls = dot.className || '';
                    if (cls.includes('w-2.5') || cls.includes('h-2.5')) {
                        dot.style.width     = '10px';
                        dot.style.height    = '10px';
                        dot.style.minWidth  = '10px';
                        dot.style.minHeight = '10px';
                    } else if (cls.includes('w-3') || cls.includes('h-3')) {
                        dot.style.width     = '12px';
                        dot.style.height    = '12px';
                        dot.style.minWidth  = '12px';
                        dot.style.minHeight = '12px';
                    } else {
                        // Fallback: baca dari DOM asli
                        const origDot = document.querySelector(
                            `span.rounded-full[style="${dot.getAttribute('style')}"]`
                        );
                        if (origDot) {
                            const w = origDot.offsetWidth;
                            const h = origDot.offsetHeight;
                            if (w > 0) { dot.style.width = w + 'px'; dot.style.minWidth = w + 'px'; }
                            if (h > 0) { dot.style.height = h + 'px'; dot.style.minHeight = h + 'px'; }
                        }
                    }

                    // ── Geser dot 1px ke bawah di export ─────────────────────
                    // scale:3 → 1px CSS = 3px di gambar output
                    // Ini mengkompensasi offset 2-3px yang muncul di hasil cetak
                    dot.style.position = 'relative';
                    dot.style.top      = '6px';
                });
            }
        }).then(canvas => {
            // ─── Restore canvas Chart.js ─────────────────────────────────────────
            restoreCanvases();

            const mimeType = type === 'jpeg' ? 'image/jpeg' : 'image/png';
            const quality  = type === 'jpeg' ? 0.95 : 1.0;
            const imgData  = canvas.toDataURL(mimeType, quality);

            if (type === 'pdf') {
                const { jsPDF } = window.jspdf;

                // Dimensi PDF disesuaikan dengan rasio gambar (bukan paksa A4 saja)
                // Agar isi tidak terpotong dan sama persis dengan tampilan web
                const PX_PER_MM = 3.7795275591; // 1mm = 3.78px pada 96dpi
                const pdfW_mm   = canvas.width  / (3 * PX_PER_MM); // kompensasi scale 3x
                const pdfH_mm   = canvas.height / (3 * PX_PER_MM);
                const orientation = pdfW_mm > pdfH_mm ? 'l' : 'p';

                const pdf = new jsPDF({
                    orientation: orientation,
                    unit: 'mm',
                    format: [pdfW_mm, pdfH_mm]
                });

                pdf.addImage(imgData, 'PNG', 0, 0, pdfW_mm, pdfH_mm, '', 'FAST');
                pdf.save(fileName + '.pdf');

                Swal.fire({
                    icon: 'success',
                    title: 'Export Berhasil!',
                    text: 'Dokumen PDF telah diunduh.',
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                const link = document.createElement('a');
                link.href = imgData;
                link.download = fileName + '.' + (type === 'jpeg' ? 'jpg' : 'png');
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

                Swal.fire({
                    icon: 'success',
                    title: 'Export Berhasil!',
                    text: 'Gambar telah diunduh.',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        }).catch(err => {
            restoreCanvases();
            console.error('html2canvas error:', err);
            Swal.fire({
                icon: 'error',
                title: 'Export Gagal',
                text: 'Terjadi kesalahan saat memproses ekspor. Silakan coba lagi.'
            });
        });
    }, 300); // Jeda 300ms untuk memastikan img sudah ter-render
}
</script>
