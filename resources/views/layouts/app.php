<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Strip Map Ruas Jalan') ?></title>
    <link rel="shortcut icon" href="<?= base_url('assets/img/favicon.png') ?>" type="image/png">



    <!-- Google Font: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- TailwindCSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                    },
                }
            }
        }
    </script>

    <!-- Alpine.js Collapse Plugin -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
</head>
<body class="h-full bg-gray-50 font-sans antialiased" x-data="{ sidebarOpen: false }">

    <div class="min-h-screen flex flex-col">

        <!-- Sidebar Navigation Left -->
        <?php view('layouts.sidebar'); ?>

        <!-- Main Workspace (Shifted right on Desktop lg:pl-64) -->
        <div class="flex-1 flex flex-col lg:pl-64 transition-all duration-300">
            
            <!-- Top Mobile Header / Bar (Hidden on Desktop) -->
            <header class="lg:hidden bg-white border-b border-gray-200 sticky top-0 z-30 shadow-sm h-16 flex items-center justify-between px-4 sm:px-6">
                <!-- Hamburger Button (Mobile) -->
                <button type="button" 
                        @click="sidebarOpen = true" 
                        class="lg:hidden text-gray-500 hover:text-gray-700 p-2 rounded-lg hover:bg-gray-100 transition-colors"
                        aria-label="Open Sidebar">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>

                <!-- Page Header Title or System Brand Badge -->
                <div class="flex items-center gap-3">
                    <span class="text-xs font-semibold text-gray-500 bg-gray-100 px-3 py-1 rounded-full border border-gray-200">
                        Sistem Informasi Preservasi Jalan BMBK
                    </span>
                </div>

                <!-- Right Utility Icons (Quick Actions) -->
                <div class="flex items-center gap-3">
                    <a href="<?= base_url('ruas/create') ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 text-white text-xs font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        <span class="hidden sm:inline">Tambah Ruas</span>
                    </a>
                </div>
            </header>

            <!-- Main Content Container -->
            <main class="flex-grow w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

                <!-- Flash Messages (SweetAlert2 Toast) -->
                <?php $flash = get_flash(); ?>
                <?php if ($flash): ?>
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 5000,
                        timerProgressBar: true,
                        didOpen: (toast) => {
                            toast.onmouseenter = Swal.stopTimer;
                            toast.onmouseleave = Swal.resumeTimer;
                        }
                    });
                    Toast.fire({
                        icon: '<?= $flash['type'] === 'success' ? 'success' : 'error' ?>',
                        title: '<?= addslashes($flash['message']) ?>'
                    });
                });
                </script>
                <?php endif; ?>

                <!-- Page Content -->
                <?php
                    if (isset($content) && isset($__pageData)) {
                        view($content, $__pageData);
                    } elseif (isset($content)) {
                        $__childData = array_diff_key(
                            get_defined_vars(),
                            array_flip(['content', 'flash', '__childData', '__pageData'])
                        );
                        view($content, $__childData);
                    }
                ?>

            </main>

            <!-- Footer -->
            <footer class="border-t border-gray-200 bg-white mt-auto">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 text-center text-xs text-gray-500">
                    &copy; <?= date('Y') ?> Dinas Bina Marga & Bina Konstruksi Provinsi Lampung. All rights reserved.
                </div>
            </footer>

        </div>

    </div>

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Custom JS -->
    <script src="<?= base_url('assets/js/app.js') ?>"></script>

</body>
</html>
