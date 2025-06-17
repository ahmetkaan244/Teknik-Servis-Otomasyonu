<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Site kök URL'si
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];

// Ana dizini bul
$root_path = dirname($_SERVER['PHP_SELF']);
$root_path = str_replace('\\', '/', $root_path); // Windows için düzeltme
$root_path = rtrim($root_path, '/');

// Eğer /musteri veya /admin dizinindeyse, bir üst dizine çık
if (strpos($root_path, '/musteri') !== false || strpos($root_path, '/admin') !== false) {
    $root_path = dirname($root_path);
}

// Root path'i normalize et
$root_path = $root_path === '/' ? '' : $root_path;

// Site URL'sini tanımla
define('SITE_URL', $protocol . $host . $root_path);

// Debug bilgisi
define('DEBUG', true);

// Sayfa yönlendirme fonksiyonu
function yonlendir($sayfa) {
    if (headers_sent()) {
        echo '<script>window.location.href="' . rtrim(SITE_URL, '/') . '/' . ltrim($sayfa, '/') . '";</script>';
        echo '<noscript><meta http-equiv="refresh" content="0;url=' . rtrim(SITE_URL, '/') . '/' . ltrim($sayfa, '/') . '"></noscript>';
    } else {
        if (ob_get_level()) {
            ob_end_clean();
        }
        header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        header('Pragma: no-cache');
        header('Location: ' . rtrim(SITE_URL, '/') . '/' . ltrim($sayfa, '/'));
    }
    exit();
}

// Tam URL oluşturma fonksiyonu
function url($yol) {
    // Eğer tam URL verilmişse, olduğu gibi döndür
    if (strpos($yol, 'http://') === 0 || strpos($yol, 'https://') === 0) {
        return $yol;
    }
    
    // Debug modunda URL bilgilerini göster
    if (DEBUG) {
        error_log("URL Debug: SITE_URL = " . SITE_URL);
        error_log("URL Debug: Requested Path = " . $yol);
        error_log("URL Debug: PHP_SELF = " . $_SERVER['PHP_SELF']);
        error_log("URL Debug: Root Path = " . dirname($_SERVER['PHP_SELF']));
    }
    
    // Yerel dosya yolunu URL'ye çevir
    return rtrim(SITE_URL, '/') . '/' . ltrim($yol, '/');
}
?> 