/**
 * ============================================================
 * Strip Map Ruas Jalan — Main JavaScript
 * ============================================================
 * Utility functions yang digunakan di seluruh aplikasi.
 * Alpine.js component logic ada di masing-masing view (inline).
 */

/**
 * Konversi format STA (misal "2+350") ke meter (2350)
 * @param {string} sta
 * @returns {number}
 */
function staToMeter(sta) {
    sta = sta.trim();
    if (sta.includes('+')) {
        const parts = sta.split('+');
        return parseFloat(parts[0]) * 1000 + parseFloat(parts[1] || 0);
    }
    return parseFloat(sta) || 0;
}

/**
 * Konversi meter ke format STA (misal 2350 → "2+350")
 * @param {number} meter
 * @returns {string}
 */
function meterToSta(meter) {
    const km = Math.floor(meter / 1000);
    const m  = meter - (km * 1000);
    return `${km}+${String(Math.round(m)).padStart(3, '0')}`;
}

/**
 * Format angka dengan separator ribuan (format Indonesia)
 * @param {number} num
 * @param {number} decimals
 * @returns {string}
 */
function formatNumber(num, decimals = 0) {
    return new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
    }).format(num);
}

/**
 * Konfirmasi hapus dengan dialog
 * @param {string} message
 * @returns {boolean}
 */
function confirmDelete(message) {
    return confirm(message || 'Yakin ingin menghapus data ini?');
}

/**
 * Auto-format input STA saat mengetik
 * Menambahkan '+' otomatis setelah digit km
 * @param {HTMLInputElement} input
 */
function autoFormatSta(input) {
    let val = input.value.replace(/[^0-9+]/g, '');

    // Jika belum ada '+' dan sudah ada lebih dari 1 digit
    if (!val.includes('+') && val.length > 1) {
        // Sisipkan '+' sebelum 3 digit terakhir
        if (val.length <= 3) {
            val = '0+' + val.padStart(3, '0');
        } else {
            const km = val.slice(0, val.length - 3);
            const m  = val.slice(val.length - 3);
            val = km + '+' + m;
        }
    }

    input.value = val;
}
