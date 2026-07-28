<!-- ============================================================ -->
<!-- Halaman Daftar Strip Map & Perkerasan per Ruas               -->
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
                <h1 class="text-2xl font-bold text-gray-900">Strip Map & Perkerasan Ruas Jalan</h1>
                <p class="text-sm text-gray-500">Manajemen segmen kondisi dan jenis perkerasan jalan.</p>
            </div>
        </div>
        <div class="flex gap-2">
            <?php if (!empty($stripmaps) || !empty($perkerasans)): ?>
            <a href="<?= base_url('stripmap/preview/' . $ruas['id']) ?>"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-xl hover:bg-indigo-700 transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                Preview Mode
            </a>
            <?php endif; ?>
            <label class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 text-white text-sm font-medium rounded-xl hover:bg-emerald-700 transition-colors shadow-sm cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                </svg>
                Import KML / KMZ
                <input type="file" accept=".kml,.kmz" class="hidden" onchange="handleDirectKmlImport(event, <?= $ruas['id'] ?>)">
            </label>
            <a href="<?= base_url('stripmap/create/' . $ruas['id']) ?>"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-xl hover:bg-blue-700 transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Segmen Data
            </a>
        </div>
    </div>

    <!-- Hidden Form untuk Import KML Langsung -->
    <form id="direct-kml-form" method="POST" action="<?= base_url('stripmap/import-kml/' . $ruas['id']) ?>" class="hidden">
        <input type="hidden" name="koordinat_json" id="kml_koordinat_json">
        <input type="hidden" name="lat_awal" id="kml_lat_awal">
        <input type="hidden" name="lng_awal" id="kml_lng_awal">
        <input type="hidden" name="lat_akhir" id="kml_lat_akhir">
        <input type="hidden" name="lng_akhir" id="kml_lng_akhir">
    </form>

    <!-- Data Umum Ruas Jalan Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-sm font-semibold text-gray-900">Data Umum Ruas Jalan</h2>
        </div>
        <div class="border-t border-gray-100">
            <table class="w-full text-sm text-left">
                <tbody class="divide-y divide-gray-100">
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-3 font-semibold text-gray-500 w-1/4">Nama Ruas</td>
                        <td class="px-6 py-3 text-gray-900 font-bold"><?= e($ruas['nama_ruas']) ?></td>
                    </tr>
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-3 font-semibold text-gray-500 w-1/4">Nomor Ruas</td>
                        <td class="px-6 py-3 text-gray-800 font-semibold font-mono"><?= e($ruas['kode_ruas']) ?></td>
                    </tr>
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-3 font-semibold text-gray-500 w-1/4">Panjang Ruas</td>
                        <td class="px-6 py-3 text-gray-900 font-bold"><?= format_number($ruas['panjang']) ?> m</td>
                    </tr>
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-3 font-semibold text-gray-500 w-1/4">Koridor</td>
                        <td class="px-6 py-3 text-gray-900 font-semibold"><?= e($ruas['koridor'] ?? '-') ?></td>
                    </tr>
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-3 font-semibold text-gray-500 w-1/4">Kabupaten / Kota</td>
                        <td class="px-6 py-3 text-gray-900 font-semibold"><?= e($ruas['kabupaten_kota'] ?? '-') ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Strip Map & Perkerasan Visual Preview Partial -->
    <?php if (!empty($stripmaps) || !empty($perkerasans)): ?>
        <?php view('stripmap._visual', [
            'stripmaps'         => $stripmaps,
            'summary'           => $summary,
            'ruas'              => $ruas,
            'perkerasans'       => $perkerasans ?? [],
            'summaryPerkerasan' => $summaryPerkerasan ?? []
        ]); ?>
    <?php endif; ?>

    <!-- Peta Lokasi Ruas (tampil jika ada koordinat awal/akhir ATAU ada rute KML/KMZ) -->
    <?php
    $hasMapData = (!empty($ruas['lat_awal']) && !empty($ruas['lng_awal']))
               || (!empty($ruas['koordinat_json']) && $ruas['koordinat_json'] !== '[]' && $ruas['koordinat_json'] !== 'null');
    ?>
    <?php if ($hasMapData): ?>
    <div x-data="{ isOpen: true }" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between cursor-pointer select-none bg-gray-50/60" @click="isOpen = !isOpen">
            <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                <span class="w-2 h-4 rounded bg-teal-600 inline-block"></span>
                Peta Lokasi Ruas Jalan
            </h2>
            <div class="flex items-center gap-3">
                <a href="https://www.google.com/maps?q=&layer=c&cbll=<?= e($ruas['lat_awal']) ?>,<?= e($ruas['lng_awal']) ?>"
                   target="_blank" rel="noopener"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors"
                   @click.stop>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.069A1 1 0 0121 8.82V18a1 1 0 01-1.447.894L15 17M3 8a2 2 0 012-2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"/></svg>
                    Street View
                </a>
                <?php if (!empty($ruas['lat_akhir']) && !empty($ruas['lng_akhir'])): ?>
                <a href="https://www.google.com/maps/dir/<?= e($ruas['lat_awal']) ?>,<?= e($ruas['lng_awal']) ?>/<?= e($ruas['lat_akhir']) ?>,<?= e($ruas['lng_akhir']) ?>"
                   target="_blank" rel="noopener"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-teal-700 bg-teal-50 rounded-lg hover:bg-teal-100 transition-colors"
                   @click.stop>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                    Lihat di Google Maps
                </a>
                <?php endif; ?>
                <button class="text-gray-500 hover:text-gray-700 focus:outline-none transition-transform duration-200" :class="isOpen ? 'rotate-90' : 'rotate-0'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>
        <div x-show="isOpen" x-collapse>
            <?php
            // Hitung total dan urutkan segmen secara linier (sepanjang rute STA 0 s/d STA Akhir)
            $totBaik = $totSedang = $totRR = $totRB = 0.0;
            $linearPieces = [];

            // Sort stripmaps berdasarkan sta_awal ascending
            $sortedStripmaps = $stripmaps ?? [];
            usort($sortedStripmaps, fn($a, $b) => (float)$a['sta_awal'] <=> (float)$b['sta_awal']);

            foreach ($sortedStripmaps as $s) {
                $b  = (float) $s['baik'];
                $sd = (float) $s['sedang'];
                $rr = (float) $s['rusak_ringan'];
                $rb = (float) $s['rusak_berat'];

                $totBaik   += $b;
                $totSedang += $sd;
                $totRR     += $rr;
                $totRB     += $rb;

                $sa = (float)$s['sta_awal'];
                $curr = $sa;

                if ($b > 0) {
                    $linearPieces[] = ['sta_awal' => $curr, 'sta_akhir' => $curr + $b, 'panjang' => $b, 'lbl' => 'Baik', 'bg' => 'bg-emerald-500', 'hex' => '#10b981'];
                    $curr += $b;
                }
                if ($sd > 0) {
                    $linearPieces[] = ['sta_awal' => $curr, 'sta_akhir' => $curr + $sd, 'panjang' => $sd, 'lbl' => 'Sedang', 'bg' => 'bg-yellow-500', 'hex' => '#eab308'];
                    $curr += $sd;
                }
                if ($rr > 0) {
                    $linearPieces[] = ['sta_awal' => $curr, 'sta_akhir' => $curr + $rr, 'panjang' => $rr, 'lbl' => 'Rusak Ringan', 'bg' => 'bg-orange-500', 'hex' => '#f97316'];
                    $curr += $rr;
                }
                if ($rb > 0) {
                    $linearPieces[] = ['sta_awal' => $curr, 'sta_akhir' => $curr + $rb, 'panjang' => $rb, 'lbl' => 'Rusak Berat', 'bg' => 'bg-red-500', 'hex' => '#ef4444'];
                    $curr += $rb;
                }
            }

            $totKond = $totBaik + $totSedang + $totRR + $totRB;
            $pct = fn($v) => $totKond > 0 ? round($v / $totKond * 100, 1) : 0;
            $summaryRows = [
                ['Baik',         $totBaik,   '#10b981', 'bg-emerald-500'],
                ['Sedang',       $totSedang, '#eab308', 'bg-yellow-500'],
                ['Rusak Ringan', $totRR,     '#f97316', 'bg-orange-500'],
                ['Rusak Berat',  $totRB,     '#ef4444', 'bg-red-500'],
            ];

            $formatSta = function($m) {
                $km = floor($m / 1000);
                $r  = round($m % 1000);
                return 'STA ' . $km . '+' . str_pad((string)$r, 3, '0', STR_PAD_LEFT);
            };
            ?>
            <?php if ($totKond > 0): ?>
            <div class="px-6 pt-5 pb-4 border-b border-gray-100">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-gray-600 uppercase tracking-wider flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                        Kondisi Linier Sepanjang Rute Jalan
                    </span>
                    <span class="text-xs text-gray-500 font-medium">Total: <?= format_number($totKond) ?> m</span>
                </div>
                <!-- Bar Linier Berurutan Sesuai Rute Asli (STA 0 s/d STA Akhir) -->
                <div class="flex w-full h-4 rounded-full overflow-hidden bg-gray-100 shadow-inner">
                    <?php foreach ($linearPieces as $piece): ?>
                        <?php $wPct = ($piece['panjang'] / $totKond) * 100; ?>
                        <div class="<?= $piece['bg'] ?> h-full transition-all hover:brightness-110 cursor-pointer"
                             style="width:<?= $wPct ?>%"
                             title="STA <?= format_number($piece['sta_awal']) ?> - <?= format_number($piece['sta_akhir']) ?> m (<?= $piece['lbl'] ?>: <?= format_number($piece['panjang']) ?> m)"></div>
                    <?php endforeach; ?>
                </div>
                <!-- Ringkasan Statistik Persentase -->
                <div class="flex flex-wrap gap-x-5 gap-y-1.5 mt-3">
                    <?php foreach ($summaryRows as [$lbl, $val, $hex, $bg]): ?>
                    <div class="flex items-center gap-1.5 text-xs">
                        <span class="w-3 h-3 rounded-sm inline-block" style="background:<?= $hex ?>"></span>
                        <span class="text-gray-600"><?= $lbl ?></span>
                        <span class="font-semibold text-gray-900"><?= $pct($val) ?>%</span>
                        <span class="text-gray-400">(<?= format_number($val) ?> m)</span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            <div id="ruas-detail-map" class="w-full" style="height:380px;"></div>
        </div>
    </div>

    <!-- Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
    (function () {
        const latAwal  = <?= !empty($ruas['lat_awal']) ? (float) $ruas['lat_awal'] : 'null' ?>;
        const lngAwal  = <?= !empty($ruas['lng_awal']) ? (float) $ruas['lng_awal'] : 'null' ?>;
        const latAkhir = <?= !empty($ruas['lat_akhir']) ? (float) $ruas['lat_akhir'] : 'null' ?>;
        const lngAkhir = <?= !empty($ruas['lng_akhir']) ? (float) $ruas['lng_akhir'] : 'null' ?>;
        const panjangRuas = <?= (float) $ruas['panjang'] ?>;

        // Polyline rute asli hasil impor KML/KMZ (disimpan sebagai [[lng,lat], ...])
        <?php
            $safeRouteJson = '[]';
            if (!empty($ruas['koordinat_json'])) {
                $decodedRoute = json_decode($ruas['koordinat_json'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decodedRoute)) {
                    $safeRouteJson = $ruas['koordinat_json'];
                }
            }
        ?>
        const rawRoute = <?= $safeRouteJson ?>;

        const segments = <?= json_encode(array_map(function($s) {
            return [
                'sta_awal'    => (float) $s['sta_awal'],
                'sta_akhir'   => (float) $s['sta_akhir'],
                'panjang'     => (float) $s['panjang'],
                'baik'        => (float) $s['baik'],
                'sedang'      => (float) $s['sedang'],
                'rusak_ringan'=> (float) $s['rusak_ringan'],
                'rusak_berat' => (float) $s['rusak_berat'],
            ];
        }, $stripmaps ?? [])) ?>;

        const condColors = { baik: '#10b981', sedang: '#eab308', rusak_ringan: '#f97316', rusak_berat: '#ef4444' };
        const condLabels = { baik: 'Baik', sedang: 'Sedang', rusak_ringan: 'Rusak Ringan', rusak_berat: 'Rusak Berat' };
        // Urutan tetap penggambaran kondisi di dalam tiap segmen
        const condOrder = ['baik', 'sedang', 'rusak_ringan', 'rusak_berat'];

        const staFmt = m => {
            const km = Math.floor(m / 1000);
            const r  = Math.round(m % 1000);
            return km + '+' + String(r).padStart(3, '0');
        };

        // Pecah satu segmen menjadi potongan kondisi berurutan (proporsi terhadap panjang segmen)
        function condPieces(seg) {
            const sum = seg.baik + seg.sedang + seg.rusak_ringan + seg.rusak_berat;
            const denom = sum > 0 ? sum : ((seg.sta_akhir - seg.sta_awal) || 1);
            const pieces = [];
            let acc = 0;
            condOrder.forEach(key => {
                const len = seg[key];
                if (len > 0) {
                    pieces.push({ key, color: condColors[key], len, t0: acc / denom, t1: (acc + len) / denom });
                    acc += len;
                }
            });
            return pieces;
        }

        function piecePopup(seg, piece, svLat, svLng) {
            return `<b>${condLabels[piece.key]}</b> — ${piece.len.toLocaleString('id-ID')} m<br>
                <span style="color:#6b7280">Segmen STA ${staFmt(seg.sta_awal)} – ${staFmt(seg.sta_akhir)}</span><br>
                Baik: ${seg.baik} m &nbsp; Sedang: ${seg.sedang} m<br>
                R.Ringan: ${seg.rusak_ringan} m &nbsp; R.Berat: ${seg.rusak_berat} m<br>
                <a href="https://www.google.com/maps?q=&layer=c&cbll=${svLat},${svLng}" target="_blank" rel="noopener"
                   style="color:#2563eb;text-decoration:underline">Buka Street View titik ini</a>`;
        }

        // Jarak haversine antar dua titik [lat,lng] dalam meter
        function haversine(a, b) {
            const R = 6371000, toRad = d => d * Math.PI / 180;
            const dLat = toRad(b[0] - a[0]), dLng = toRad(b[1] - a[1]);
            const s = Math.sin(dLat/2)**2 + Math.cos(toRad(a[0]))*Math.cos(toRad(b[0]))*Math.sin(dLng/2)**2;
            return 2 * R * Math.asin(Math.sqrt(s));
        }

        // Legenda warna (kontrol Leaflet, pojok kanan bawah)
        function addLegend(map) {
            const legend = L.control({ position: 'bottomright' });
            legend.onAdd = function () {
                const div = L.DomUtil.create('div');
                div.style.cssText = 'background:#fff;padding:8px 10px;border-radius:8px;box-shadow:0 1px 4px rgba(0,0,0,.25);font:12px/1.4 sans-serif';
                div.innerHTML = '<div style="font-weight:600;margin-bottom:4px">Kondisi Jalan</div>' +
                    condOrder.map(k =>
                        `<div style="display:flex;align-items:center;gap:6px;margin:2px 0">
                            <span style="width:14px;height:6px;border-radius:2px;background:${condColors[k]};display:inline-block"></span>
                            <span style="color:#374151">${condLabels[k]}</span>
                        </div>`).join('');
                return div;
            };
            legend.addTo(map);
        }

        // Marker patok STA setiap interval "bulat" sepanjang rute
        function addStaMarkers(map, posAtFrac, totalSta) {
            if (!(totalSta > 0)) return;
            // Pilih interval agar jumlah patok wajar (<= ~15)
            let step = 1000;
            const steps = [1000, 2000, 5000, 10000, 20000, 50000];
            for (const s of steps) { step = s; if (totalSta / s <= 15) break; }
            for (let m = 0; m <= totalSta + 1; m += step) {
                const pos = posAtFrac(Math.min(m / totalSta, 1));
                if (!pos) continue;
                L.marker(pos, {
                    icon: L.divIcon({
                        className: '',
                        html: `<div style="background:#1f2937;color:#fff;font:10px/1 sans-serif;padding:2px 4px;border-radius:4px;white-space:nowrap;box-shadow:0 1px 2px rgba(0,0,0,.4)">${staFmt(m)}</div>`,
                        iconSize: [0, 0], iconAnchor: [0, 18]
                    }),
                    interactive: false, keyboard: false
                }).addTo(map);
            }
        }

        function initMap() {
            const container = document.getElementById('ruas-detail-map');
            if (!container) return;

            // Konversi rute [[lng,lat]] -> [[lat,lng]]
            const route = Array.isArray(rawRoute) ? rawRoute.map(p => [p[1], p[0]]) : [];
            const hasRoute = route.length >= 2;
            const hasValidCoords = hasRoute || (latAwal !== null && lngAwal !== null && (latAwal !== 0 || lngAwal !== 0));

            if (!hasValidCoords) {
                container.style.height = 'auto';
                container.innerHTML = `
                    <div class="flex flex-col items-center justify-center py-8 px-4 bg-slate-50 rounded-xl border border-dashed border-slate-300 text-center">
                        <svg class="w-8 h-8 text-slate-400 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                        <p class="text-xs font-semibold text-slate-700">Koordinat Peta Belum Tersedia</p>
                        <p class="text-[11px] text-slate-500 max-w-sm mt-0.5">Data ruas ini belum memiliki titik koordinat awal/akhir atau rute KML. Anda dapat mengedit ruas jalan untuk menambahkan koordinat peta.</p>
                    </div>
                `;
                return;
            }

            const googleHybrid = L.tileLayer('https://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
                maxZoom: 20,
                attribution: '&copy; Google Maps'
            });
            const googleSat = L.tileLayer('https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
                maxZoom: 20,
                attribution: '&copy; Google Maps'
            });
            const googleStreets = L.tileLayer('https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                maxZoom: 20,
                attribution: '&copy; Google Maps'
            });
            const osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            });

            const map = L.map(container, {
                layers: [googleHybrid]
            });

            L.control.layers({
                "🛰️ Google Satelit Hybrid": googleHybrid,
                "📷 Google Satelit Murni": googleSat,
                "🗺️ Google Streets": googleStreets,
                "🌐 OpenStreetMap": osm
            }, null, { position: 'topright' }).addTo(map);

            const makeIcon = (color) => L.divIcon({
                className: '',
                html: `<div style="width:14px;height:14px;border-radius:50%;background:${color};border:3px solid #fff;box-shadow:0 0 0 1px rgba(0,0,0,.3)"></div>`,
                iconSize: [14, 14], iconAnchor: [7, 7]
            });

            // Titik awal & akhir (pakai ujung rute jika ada, agar konsisten)
            const startPt = hasRoute ? route[0] : [latAwal, lngAwal];
            const endPt   = hasRoute ? route[route.length - 1]
                          : (latAkhir !== null ? [latAkhir, lngAkhir] : null);

            L.marker(startPt, { icon: makeIcon('#10b981') }).addTo(map).bindPopup('<b>Titik Awal</b>');
            if (endPt) L.marker(endPt, { icon: makeIcon('#ef4444') }).addTo(map).bindPopup('<b>Titik Akhir</b>');

            const totalSta = panjangRuas > 0 ? panjangRuas
                           : (segments.length ? segments[segments.length - 1].sta_akhir : 0);

            if (hasRoute) {
                const posAtFrac = drawWithRoute(map, route, totalSta);
                addStaMarkers(map, posAtFrac, totalSta);
                map.fitBounds(L.polyline(route).getBounds(), { padding: [30, 30] });
            } else if (endPt) {
                const posAtFrac = drawStraight(map, totalSta);
                addStaMarkers(map, posAtFrac, totalSta);
                map.fitBounds([startPt, endPt], { padding: [40, 40] });
            } else {
                map.setView(startPt, 14);
            }

            if (segments.length > 0) addLegend(map);
            setTimeout(() => map.invalidateSize(), 250);
        }

        // --- Mode 1: rute asli dari KML, kondisi mengikuti lekuk jalan ---
        // Mengembalikan posAtFrac(frac 0..1) -> [lat,lng] untuk penempatan patok STA
        function drawWithRoute(map, route, totalSta) {
            // Hitung jarak kumulatif tiap vertex
            const cum = [0];
            for (let i = 1; i < route.length; i++) cum.push(cum[i-1] + haversine(route[i-1], route[i]));
            const total = cum[cum.length - 1] || 1;

            // Titik pada jarak d (meter) sepanjang rute
            const pointAtDist = (d) => {
                d = Math.max(0, Math.min(d, total));
                let i = 1;
                while (i < cum.length && cum[i] < d) i++;
                if (i >= route.length) return route[route.length - 1];
                const segLen = cum[i] - cum[i-1] || 1;
                const t = (d - cum[i-1]) / segLen;
                return [
                    route[i-1][0] + t * (route[i][0] - route[i-1][0]),
                    route[i-1][1] + t * (route[i][1] - route[i-1][1])
                ];
            };

            // Potongan rute antara jarak d0..d1 (termasuk vertex di antaranya)
            const sliceByDist = (d0, d1) => {
                const pts = [pointAtDist(d0)];
                for (let i = 0; i < route.length; i++) {
                    if (cum[i] > d0 && cum[i] < d1) pts.push(route[i]);
                }
                pts.push(pointAtDist(d1));
                return pts;
            };

            const staSpan = totalSta > 0 ? totalSta : total;

            if (segments.length > 0) {
                segments.forEach(seg => {
                    // Petakan STA segmen -> jarak rute secara proporsional
                    const segD0 = (seg.sta_awal  / staSpan) * total;
                    const segD1 = (seg.sta_akhir / staSpan) * total;
                    const span  = segD1 - segD0;
                    // Gambar tiap kondisi sebagai sub-potongan berurutan
                    condPieces(seg).forEach(piece => {
                        const dd0 = segD0 + piece.t0 * span;
                        const dd1 = segD0 + piece.t1 * span;
                        const slice = sliceByDist(dd0, dd1);
                        const sv = slice[0];
                        L.polyline(slice, { color: piece.color, weight: 7, opacity: 0.9 })
                            .addTo(map).bindPopup(piecePopup(seg, piece, sv[0], sv[1]));
                    });
                });
            } else {
                L.polyline(route, { color: '#7c3aed', weight: 5, opacity: 0.8 }).addTo(map);
            }

            return (frac) => pointAtDist(frac * total);
        }

        // --- Mode 2: fallback garis lurus (hanya punya titik awal & akhir) ---
        function drawStraight(map, totalSta) {
            const interpolate = (t) => [
                latAwal + t * (latAkhir - latAwal),
                lngAwal + t * (lngAkhir - lngAwal)
            ];
            const staSpan = totalSta > 0 ? totalSta : (segments.length ? segments[segments.length - 1].sta_akhir : 1);

            if (segments.length > 0) {
                segments.forEach(seg => {
                    const f0 = Math.min(seg.sta_awal  / staSpan, 1);
                    const f1 = Math.min(seg.sta_akhir / staSpan, 1);
                    const span = f1 - f0;
                    condPieces(seg).forEach(piece => {
                        const p0 = interpolate(f0 + piece.t0 * span);
                        const p1 = interpolate(f0 + piece.t1 * span);
                        L.polyline([p0, p1], { color: piece.color, weight: 7, opacity: 0.85 })
                            .addTo(map).bindPopup(piecePopup(seg, piece, p0[0], p0[1]));
                    });
                });
            } else {
                L.polyline([[latAwal, lngAwal], [latAkhir, lngAkhir]], { color: '#3b82f6', weight: 5, opacity: 0.7 }).addTo(map);
            }

            return (frac) => interpolate(frac);
        }

        // Jalankan setelah browser selesai render elemen saat ini
        // (DOMContentLoaded sudah terlambat karena view ini dirender di tengah halaman)
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initMap);
        } else {
            setTimeout(initMap, 0);
        }
    })();
    </script>
    <?php endif; ?>

    <!-- Table 1: Kondisi Jalan (Strip Map) -->
    <?php if (!empty($stripmaps)): ?>
    <div x-data="{ isOpen: true }" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between cursor-pointer select-none bg-gray-50/60" @click="isOpen = !isOpen">
            <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                <span class="w-2 h-4 rounded bg-blue-600 inline-block"></span>
                Data Segmen Kondisi Jalan (Strip Map)
            </h2>
            <button class="text-gray-500 hover:text-gray-700 focus:outline-none transition-transform duration-200" :class="isOpen ? 'rotate-90' : 'rotate-0'">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>
        <div x-show="isOpen" x-collapse class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">No</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">STA Awal</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">STA Akhir</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Panjang</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-emerald-700 uppercase tracking-wider">Baik</th>
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
                            <span class="inline-flex px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 text-xs font-semibold"><?= format_number($sm['baik']) ?></span>
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
                                <a href="<?= base_url('stripmap/create/' . $ruas['id'] . '?insert_after=' . $sm['id']) ?>"
                                   class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors"
                                   title="Sisipkan segmen baru setelah segmen ini">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                    Sisipkan
                                </a>
                                <a href="<?= base_url('stripmap/edit/' . $sm['id']) ?>"
                                   class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-amber-700 bg-amber-50 rounded-lg hover:bg-amber-100 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    Edit
                                </a>
                                <a href="<?= base_url('stripmap/delete/' . $sm['id']) ?>"
                                   onclick="confirmDelete(event, this.href, 'Yakin ingin menghapus segmen ini?')"
                                   class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-red-700 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
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
    <?php endif; ?>

    <!-- Table 2: Jenis Perkerasan Jalan -->
    <?php if (!empty($perkerasans)): ?>
    <div x-data="{ isOpen: true }" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between cursor-pointer select-none bg-gray-50/60" @click="isOpen = !isOpen">
            <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                <span class="w-2 h-4 rounded bg-amber-700 inline-block"></span>
                Data Segmen Jenis Perkerasan Jalan
            </h2>
            <button class="text-gray-500 hover:text-gray-700 focus:outline-none transition-transform duration-200" :class="isOpen ? 'rotate-90' : 'rotate-0'">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>
        <div x-show="isOpen" x-collapse class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">No</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">STA Awal</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">STA Akhir</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Panjang</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Rigid</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-900 uppercase tracking-wider">Aspal</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-amber-800 uppercase tracking-wider">Agregat / Tanah</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-purple-700 uppercase tracking-wider">Belum Tembus</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($perkerasans as $i => $pk): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 text-sm text-gray-500"><?= $i + 1 ?></td>
                        <td class="px-4 py-3 text-sm text-gray-700 text-center font-mono"><?= meter_to_sta($pk['sta_awal']) ?></td>
                        <td class="px-4 py-3 text-sm text-gray-700 text-center font-mono"><?= meter_to_sta($pk['sta_akhir']) ?></td>
                        <td class="px-4 py-3 text-sm text-gray-700 text-center font-semibold"><?= format_number($pk['panjang']) ?></td>
                        <td class="px-4 py-3 text-sm text-center">
                            <span class="inline-flex px-2 py-0.5 rounded-md bg-gray-100 text-gray-700 text-xs font-semibold"><?= format_number($pk['rigid']) ?></span>
                        </td>
                        <td class="px-4 py-3 text-sm text-center">
                            <span class="inline-flex px-2 py-0.5 rounded-md bg-slate-900 text-white text-xs font-semibold"><?= format_number($pk['aspal']) ?></span>
                        </td>
                        <td class="px-4 py-3 text-sm text-center">
                            <span class="inline-flex px-2 py-0.5 rounded-md bg-amber-100 text-amber-900 text-xs font-semibold"><?= format_number($pk['agregat_tanah']) ?></span>
                        </td>
                        <td class="px-4 py-3 text-sm text-center">
                            <span class="inline-flex px-2 py-0.5 rounded-md bg-purple-100 text-purple-800 text-xs font-semibold"><?= format_number($pk['belum_tembus']) ?></span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <a href="<?= base_url('perkerasan/edit/' . $pk['id']) ?>"
                                   class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-amber-700 bg-amber-50 rounded-lg hover:bg-amber-100 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    Edit
                                </a>
                                <a href="<?= base_url('perkerasan/delete/' . $pk['id']) ?>"
                                   onclick="confirmDelete(event, this.href, 'Yakin ingin menghapus data perkerasan ini?')"
                                   class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-red-700 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
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
    <?php endif; ?>

    <?php if (empty($stripmaps) && empty($perkerasans)): ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2z"/>
        </svg>
        <h3 class="text-lg font-semibold text-gray-600 mb-2">Belum ada data strip map & perkerasan</h3>
        <p class="text-sm text-gray-500 mb-6">Tambahkan segmen pertama untuk ruas ini.</p>
        <a href="<?= base_url('stripmap/create/' . $ruas['id']) ?>"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-xl hover:bg-blue-700 transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Segmen Data
        </a>
    </div>
    <?php endif; ?>

