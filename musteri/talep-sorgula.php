<?php
require_once '../config/config.php';
require_once '../config/database.php';

$mesaj = '';
$hata = '';
$talep = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $talep_no = mysqli_real_escape_string($conn, $_POST['talep_no']);
    $telefon = mysqli_real_escape_string($conn, $_POST['telefon']);
    
    $sql = "SELECT * FROM servis_talepleri WHERE id = ? AND telefon = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "is", $talep_no, $telefon);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            $talep = $row;
        } else {
            $hata = "Belirtilen talep numarası ve telefon numarası ile eşleşen kayıt bulunamadı.";
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
    <title>Talep Sorgula - Teknik Servis</title>
    <link rel="stylesheet" href="<?php echo url('assets/css/tailwind.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo url('assets/css/style.css'); ?>">
</head>
<body class="flex flex-col min-h-full bg-gray-100">
    <?php include '../includes/header.php'; ?>
    
    <main class="flex-grow container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto">
            <?php if (!$talep): ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Talep Sorgula</h2>
                    
                    <?php if ($hata): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <?php echo $hata; ?>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" class="space-y-6">
                        <div>
                            <label for="talep_no" class="block text-sm font-medium text-gray-700">Talep Numarası</label>
                            <input type="number" name="talep_no" id="talep_no" required 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label for="telefon" class="block text-sm font-medium text-gray-700">Telefon Numarası</label>
                            <input type="tel" name="telefon" id="telefon" required 
                                placeholder="05XX XXX XX XX"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        
                        <div>
                        <button type="submit" style="background-color: #2563eb;" class="w-full text-black py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                               Sorgula
                         </button>
                        </div>
                    </form>
                </div>
            </div>
            <?php else: ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">Talep Detayları</h2>
                        <a href="<?php echo url('musteri/talep-sorgula.php'); ?>" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Yeni Sorgu
                        </a>
                    </div>
                    
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Talep Numarası</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo $talep['id']; ?></dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Durum</dt>
                            <dd class="mt-1">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    <?php 
                                    echo match($talep['durum']) {
                                        'Beklemede' => 'bg-yellow-100 text-yellow-800',
                                        'İşlemde' => 'bg-blue-100 text-blue-800',
                                        'Tamamlandı' => 'bg-green-100 text-green-800',
                                        default => 'bg-red-100 text-red-800'
                                    };
                                    ?>">
                                    <?php echo htmlspecialchars($talep['durum']); ?>
                                </span>
                            </dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Müşteri Adı</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($talep['musteri_adi']); ?></dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Telefon</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($talep['telefon']); ?></dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">E-posta</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($talep['email']); ?></dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Teslimat Yöntemi</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($talep['teslimat_yontemi']); ?></dd>
                        </div>

                        <?php if($talep['teslimat_yontemi'] === 'Kargo' && !empty($talep['kargo_kodu'])): ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Kargo Takip Kodu</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($talep['kargo_kodu']); ?></dd>
                        </div>
                        <?php endif; ?>

                        <?php if($talep['teslimat_yontemi'] === 'Adrese Servis'): ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Adres</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo nl2br(htmlspecialchars($talep['adres'])); ?></dd>
                        </div>
                        <?php endif; ?>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Cihaz Türü</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($talep['cihaz_turu']); ?></dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Marka/Model</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($talep['marka_model']); ?></dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Sorun Açıklaması</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo nl2br(htmlspecialchars($talep['sorun_aciklamasi'])); ?></dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Oluşturulma Tarihi</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo date('d.m.Y H:i', strtotime($talep['created_at'])); ?></dd>
                        </div>

                        <?php if($talep['servis_oncesi_foto']): ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Servise Gelmeden Önceki Fotoğraf</dt>
                            <dd class="mt-1">
                                <img src="<?php echo url('uploads/servis_foto/' . $talep['servis_oncesi_foto']); ?>" 
                                    alt="Servis öncesi fotoğraf" 
                                    class="max-w-xs rounded-lg shadow-sm">
                            </dd>
                        </div>
                        <?php endif; ?>

                        <?php if($talep['servis_sonrasi_foto']): ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Servisten Çıktıktan Sonraki Fotoğraf</dt>
                            <dd class="mt-1">
                                <img src="<?php echo url('uploads/servis_foto/' . $talep['servis_sonrasi_foto']); ?>" 
                                    alt="Servis sonrası fotoğraf" 
                                    class="max-w-xs rounded-lg shadow-sm">
                            </dd>
                        </div>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html> 