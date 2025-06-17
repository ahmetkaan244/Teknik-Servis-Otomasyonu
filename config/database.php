<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Veritabanı bağlantı bilgileri
$db_host = 'localhost';
$db_name = 'teknik_servis';
$db_user = 'root';
$db_pass = '';

// Veritabanı bağlantısını oluştur
try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Hata raporlamayı etkinleştir
    
    $conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
    
    if (!$conn) {
        throw new Exception("Veritabanı bağlantısı başarısız: " . mysqli_connect_error());
    }
    
    // Bağlantı karakter setini ayarla
    if (!mysqli_set_charset($conn, "utf8mb4")) {
        throw new Exception("Karakter seti ayarlanamadı: " . mysqli_error($conn));
    }
    
    // Test sorgusu
    $test_query = "SHOW TABLES LIKE 'email_onay_kodlari'";
    $test_result = mysqli_query($conn, $test_query);
    if (!$test_result) {
        throw new Exception("Test sorgusu başarısız: " . mysqli_error($conn));
    }
    if (mysqli_num_rows($test_result) === 0) {
        throw new Exception("email_onay_kodlari tablosu bulunamadı!");
    }
    
} catch (Exception $e) {
    error_log("Veritabanı hatası: " . $e->getMessage());
    die("Veritabanı hatası: " . $e->getMessage());
}
?> 