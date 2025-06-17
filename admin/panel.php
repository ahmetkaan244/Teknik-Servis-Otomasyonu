<?php
session_start();
require_once '../config/config.php';
require_once '../config/database.php';

// Oturum kontrolü
if(!isset($_SESSION['admin_id'])){
    header("location: giris.php");
    exit;
}

// Durum güncelleme işlemi
if(isset($_POST['talep_id']) && isset($_POST['yeni_durum'])){
    $talep_id = mysqli_real_escape_string($conn, $_POST['talep_id']);
    $yeni_durum = mysqli_real_escape_string($conn, $_POST['yeni_durum']);
    
    // Önce mevcut talebi kontrol et
    $check_sql = "SELECT teslimat_yontemi, kargo_kodu, email, musteri_adi, cihaz_turu, marka_model FROM servis_talepleri WHERE id = ?";
    if($check_stmt = mysqli_prepare($conn, $check_sql)){
        mysqli_stmt_bind_param($check_stmt, "i", $talep_id);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        $talep_bilgi = mysqli_fetch_assoc($check_result);
        mysqli_stmt_close($check_stmt);
        
        // Kargo ile teslimat ve işlem tamamlanıyor ise kargo kodu kontrolü yap
        if($talep_bilgi['teslimat_yontemi'] === 'Kargo' && $yeni_durum === 'Tamamlandı' && empty($talep_bilgi['kargo_kodu'])){
            $hata = "Kargo ile teslimat için kargo takip kodu girilmesi zorunludur.";
        } else {
            $sql = "UPDATE servis_talepleri SET durum = ? WHERE id = ?";
            if($stmt = mysqli_prepare($conn, $sql)){
                mysqli_stmt_bind_param($stmt, "si", $yeni_durum, $talep_id);
                if(mysqli_stmt_execute($stmt)){
                    // Eğer durum "Tamamlandı" olarak değiştirildiyse email gönder
                    if($yeni_durum === 'Tamamlandı'){
                        require_once '../config/mail.php';
                        
                        $to = $talep_bilgi['email'];
                        $subject = "Servis Talebiniz Tamamlandı - #" . $talep_id;
                        
                        $message = "Sayın " . htmlspecialchars($talep_bilgi['musteri_adi']) . ",\n\n";
                        $message .= "Servis talebiniz başarıyla tamamlanmıştır.\n\n";
                        $message .= "Talep Detayları:\n";
                        $message .= "Talep No: #" . $talep_id . "\n";
                        $message .= "Cihaz: " . htmlspecialchars($talep_bilgi['cihaz_turu']) . "\n";
                        $message .= "Marka/Model: " . htmlspecialchars($talep_bilgi['marka_model']) . "\n";
                        
                        if($talep_bilgi['teslimat_yontemi'] === 'Kargo'){
                            $message .= "Kargo Takip Kodu: " . htmlspecialchars($talep_bilgi['kargo_kodu']) . "\n";
                        }
                        
                        $message .= "\nBizi tercih ettiğiniz için teşekkür ederiz.\n";
                        $message .= "Teknik Servis Ekibi";
                        
                        // HTML formatında email
                        $html_message = "
                        <html>
                        <head>
                            <style>
                                body { font-family: Arial, sans-serif; line-height: 1.6; }
                                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                                .header { background-color: #2563eb; color: white; padding: 20px; text-align: center; }
                                .content { padding: 20px; background-color: #f8f9fa; }
                                .footer { text-align: center; padding: 20px; color: #666; }
                                .details { background-color: white; padding: 15px; margin: 15px 0; border-radius: 5px; }
                            </style>
                        </head>
                        <body>
                            <div class='container'>
                                <div class='header'>
                                    <h2>Servis Talebiniz Tamamlandı</h2>
                                </div>
                                <div class='content'>
                                    <p>Sayın " . htmlspecialchars($talep_bilgi['musteri_adi']) . ",</p>
                                    <p>Servis talebiniz başarıyla tamamlanmıştır.</p>
                                    
                                    <div class='details'>
                                        <h3>Talep Detayları:</h3>
                                        <p><strong>Talep No:</strong> #" . $talep_id . "</p>
                                        <p><strong>Cihaz:</strong> " . htmlspecialchars($talep_bilgi['cihaz_turu']) . "</p>
                                        <p><strong>Marka/Model:</strong> " . htmlspecialchars($talep_bilgi['marka_model']) . "</p>";
                        
                        if($talep_bilgi['teslimat_yontemi'] === 'Kargo'){
                            $html_message .= "<p><strong>Kargo Takip Kodu:</strong> " . htmlspecialchars($talep_bilgi['kargo_kodu']) . "</p>";
                        }
                        
                        $html_message .= "
                                    </div>
                                    <p>Bizi tercih ettiğiniz için teşekkür ederiz.</p>
                                </div>
                                <div class='footer'>
                                    <p>Teknik Servis Ekibi</p>
                                </div>
                            </div>
                        </body>
                        </html>";
                        
                        // Email gönderme
                        send_mail($to, $subject, $message, $html_message);
                    }
                }
                mysqli_stmt_close($stmt);
            }
        }
    }
}

// Kargo kodu güncelleme işlemi
if(isset($_POST['talep_id']) && isset($_POST['kargo_kodu'])){
    $talep_id = mysqli_real_escape_string($conn, $_POST['talep_id']);
    $kargo_kodu = mysqli_real_escape_string($conn, $_POST['kargo_kodu']);
    
    $sql = "UPDATE servis_talepleri SET kargo_kodu = ? WHERE id = ?";
    if($stmt = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($stmt, "si", $kargo_kodu, $talep_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

// Servis sonrası fotoğraf yükleme işlemi
if(isset($_FILES['servis_sonrasi_foto']) && isset($_POST['foto_talep_id'])){
    $talep_id = mysqli_real_escape_string($conn, $_POST['foto_talep_id']);
    
    if($_FILES['servis_sonrasi_foto']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/servis_foto/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['servis_sonrasi_foto']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = array('jpg', 'jpeg', 'png');
        
        if (in_array($file_extension, $allowed_extensions)) {
            $unique_filename = uniqid('servis_sonrasi_') . '.' . $file_extension;
            $target_file = $upload_dir . $unique_filename;
            
            if (move_uploaded_file($_FILES['servis_sonrasi_foto']['tmp_name'], $target_file)) {
                $sql = "UPDATE servis_talepleri SET servis_sonrasi_foto = ? WHERE id = ?";
                if($stmt = mysqli_prepare($conn, $sql)){
                    mysqli_stmt_bind_param($stmt, "si", $unique_filename, $talep_id);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                }
            }
        }
    }
}

// Talepleri listele
$sql = "SELECT st.*, 
        CASE 
            WHEN st.onay_durumu = 'Onaylandı' THEN 1 
            ELSE 0 
        END as email_onaylandi,
        CASE 
            WHEN st.onay_durumu = 'Onaylandı' THEN 'Onaylandı'
            WHEN st.onay_durumu = 'Beklemede' THEN 'Beklemede'
            ELSE 'Onaylanmadı'
        END as email_durum
        FROM servis_talepleri st 
        ORDER BY st.created_at DESC";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Veritabanı hatası: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yönetici Paneli - Teknik Servis</title>
    <link rel="stylesheet" href="<?php echo url('assets/css/tailwind.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo url('assets/css/style.css'); ?>">
    <style>
        /* Modal stilleri */
        .modal {
            display: none;
        }
        .modal.show {
            display: block;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <?php 
    $admin_header = true;
    include '../includes/header.php'; 
    ?>
    
    <main class="flex-grow">
        <div class="max-w-7xl mx-auto px-4 py-6">
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Talep No</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Müşteri Adı</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cihaz Türü</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Marka/Model</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">E-posta Onayı</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tarih</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $row['id']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['musteri_adi']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['cihaz_turu']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['marka_model']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <form method="POST" action="" class="flex items-center space-x-2">
                                        <input type="hidden" name="talep_id" value="<?php echo $row['id']; ?>">
                                        <select name="yeni_durum" 
                                            onchange="this.form.submit()"
                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm
                                            <?php 
                                            echo match($row['durum']) {
                                                'Beklemede' => 'bg-yellow-50 text-yellow-800',
                                                'İşlemde' => 'bg-blue-50 text-blue-800',
                                                'Tamamlandı' => 'bg-green-50 text-green-800',
                                                'İptal' => 'bg-red-50 text-red-800',
                                                default => 'bg-gray-50 text-gray-800'
                                            };
                                            ?>">
                                            <option value="Beklemede" <?php echo $row['durum'] == 'Beklemede' ? 'selected' : ''; ?>>Beklemede</option>
                                            <option value="İşlemde" <?php echo $row['durum'] == 'İşlemde' ? 'selected' : ''; ?>>İşlemde</option>
                                            <option value="Tamamlandı" <?php echo $row['durum'] == 'Tamamlandı' ? 'selected' : ''; ?>>Tamamlandı</option>
                                            <option value="İptal" <?php echo $row['durum'] == 'İptal' ? 'selected' : ''; ?>>İptal</option>
                                        </select>
                                    </form>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if($row['email_onaylandi']): ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            <?php echo $row['email_durum']; ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $row['email_durum'] == 'Beklemede' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'; ?>">
                                            <?php echo $row['email_durum']; ?>
                                    </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo date('d.m.Y H:i', strtotime($row['created_at'])); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <button type="button" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" onclick="openModal('talepDetay<?php echo $row['id']; ?>')">
                                        Detaylar
                                    </button>
                                </td>
                            </tr>
                            
                            <!-- Talep Detay Modal -->
                            <div id="talepDetay<?php echo $row['id']; ?>" class="fixed inset-0 bg-gray-500 bg-opacity-75 overflow-y-auto">
                                <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                    <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                                        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                                    </div>
                                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                                    <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                        <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                                            <div class="flex items-center justify-between">
                                                <h3 class="text-lg font-medium text-gray-900">Talep Detayı #<?php echo $row['id']; ?></h3>
                                                <button type="button" class="text-gray-400 hover:text-gray-500" onclick="closeModal('talepDetay<?php echo $row['id']; ?>')">
                                                    <span class="sr-only">Kapat</span>
                                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 max-h-[calc(100vh-200px)] overflow-y-auto">
                                            <dl class="space-y-4">
                                                <div>
                                                    <dt class="text-sm font-medium text-gray-500">Müşteri Adı</dt>
                                                    <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($row['musteri_adi']); ?></dd>
                                                </div>
                                                <div>
                                                    <dt class="text-sm font-medium text-gray-500">Telefon</dt>
                                                    <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($row['telefon']); ?></dd>
                                                </div>
                                                <div>
                                                    <dt class="text-sm font-medium text-gray-500">E-posta</dt>
                                                    <dd class="mt-1 text-sm text-gray-900">
                                                        <?php echo htmlspecialchars($row['email']); ?>
                                                        <?php if($row['email_onaylandi']): ?>
                                                            <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Onaylı</span>
                                                        <?php else: ?>
                                                            <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Onaylanmadı</span>
                                                        <?php endif; ?>
                                                    </dd>
                                                </div>
                                                <div>
                                                    <dt class="text-sm font-medium text-gray-500">Teslimat Yöntemi</dt>
                                                    <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($row['teslimat_yontemi']); ?></dd>
                                                </div>

                                                <?php if($row['teslimat_yontemi'] === 'Kargo'): ?>
                                                <div>
                                                    <dt class="text-sm font-medium text-gray-500">Kargo Takip Kodu</dt>
                                                    <dd class="mt-1">
                                                        <form method="POST" action="" class="flex gap-2">
                                                            <input type="hidden" name="talep_id" value="<?php echo $row['id']; ?>">
                                                            <input type="text" name="kargo_kodu" 
                                                                value="<?php echo htmlspecialchars($row['kargo_kodu'] ?? ''); ?>"
                                                                placeholder="Kargo takip kodu girin"
                                                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                            <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                                Kaydet
                                                            </button>
                                                        </form>
                                                    </dd>
                                                </div>
                                                <?php endif; ?>

                                                <?php if($row['teslimat_yontemi'] === 'Adrese Servis'): ?>
                                                <div>
                                                    <dt class="text-sm font-medium text-gray-500">Adres</dt>
                                                    <dd class="mt-1 text-sm text-gray-900"><?php echo nl2br(htmlspecialchars($row['adres'])); ?></dd>
                                                </div>
                                                <?php endif; ?>

                                                <div>
                                                    <dt class="text-sm font-medium text-gray-500">Servis Öncesi Fotoğraf</dt>
                                                    <dd class="mt-1">
                                                        <?php if($row['servis_oncesi_foto']): ?>
                                                            <img src="<?php echo url('uploads/servis_foto/' . $row['servis_oncesi_foto']); ?>" 
                                                                alt="Servis öncesi fotoğraf" 
                                                                class="max-w-xs rounded-lg shadow-sm">
                                                        <?php else: ?>
                                                            <span class="text-sm text-gray-500">Fotoğraf yüklenmemiş</span>
                                                        <?php endif; ?>
                                                    </dd>
                                                </div>

                                                <div>
                                                    <dt class="text-sm font-medium text-gray-500">Servis Sonrası Fotoğraf</dt>
                                                    <dd class="mt-1">
                                                        <?php if($row['servis_sonrasi_foto']): ?>
                                                            <img src="<?php echo url('uploads/servis_foto/' . $row['servis_sonrasi_foto']); ?>" 
                                                                alt="Servis sonrası fotoğraf" 
                                                                class="max-w-xs rounded-lg shadow-sm">
                                                        <?php else: ?>
                                                            <form method="POST" action="" enctype="multipart/form-data" class="space-y-2">
                                                                <input type="hidden" name="foto_talep_id" value="<?php echo $row['id']; ?>">
                                                                <input type="file" name="servis_sonrasi_foto" accept="image/jpeg,image/png"
                                                                    class="block w-full text-sm text-gray-500
                                                                        file:mr-4 file:py-2 file:px-4
                                                                        file:rounded-md file:border-0
                                                                        file:text-sm file:font-semibold
                                                                        file:bg-indigo-50 file:text-indigo-700
                                                                        hover:file:bg-indigo-100">
                                                                <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                                    Fotoğraf Yükle
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                    </dd>
                                                </div>

                                                <!-- Fatura Oluşturma Bölümü -->
                                                <div class="border-t pt-4 mt-4">
                                                    <dt class="text-sm font-medium text-gray-500">Fatura Bilgileri</dt>
                                                    <dd class="mt-1">
                                                        <form method="POST" action="fatura_olustur.php" class="space-y-4">
                                                <input type="hidden" name="talep_id" value="<?php echo $row['id']; ?>">
                                                            
                                                            <div>
                                                                <label class="block text-sm font-medium text-gray-700">
                                                                    Fatura Tipi
                                                                </label>
                                                                <select name="fatura_tipi" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                                    <option value="bireysel">Bireysel</option>
                                                                    <option value="kurumsal">Kurumsal</option>
                                                    </select>
                                                            </div>

                                                            <div>
                                                                <label class="block text-sm font-medium text-gray-700">
                                                                    Vergi/TC Kimlik No
                                                                </label>
                                                                <input type="text" name="vergi_no" 
                                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                                    required>
                                                            </div>

                                                            <div>
                                                                <label class="block text-sm font-medium text-gray-700">
                                                                    Fatura Adresi
                                                                </label>
                                                                <textarea name="fatura_adres" rows="3" 
                                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                                    required><?php echo htmlspecialchars($row['adres']); ?></textarea>
                                                            </div>

                                                            <div>
                                                                <label class="block text-sm font-medium text-gray-700">
                                                                    Yapılan İşlem
                                                                </label>
                                                                <input type="text" name="islem_aciklamasi" 
                                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                                    required>
                                                            </div>

                                                            <div>
                                                                <label class="block text-sm font-medium text-gray-700">
                                                                    Tutar (TL)
                                                                </label>
                                                                <input type="number" name="tutar" step="0.01" 
                                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                                    required>
                                                            </div>

                                                            <div>
                                                                <button type="submit" 
                                                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                                    Fatura Oluştur
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </dd>
                                                </div>
                                            </dl>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
    
    <script>
    function openModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
    }
    
    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }

    function durumDegisti(yeniDurum, teslimatYontemi, talepId) {
        if (yeniDurum === 'Tamamlandı' && teslimatYontemi === 'Kargo') {
            const kargoKodu = document.querySelector(`input[name="kargo_kodu"][data-talep-id="${talepId}"]`).value;
            if (!kargoKodu) {
                alert('Kargo ile teslimat için kargo takip kodu girilmesi zorunludur!');
                return false;
            }
        }
        return true;
    }
    </script>
</body>
</html> 