</div>

<!-- JSZip for KMZ extraction & Direct KML Import Handler -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script>
async function handleDirectKmlImport(e, ruasId) {
    const file = e.target.files[0];
    if (!file) return;
    const name = file.name.toLowerCase();
    try {
        let kmlText;
        if (name.endsWith('.kmz')) {
            if (typeof JSZip === 'undefined') {
                alert('Pustaka JSZip belum termuat.');
                return;
            }
            const zip = await JSZip.loadAsync(await file.arrayBuffer());
            const kmlEntry = Object.keys(zip.files).find(f => f.toLowerCase().endsWith('.kml'));
            if (!kmlEntry) { alert('File KMZ tidak berisi file .kml.'); return; }
            kmlText = await zip.files[kmlEntry].async('string');
        } else if (name.endsWith('.kml')) {
            kmlText = await file.text();
        } else {
            alert('Format file harus .kml atau .kmz');
            return;
        }

        const coords = parseKmlRouteText(kmlText); // [[lat, lng], ...]
        if (coords.length < 2) {
            alert('Tidak ditemukan garis rute (LineString) yang valid di dalam file KML/KMZ.');
            return;
        }

        const first = coords[0];
        const last = coords[coords.length - 1];

        document.getElementById('kml_koordinat_json').value = JSON.stringify(coords.map(p => [p[1], p[0]]));
        document.getElementById('kml_lat_awal').value = Math.round(first[0] * 1e7) / 1e7;
        document.getElementById('kml_lng_awal').value = Math.round(first[1] * 1e7) / 1e7;
        document.getElementById('kml_lat_akhir').value = Math.round(last[0] * 1e7) / 1e7;
        document.getElementById('kml_lng_akhir').value = Math.round(last[1] * 1e7) / 1e7;

        document.getElementById('direct-kml-form').submit();
    } catch (err) {
        console.error('Import KML error:', err);
        alert('Gagal membaca file. Pastikan file KML/KMZ valid.');
    }
}

