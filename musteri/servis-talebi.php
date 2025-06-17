<?php
ob_start();
session_start();
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/mail.php';

$mesaj = '';
$hata = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Form verilerini al ve temizle
    $musteri_adi = trim(mysqli_real_escape_string($conn, $_POST['musteri_adi'] ?? ''));
    $email = trim(mysqli_real_escape_string($conn, $_POST['email'] ?? ''));
    $telefon = trim(mysqli_real_escape_string($conn, $_POST['telefon'] ?? ''));
    $teslimat_yontemi = trim(mysqli_real_escape_string($conn, $_POST['teslimat_yontemi'] ?? ''));
    $adres = trim(mysqli_real_escape_string($conn, $_POST['adres'] ?? ''));
    $kargo_kodu = trim(mysqli_real_escape_string($conn, $_POST['kargo_kodu'] ?? ''));
    $cihaz_turu = trim(mysqli_real_escape_string($conn, $_POST['cihaz_turu'] ?? ''));
    $marka_model = trim(mysqli_real_escape_string($conn, $_POST['marka_model'] ?? ''));
    $sorun_aciklamasi = trim(mysqli_real_escape_string($conn, $_POST['sorun_aciklamasi'] ?? ''));
    
    // Fotoğraf yükleme işlemi
    $servis_oncesi_foto = null;
    if (isset($_FILES['servis_oncesi_foto']) && $_FILES['servis_oncesi_foto']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/servis_foto/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['servis_oncesi_foto']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = array('jpg', 'jpeg', 'png');
        
        if (in_array($file_extension, $allowed_extensions)) {
            $unique_filename = uniqid('servis_oncesi_') . '.' . $file_extension;
            $target_file = $upload_dir . $unique_filename;
            
            if (move_uploaded_file($_FILES['servis_oncesi_foto']['tmp_name'], $target_file)) {
                $servis_oncesi_foto = $unique_filename;
            }
        }
    }
    
    // Form validasyonu
    if (empty($musteri_adi) || empty($email) || empty($telefon) || 
        empty($cihaz_turu) || empty($marka_model) || empty($sorun_aciklamasi) ||
        empty($teslimat_yontemi) || 
        ($teslimat_yontemi === 'Adrese Servis' && empty($adres)) ||
        ($teslimat_yontemi === 'Kargo' && empty($kargo_kodu))) {
        $hata = "Lütfen tüm zorunlu alanları doldurun.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $hata = "Geçerli bir e-posta adresi girin.";
    } else {
        try {
            // Servis talebini veritabanına kaydet
            $sql = "INSERT INTO servis_talepleri (musteri_adi, email, telefon, teslimat_yontemi, adres, kargo_kodu,
                    cihaz_turu, marka_model, sorun_aciklamasi, servis_oncesi_foto) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            if ($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param($stmt, "ssssssssss", 
                    $musteri_adi, $email, $telefon, $teslimat_yontemi, $adres, $kargo_kodu,
                    $cihaz_turu, $marka_model, $sorun_aciklamasi, $servis_oncesi_foto);
                
                if (mysqli_stmt_execute($stmt)) {
                    $talep_id = mysqli_insert_id($conn);
                    
                    // 6 haneli onay kodu oluştur
                    $onay_kodu = sprintf("%06d", mt_rand(0, 999999));
                    $sql_onay = "INSERT INTO email_onay_kodlari (talep_id, onay_kodu) VALUES (?, ?)";
                    
                    if ($stmt_onay = mysqli_prepare($conn, $sql_onay)) {
                        mysqli_stmt_bind_param($stmt_onay, "is", $talep_id, $onay_kodu);
                        
                        if (mysqli_stmt_execute($stmt_onay)) {
                            // E-posta gönder
                            $mail = new MailGonderici();
                            
                            $mail_icerik = "Sayın {$musteri_adi},\n\n";
                            $mail_icerik .= "Servis talebiniz başarıyla oluşturuldu.\n\n";
                            $mail_icerik .= "Talep Bilgileri:\n";
                            $mail_icerik .= "----------------\n";
                            $mail_icerik .= "Talep No: {$talep_id}\n";
                            $mail_icerik .= "Teslimat Yöntemi: {$teslimat_yontemi}\n";
                            $mail_icerik .= "Cihaz: {$cihaz_turu} - {$marka_model}\n";
                            $mail_icerik .= "Onay Kodu: {$onay_kodu}\n\n";
                            $mail_icerik .= "Servis talebinizi onaylamak için lütfen size gönderilen onay kodunu kullanın.\n\n";
                            $mail_icerik .= "Saygılarımızla,\nTeknik Servis Ekibi";
                            
                            error_log("Mail gönderiliyor: " . $email);
                            if ($mail->gonder($email, "Servis Talebi Onayı", $mail_icerik)) {
                                error_log("Mail başarıyla gönderildi");
                                $_SESSION['talep_onay'] = [
                                    'talep_id' => $talep_id,
                                    'email' => $email,
                                    'onay_kodu_gonderildi' => true
                                ];
                                $mesaj = "Servis talebiniz alınmıştır. Lütfen e-posta adresinize gönderilen onay kodunu kontrol edin.";
                            } else {
                                error_log("Mail gönderimi başarısız");
                                throw new Exception("E-posta gönderilemedi");
                            }
                        } else {
                            throw new Exception("Onay kodu kaydedilemedi");
                        }
                        mysqli_stmt_close($stmt_onay);
                    } else {
                        throw new Exception("Onay kodu sorgusu hazırlanamadı");
                    }
                } else {
                    throw new Exception("Servis talebi kaydedilemedi");
                }
                mysqli_stmt_close($stmt);
            } else {
                throw new Exception("Sorgu hazırlanamadı");
            }
        } catch (Exception $e) {
            error_log("Hata: " . $e->getMessage());
            $hata = "Bir hata oluştu: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Servis Talebi Oluştur - Teknik Servis</title>
    <link rel="stylesheet" href="<?php echo url('assets/css/tailwind.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo url('assets/css/style.css'); ?>">
</head>
<body class="flex flex-col min-h-full bg-gray-100">
    <?php include '../includes/header.php'; ?>
    
    <main class="flex-grow container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Servis Talebi Oluştur</h2>
                
                <?php if ($mesaj): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?php echo $mesaj; ?>
                    <div class="mt-4">
                        <form method="POST" action="talep-onayla.php" class="space-y-4">
                            <div>
                                <label for="onay_kodu" class="block text-sm font-medium text-gray-700">Onay Kodu</label>
                                <input type="text" name="onay_kodu" id="onay_kodu" required 
                                    placeholder="Size e-posta ile gönderilen 6 haneli kodu girin"
                                    pattern="[0-9]{6}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <button type="submit" style="background-color: #2563eb;" class="w-full text-black py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                     Onayla
                     </button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($hata): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo $hata; ?>
                </div>
                <?php endif; ?>
                
                <?php if (!$mesaj): ?>
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="space-y-6" enctype="multipart/form-data">
                    <div>
                        <label for="musteri_adi" class="block text-sm font-medium text-gray-700">Adınız Soyadınız</label>
                        <input type="text" name="musteri_adi" id="musteri_adi" required 
                            value="<?php echo isset($_POST['musteri_adi']) ? htmlspecialchars($_POST['musteri_adi']) : ''; ?>"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">E-posta Adresiniz</label>
                        <input type="email" name="email" id="email" required 
                            value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label for="telefon" class="block text-sm font-medium text-gray-700">Telefon Numaranız</label>
                        <input type="tel" name="telefon" id="telefon" required 
                            value="<?php echo isset($_POST['telefon']) ? htmlspecialchars($_POST['telefon']) : ''; ?>"
                            placeholder="05XX XXX XX XX"
                            pattern="[0-9]{10,11}"
                            title="Lütfen geçerli bir telefon numarası girin (örn: 05XX XXX XX XX)"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="teslimat_yontemi" class="block text-sm font-medium text-gray-700">Teslimat Yöntemi</label>
                        <select name="teslimat_yontemi" id="teslimat_yontemi" required 
                            onchange="teslimatYontemiDegisti(this.value)"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Seçiniz</option>
                            <option value="Adrese Servis" <?php echo (isset($_POST['teslimat_yontemi']) && $_POST['teslimat_yontemi'] === 'Adrese Servis') ? 'selected' : ''; ?>>Adrese Servis</option>
                            <option value="Kargo" <?php echo (isset($_POST['teslimat_yontemi']) && $_POST['teslimat_yontemi'] === 'Kargo') ? 'selected' : ''; ?>>Kargo ile Gönderim</option>
                        </select>
                    </div>
                    
                    <div id="adres_alani" style="display: none;">
                        <label for="adres" class="block text-sm font-medium text-gray-700">Adres</label>
                        <textarea name="adres" id="adres" rows="3" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"><?php echo isset($_POST['adres']) ? htmlspecialchars($_POST['adres']) : ''; ?></textarea>
                    </div>

                    <div id="kargo_alani" style="display: none;">
                        <label for="kargo_kodu" class="block text-sm font-medium text-gray-700">Kargo Takip Kodu</label>
                        <input type="text" name="kargo_kodu" id="kargo_kodu"
                            value="<?php echo isset($_POST['kargo_kodu']) ? htmlspecialchars($_POST['kargo_kodu']) : ''; ?>"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <p class="mt-1 text-sm text-gray-500">Kargo ile gönderim için takip kodunu giriniz</p>
                    </div>
                    
                    <div>
                        <label for="cihaz_turu" class="block text-sm font-medium text-gray-700">Cihaz Türü</label>
                        <select name="cihaz_turu" id="cihaz_turu" required 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Seçiniz</option>
                            <?php
                            $cihaz_turleri = ['Bilgisayar', 'Laptop', 'Tablet', 'Telefon', 'Yazıcı', 'Diğer'];
                            foreach ($cihaz_turleri as $tur) {
                                $selected = (isset($_POST['cihaz_turu']) && $_POST['cihaz_turu'] === $tur) ? 'selected' : '';
                                echo '<option value="' . htmlspecialchars($tur) . '" ' . $selected . '>' . htmlspecialchars($tur) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="marka_model" class="block text-sm font-medium text-gray-700">Marka ve Model</label>
                        <input type="text" name="marka_model" id="marka_model" required 
                            value="<?php echo isset($_POST['marka_model']) ? htmlspecialchars($_POST['marka_model']) : ''; ?>"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label for="sorun_aciklamasi" class="block text-sm font-medium text-gray-700">Sorun Açıklaması</label>
                        <textarea name="sorun_aciklamasi" id="sorun_aciklamasi" rows="4" required 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"><?php echo isset($_POST['sorun_aciklamasi']) ? htmlspecialchars($_POST['sorun_aciklamasi']) : ''; ?></textarea>
                    </div>

                    <div>
                        <label for="servis_oncesi_foto" class="block text-sm font-medium text-gray-700">Servise Gelmeden Önceki Fotoğraf</label>
                        <input type="file" name="servis_oncesi_foto" id="servis_oncesi_foto" accept="image/jpeg,image/png"
                            class="mt-1 block w-full text-sm text-gray-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-md file:border-0
                                file:text-sm file:font-semibold
                                file:bg-blue-50 file:text-blue-700
                                hover:file:bg-blue-100">
                        <p class="mt-1 text-sm text-gray-500">Cihazın mevcut durumunu gösteren bir fotoğraf yükleyin (JPEG veya PNG)</p>
                    </div>
                    
                    <div>
                    <button type="submit" style="background-color: #2563eb;" class="w-full text-black py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                     Gönder
                     </button>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>

    <script>
    function teslimatYontemiDegisti(yontem) {
        const adresAlani = document.getElementById('adres_alani');
        const adresInput = document.getElementById('adres');
        const kargoAlani = document.getElementById('kargo_alani');
        const kargoInput = document.getElementById('kargo_kodu');
        
        if (yontem === 'Adrese Servis') {
            adresAlani.style.display = 'block';
            kargoAlani.style.display = 'none';
            adresInput.required = true;
            kargoInput.required = false;
            kargoInput.value = '';
        } else if (yontem === 'Kargo') {
            adresAlani.style.display = 'none';
            kargoAlani.style.display = 'block';
            adresInput.required = false;
            kargoInput.required = true;
            adresInput.value = '';
        } else {
            adresAlani.style.display = 'none';
            kargoAlani.style.display = 'none';
            adresInput.required = false;
            kargoInput.required = false;
            adresInput.value = '';
            kargoInput.value = '';
        }
    }

    // Sayfa yüklendiğinde mevcut seçimi kontrol et
    document.addEventListener('DOMContentLoaded', function() {
        const teslimatSelect = document.getElementById('teslimat_yontemi');
        if (teslimatSelect.value) {
            teslimatYontemiDegisti(teslimatSelect.value);
        }
    });
    </script>
</body>
</html>