<!-- ============================================================ -->
<!-- Navbar -->
<!-- ============================================================ -->
<nav class="bg-white border-b border-gray-200 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

            <!-- Logo / Brand -->
            <a href="<?= base_url() ?>" class="flex items-center gap-3 group">
                <div class="w-9 h-9 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-sm group-hover:shadow-md transition-shadow">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                    </svg>
                </div>
                <span class="text-lg font-bold text-gray-800">Strip Map</span>
            </a>

            <!-- Navigation Links -->
            <div class="flex items-center gap-1">
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
                       class="px-4 py-2 rounded-lg text-sm font-medium transition-colors
                              <?= $isActive
                                  ? 'bg-blue-50 text-blue-700'
                                  : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' ?>">
                        <?= $nav['label'] ?>
                    </a>
                <?php endforeach; ?>
            </div>

        </div>
    </div>
</nav>