function parseKmlRouteText(text) {
    const doc = new DOMParser().parseFromString(text, 'application/xml');
    const lines = [];

    const lineNodes = doc.querySelectorAll('LineString coordinates, linestring coordinates');
    lineNodes.forEach(node => {
        const pts = parseSingleCoordString(node.textContent);
        if (pts.length >= 2) lines.push(pts);
    });

    if (lines.length === 0) {
        const trackNodes = doc.querySelectorAll('Track, gx\\:Track');
        trackNodes.forEach(tnode => {
            const coordNodes = tnode.querySelectorAll('coord, gx\\:coord');
            let pts = [];
            coordNodes.forEach(c => {
                const parts = c.textContent.trim().split(/\s+/);
                const lng = parseFloat(parts[0]);
                const lat = parseFloat(parts[1]);
                if (!isNaN(lat) && !isNaN(lng)) pts.push([lat, lng]);
            });
            if (pts.length >= 2) lines.push(pts);
        });
    }

    if (lines.length === 0) {
        const allCoordNodes = doc.getElementsByTagName('coordinates');
        for (let i = 0; i < allCoordNodes.length; i++) {
            const pts = parseSingleCoordString(allCoordNodes[i].textContent);
            if (pts.length >= 2) lines.push(pts);
        }
    }

    if (lines.length === 0) return [];
    lines.sort((a, b) => b.length - a.length);
    return lines[0];
}

function parseSingleCoordString(raw) {
    if (!raw) return [];
    let points = [];
    raw.trim().split(/\s+/).forEach(tuple => {
        const parts = tuple.split(',');
        const lng = parseFloat(parts[0]);
        const lat = parseFloat(parts[1]);
        if (!isNaN(lat) && !isNaN(lng)) points.push([lat, lng]);
    });
    return points;
}
</script>
