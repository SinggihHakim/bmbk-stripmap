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
<body class="h-full bg-gray-50 font-sans antialiased">

    <div class="min-h-screen flex flex-col">

        <!-- Navbar -->
        <?php view('layouts.navbar'); ?>

        <!-- Main Content -->
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
                // $__pageData dikirim dari controller melalui view() helper
                // dan sudah di-inject oleh fungsi view() sebelum file ini di-require.
                // Kita gunakan $__pageData untuk meneruskan data bersih ke child view.
                if (isset($content) && isset($__pageData)) {
                    view($content, $__pageData);
                } elseif (isset($content)) {
                    // Fallback: kumpulkan variabel yang tersedia sekarang
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
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 text-center text-sm text-gray-500">
                &copy; <?= date('Y') ?> Strip Map Ruas Jalan. All rights reserved.
            </div>
        </footer>

    </div>

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Custom JS -->
    <script src="<?= base_url('assets/js/app.js') ?>"></script>

</body>
</html>
