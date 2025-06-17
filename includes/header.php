<?php 
if (!defined('SITE_URL')) {
    require_once __DIR__ . '/../config/config.php';
}

// Aktif sayfayı belirle
$current_page = $_SERVER['PHP_SELF'];
$is_home = $current_page === '/index.php' || $current_page === rtrim(dirname($_SERVER['PHP_SELF']), '/') . '/index.php';
?>
<nav class="bg-gray-800 shadow-lg">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="flex-shrink-0 flex items-center">
                    <a href="<?php echo rtrim(SITE_URL, '/'); ?>/" class="text-white font-bold text-xl">Teknik Servis</a>
                </div>
                <div class="hidden md:ml-6 md:flex md:items-center md:space-x-4">
                    <?php if (isset($admin_header) && $admin_header === true): ?>
                        <a href="<?php echo url('admin/panel.php'); ?>" class="bg-gray-900 text-white px-3 py-2 rounded-md text-sm font-medium">Panel</a>
                        <a href="<?php echo url('admin/cikis.php'); ?>" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition duration-150 ease-in-out">Çıkış</a>
                    <?php else: ?>
                        <a href="<?php echo rtrim(SITE_URL, '/'); ?>/" class="<?php echo $is_home ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'; ?> px-3 py-2 rounded-md text-sm font-medium transition duration-150 ease-in-out">Ana Sayfa</a>
                        <a href="<?php echo url('musteri/servis-talebi.php'); ?>" class="<?php echo (strpos($current_page, '/musteri/servis-talebi.php') !== false) ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'; ?> px-3 py-2 rounded-md text-sm font-medium transition duration-150 ease-in-out">Servis Talebi</a>
                        <a href="<?php echo url('musteri/talep-sorgula.php'); ?>" class="<?php echo (strpos($current_page, '/musteri/talep-sorgula.php') !== false) ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'; ?> px-3 py-2 rounded-md text-sm font-medium transition duration-150 ease-in-out">Talep Sorgula</a>
                        <a href="<?php echo url('admin/giris.php'); ?>" class="<?php echo (strpos($current_page, '/admin/giris.php') !== false) ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'; ?> px-3 py-2 rounded-md text-sm font-medium transition duration-150 ease-in-out">Yönetici Girişi</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="flex items-center md:hidden">
                <button type="button" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white" aria-controls="mobile-menu" aria-expanded="false">
                    <span class="sr-only">Menüyü aç</span>
                    <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
    <div class="md:hidden hidden" id="mobile-menu">
        <div class="px-2 pt-2 pb-3 space-y-1">
            <?php if (isset($admin_header) && $admin_header === true): ?>
                <a href="<?php echo url('admin/panel.php'); ?>" class="bg-gray-900 text-white block px-3 py-2 rounded-md text-base font-medium">Panel</a>
                <a href="<?php echo url('admin/cikis.php'); ?>" class="text-gray-300 hover:bg-gray-700 hover:text-white block px-3 py-2 rounded-md text-base font-medium">Çıkış</a>
            <?php else: ?>
                <a href="<?php echo rtrim(SITE_URL, '/'); ?>/" class="<?php echo $is_home ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'; ?> block px-3 py-2 rounded-md text-base font-medium">Ana Sayfa</a>
                <a href="<?php echo url('musteri/servis-talebi.php'); ?>" class="<?php echo (strpos($current_page, '/musteri/servis-talebi.php') !== false) ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'; ?> block px-3 py-2 rounded-md text-base font-medium">Servis Talebi</a>
                <a href="<?php echo url('musteri/talep-sorgula.php'); ?>" class="<?php echo (strpos($current_page, '/musteri/talep-sorgula.php') !== false) ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'; ?> block px-3 py-2 rounded-md text-base font-medium">Talep Sorgula</a>
                <a href="<?php echo url('admin/giris.php'); ?>" class="<?php echo (strpos($current_page, '/admin/giris.php') !== false) ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'; ?> block px-3 py-2 rounded-md text-base font-medium">Yönetici Girişi</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<script>
document.querySelector('button[aria-controls="mobile-menu"]').addEventListener('click', function() {
    const menu = document.getElementById('mobile-menu');
    const expanded = this.getAttribute('aria-expanded') === 'true';
    this.setAttribute('aria-expanded', !expanded);
    menu.classList.toggle('hidden');
});
</script> 