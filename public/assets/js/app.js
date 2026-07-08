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
 * Konfirmasi hapus dengan SweetAlert2
 * Menggunakan async karena SweetAlert2 berbasis Promise
 * @param {Event} event - Click event
 * @param {string} url - URL tujuan jika dikonfirmasi
 * @param {string} message - Pesan konfirmasi
 */
function confirmDelete(event, url, message) {
    event.preventDefault();
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: message || 'Yakin ingin menghapus data ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    });
}

/**
 * Tampilkan alert dengan SweetAlert2
 * Digunakan untuk validasi form dan notifikasi umum
 * @param {string} message - Pesan yang ditampilkan
 * @param {string} icon - Tipe icon: 'success', 'error', 'warning', 'info', 'question'
 * @param {string} title - Judul alert
 */
function showAlert(message, icon, title) {
    Swal.fire({
        title: title || 'Perhatian',
        text: message,
        icon: icon || 'warning',
        confirmButtonColor: '#3b82f6',
        confirmButtonText: 'OK'
    });
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
