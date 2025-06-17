<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $kullanici_adi = mysqli_real_escape_string($conn, $_POST['kullanici_adi']);
    $sifre = $_POST['sifre'];
    
    $sql = "SELECT id, kullanici_adi, sifre FROM yoneticiler WHERE kullanici_adi = ? AND sifre = ?";
    
    if($stmt = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($stmt, "ss", $kullanici_adi, $sifre);
        
        if(mysqli_stmt_execute($stmt)){
            $result = mysqli_stmt_get_result($stmt);
            
            if(mysqli_num_rows($result) == 1){
                $row = mysqli_fetch_assoc($result);
                $_SESSION['admin_id'] = $row['id'];
                $_SESSION['admin_username'] = $row['kullanici_adi'];
                yonlendir('admin/panel.php');
            } else{
                $message = "Kullanıcı adı veya şifre hatalı!";
            }
        } else{
            $message = "Hata oluştu! Lütfen daha sonra tekrar deneyin.";
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="tr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yönetici Girişi - Teknik Servis</title>
    <link rel="stylesheet" href="<?php echo url('assets/css/tailwind.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo url('assets/css/style.css'); ?>">
</head>
<body class="flex flex-col min-h-full bg-gray-100">
    <?php include __DIR__ . '/../includes/header.php'; ?>
    
    <main class="flex-grow flex items-center justify-center py-8">
        <div class="max-w-md w-full mx-4">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-bold text-center mb-6">Yönetici Girişi</h2>
                
                <?php if($message): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-4">
                        <label for="kullanici_adi" class="block text-gray-700 text-sm font-bold mb-2">Kullanıcı Adı</label>
                        <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="kullanici_adi" name="kullanici_adi" required>
                    </div>
                    
                    <div class="mb-6">
                        <label for="sifre" class="block text-gray-700 text-sm font-bold mb-2">Şifre</label>
                        <input type="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="sifre" name="sifre" required>
                    </div>
                    
                    <div class="flex items-center justify-center">
                    <button type="submit" style="background-color: #2563eb;" class="w-full text-black py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                     Gönder
                    </button>

                    </div>
                </form>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html> 