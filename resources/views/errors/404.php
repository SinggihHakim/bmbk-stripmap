<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Halaman Tidak Ditemukan</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] } } } }
    </script>
</head>
<body class="h-full bg-gray-50 font-sans antialiased flex items-center justify-center">
    <div class="text-center px-6">
        <p class="text-8xl font-extrabold text-blue-600 mb-4">404</p>
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Halaman Tidak Ditemukan</h1>
        <p class="text-gray-500 mb-8">Maaf, halaman yang Anda cari tidak ada atau telah dipindahkan.</p>
        <a href="<?= base_url() ?>"
           class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white text-sm font-medium rounded-xl hover:bg-blue-700 transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1"/>
            </svg>
            Kembali ke Dashboard
        </a>
    </div>
</body>
</html>
