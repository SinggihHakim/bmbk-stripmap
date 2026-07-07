<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Strip Map Ruas Jalan') ?></title>

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

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
</head>
<body class="h-full bg-gray-50 font-sans antialiased">

    <div class="min-h-full">

        <!-- Navbar -->
        <?php view('layouts.navbar'); ?>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <!-- Flash Messages -->
            <?php $flash = get_flash(); ?>
            <?php if ($flash): ?>
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 -translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 -translate-y-2"
                     class="mb-6 rounded-xl p-4 shadow-sm border
                        <?= $flash['type'] === 'success'
                            ? 'bg-green-50 border-green-200 text-green-800'
                            : 'bg-red-50 border-red-200 text-red-800' ?>">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <?php if ($flash['type'] === 'success'): ?>
                                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            <?php else: ?>
                                <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                            <?php endif; ?>
                            <p class="text-sm font-medium"><?= $flash['message'] ?></p>
                        </div>
                        <button @click="show = false" class="text-gray-400 hover:text-gray-600 transition">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>
                </div>
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
        <footer class="border-t border-gray-200 bg-white mt-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 text-center text-sm text-gray-500">
                &copy; <?= date('Y') ?> Strip Map Ruas Jalan. All rights reserved.
            </div>
        </footer>

    </div>

    <!-- Custom JS -->
    <script src="<?= base_url('assets/js/app.js') ?>"></script>

</body>
</html>
