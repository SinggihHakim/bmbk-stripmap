<!-- ============================================================ -->
<!-- Navbar -->
<!-- ============================================================ -->
<nav class="bg-white border-b border-gray-200 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

            <!-- Logo / Brand -->
            <a href="<?= base_url() ?>" class="flex items-center gap-3 group">
                <img src="<?= base_url('assets/img/logo.png') ?>" alt="Logo" class="h-10 w-auto object-contain">
                <div class="flex flex-col justify-center hidden sm:flex">
                    <span class="text-base font-bold text-gray-900 leading-tight tracking-tight">Stripmap</span>
                    <span class="text-xs font-medium text-gray-500 leading-tight">Bina Marga dan Bina Kontruksi Provinsi Lampung</span>
                </div>
            </a>

            <!-- Navigation Links -->
            <div class="flex items-center gap-6">
                <?php
                $currentUrl = trim($_GET['url'] ?? '', '/');
                $navItems = [
                    ['url' => '',     'label' => 'Dashboard', 'match' => ['']],
                    ['url' => 'ruas', 'label' => 'Ruas Jalan', 'match' => ['ruas', 'stripmap']],
                ];
                ?>
                <?php foreach ($navItems as $nav): ?>
                    <?php
                    $isActive = false;
                    foreach ($nav['match'] as $m) {
                        if (($m === '' && $currentUrl === '') || ($m !== '' && str_starts_with($currentUrl, $m))) {
                            $isActive = true;
                            break;
                        }
                    }
                    ?>
                    <a href="<?= base_url($nav['url']) ?>"
                       class="relative py-2 px-1 text-sm font-medium transition-colors group
                              <?= $isActive
                                  ? 'text-blue-700 after:scale-x-100'
                                  : 'text-gray-500 hover:text-blue-900 after:scale-x-0 hover:after:scale-x-100' ?>
                              after:content-[''] after:absolute after:left-0 after:-bottom-1 after:w-full after:h-0.5 after:bg-blue-600 after:transition-transform after:duration-300 after:origin-left">
                        <?= $nav['label'] ?>
                    </a>
                <?php endforeach; ?>
            </div>

        </div>
    </div>
</nav>
