<?php
ob_start(); // Output buffer başlat
session_start();
require_once '../config/config.php';
require_once '../config/database.php';

// Oturum kontrolü
if(!isset($_SESSION['admin_id'])){
    header("location: giris.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Form verilerini al
    $talep_id = mysqli_real_escape_string($conn, $_POST['talep_id']);
    $fatura_tipi = mysqli_real_escape_string($conn, $_POST['fatura_tipi']);
    $vergi_no = mysqli_real_escape_string($conn, $_POST['vergi_no']);
    $fatura_adres = mysqli_real_escape_string($conn, $_POST['fatura_adres']);
    $islem_aciklamasi = mysqli_real_escape_string($conn, $_POST['islem_aciklamasi']);
    $tutar = floatval($_POST['tutar']);
    
    // Fatura numarası oluştur (Yıl + Ay + 4 haneli sıra no)
    $yil = date('Y');
    $ay = date('m');
    
    // O ay için son fatura numarasını bul
    $sql = "SELECT MAX(SUBSTRING_INDEX(fatura_no, '-', -1)) as son_sira 
            FROM faturalar 
            WHERE fatura_no LIKE '$yil$ay-%'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $son_sira = $row['son_sira'] ? intval($row['son_sira']) : 0;
    $yeni_sira = str_pad($son_sira + 1, 4, '0', STR_PAD_LEFT);
    
    $fatura_no = $yil . $ay . '-' . $yeni_sira;
    
    // Faturayı veritabanına kaydet
    $sql = "INSERT INTO faturalar (
                talep_id, 
                fatura_no, 
                fatura_tipi,
                vergi_no,
                fatura_adres,
                islem_aciklamasi,
                tutar,
                created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
    
    if($stmt = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($stmt, "isssssd", 
            $talep_id,
            $fatura_no,
            $fatura_tipi,
            $vergi_no,
            $fatura_adres,
            $islem_aciklamasi,
            $tutar
        );
        
        if(mysqli_stmt_execute($stmt)){
            // Çıktı tamponunu temizle
            ob_clean();
            // Fatura PDF'sini oluştur ve indir
            header("Location: fatura_pdf.php?fatura_no=" . $fatura_no);
            exit;
        } else {
            echo "Fatura oluşturulurken bir hata oluştu.";
        }
        
        mysqli_stmt_close($stmt);
    }
}

mysqli_close($conn);
?> 