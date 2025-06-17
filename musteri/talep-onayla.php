<?php
session_start();
require_once '../config/config.php';
require_once '../config/database.php';

$mesaj = '';
$hata = '';

// POST metoduyla gelen onay kodunu kontrol et
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kod = $_POST['onay_kodu'] ?? '';

    if (empty($kod)) {
        $hata = "Lütfen onay kodunu girin!";
    } else {
        // Onay kodunu kontrol et
        $sql = "SELECT ek.*, st.email, st.musteri_adi, st.onay_durumu 
                FROM email_onay_kodlari ek 
                INNER JOIN servis_talepleri st ON ek.talep_id = st.id 
                WHERE ek.onay_kodu = ?";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $kod);
            
            if (mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                
                if ($row = mysqli_fetch_assoc($result)) {
                    if ($row['onay_durumu'] === 'Onaylandı') {
                        $hata = "Bu servis talebi zaten onaylanmış!";
                    } else {
                        // Servis talebini onayla
                        $sql_update = "UPDATE servis_talepleri SET onay_durumu = 'Onaylandı', durum = 'İşlemde' WHERE id = ?";
                        if ($stmt_update = mysqli_prepare($conn, $sql_update)) {
                            mysqli_stmt_bind_param($stmt_update, "i", $row['talep_id']);
                            if (mysqli_stmt_execute($stmt_update)) {
                                $mesaj = "E-posta adresiniz başarıyla onaylandı. Servis talebiniz işleme alınmıştır.";
                                
                                // Oturum bilgisini güncelle
                                $_SESSION['talep_onay'] = [
                                    'talep_id' => $row['talep_id'],
                                    'email' => $row['email'],
                                    'onaylandi' => true
                                ];
                            } else {
                                $hata = "Onaylama işlemi sırasında bir hata oluştu: " . mysqli_error($conn);
                            }
                            mysqli_stmt_close($stmt_update);
                        } else {
                            $hata = "Güncelleme sorgusu hazırlanamadı: " . mysqli_error($conn);
                        }
                    }
                } else {
                    $hata = "Geçersiz onay kodu!";
                }
            } else {
                $hata = "Sorgu çalıştırılamadı: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        } else {
            $hata = "Sorgu hazırlanamadı: " . mysqli_error($conn);
        }
    }
} else {
    $hata = "Lütfen onay kodunu girin.";
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-posta Onayı - Teknik Servis</title>
    <link rel="stylesheet" href="<?php echo url('assets/css/tailwind.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo url('assets/css/style.css'); ?>">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <?php include '../includes/header.php'; ?>
    
    <main class="flex-grow container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">E-posta Onayı</h2>
                    
                    <?php if ($mesaj): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        <?php echo $mesaj; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($hata): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <?php echo $hata; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!$mesaj && !$hata): ?>
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="space-y-4">
                        <div>
                            <label for="onay_kodu" class="block text-sm font-medium text-gray-700">Onay Kodu</label>
                            <input type="text" name="onay_kodu" id="onay_kodu" required 
                                placeholder="Size e-posta ile gönderilen 6 haneli kodu girin"
                                pattern="[0-9]{6}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Onayla
                        </button>
                    </form>
                    <?php endif; ?>
                    
                    <?php if ($mesaj): ?>
                    <div class="mt-4">
                        <a href="<?php echo url('musteri/talep-sorgula.php'); ?>" 
                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Talep Sorgula
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html> 