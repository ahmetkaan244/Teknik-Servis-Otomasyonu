<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Config dosyasını yükle
require_once __DIR__ . '/config/config.php';

// Debug bilgisi
if (DEBUG) {
    error_log("Index Debug: Document Root = " . $_SERVER['DOCUMENT_ROOT']);
    error_log("Index Debug: Script Name = " . $_SERVER['SCRIPT_NAME']);
    error_log("Index Debug: PHP_SELF = " . $_SERVER['PHP_SELF']);
    error_log("Index Debug: SITE_URL = " . SITE_URL);
}
?>
<!DOCTYPE html>
<html lang="tr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teknik Servis - Ana Sayfa</title>
    <!-- Debug bilgisi -->
    <?php
    echo "<!-- Site URL: " . SITE_URL . " -->\n";
    echo "<!-- Current Path: " . dirname($_SERVER['PHP_SELF']) . " -->\n";
    ?>
    <!-- CSS dosyaları -->
    <link rel="stylesheet" href="<?php echo url('assets/css/tailwind.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo url('assets/css/swiper-bundle.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo url('assets/css/style.css'); ?>">
    
    <style>
        .swiper-button-next,
        .swiper-button-prev {
            color: #fff !important;
            background: rgba(0, 0, 0, 0.5);
            width: 50px !important;
            height: 50px !important;
            border-radius: 25px;
        }
        
        .swiper-button-next:after,
        .swiper-button-prev:after {
            font-size: 20px !important;
        }
        
        .swiper-pagination-bullet {
            width: 12px !important;
            height: 12px !important;
            background: #fff !important;
            opacity: 0.7;
        }
        
        .swiper-pagination-bullet-active {
            opacity: 1;
            background: #fff !important;
        }

        .heroSwiper {
            width: 100%;
            height: 500px;
        }
    </style>
</head>
<body class="flex flex-col min-h-full">
    <?php include 'includes/header.php'; ?>
    
    <!-- Hero Section with Slider -->
    <div class="swiper heroSwiper">
        <div class="swiper-wrapper">
            <!-- Slide 1 -->
            <div class="swiper-slide relative">
                <img src="<?php echo url('assets/images/slider/slider1.jpg'); ?>" alt="Teknik Servis" class="w-full h-[500px] object-cover">
                <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center">
                    <div class="text-center text-white">
                        <h1 class="text-4xl md:text-6xl font-bold mb-4 drop-shadow-lg">Profesyonel Teknik Servis</h1>
                        <p class="text-xl md:text-2xl mb-8 drop-shadow-lg">Uzman ekibimizle tüm cihazlarınız güvende</p>
                        <a href="musteri/servis-talebi.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-lg transition duration-300 shadow-lg">
                            Servis Talebi Oluştur
                        </a>
                    </div>
                </div>
            </div>
            <!-- Slide 2 -->
            <div class="swiper-slide relative">
                <img src="<?php echo url('assets/images/slider/slider2.jpg'); ?>" alt="Bilgisayar Tamiri" class="w-full h-[500px] object-cover">
                <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center">
                    <div class="text-center text-white">
                        <h2 class="text-4xl md:text-6xl font-bold mb-4 drop-shadow-lg">Bilgisayar Tamiri</h2>
                        <p class="text-xl md:text-2xl mb-8 drop-shadow-lg">Hızlı ve güvenilir bilgisayar tamir hizmeti</p>
                        <a href="musteri/servis-talebi.php" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-8 rounded-lg transition duration-300 shadow-lg">
                            Hemen Başvur
                        </a>
                    </div>
                </div>
            </div>
            <!-- Slide 3 -->
            <div class="swiper-slide relative">
                <img src="<?php echo url('assets/images/slider/slider3.jpg'); ?>" alt="Elektronik Tamir" class="w-full h-[500px] object-cover">
                <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center">
                    <div class="text-center text-white">
                        <h2 class="text-4xl md:text-6xl font-bold mb-4 drop-shadow-lg">Elektronik Cihaz Tamiri</h2>
                        <p class="text-xl md:text-2xl mb-8 drop-shadow-lg">Tüm elektronik cihazlarınız için profesyonel çözümler</p>
                        <a href="musteri/servis-talebi.php" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-8 rounded-lg transition duration-300 shadow-lg">
                            Servis Randevusu Al
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="swiper-pagination"></div>
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
    </div>

    <!-- Features Section -->
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="text-center p-6">
                    <div class="w-16 h-16 mx-auto mb-4 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Profesyonel Ekip</h3>
                    <p class="text-gray-600">Uzman teknisyenlerimizle kaliteli hizmet garantisi</p>
                </div>
                <!-- Feature 2 -->
                <div class="text-center p-6">
                    <div class="w-16 h-16 mx-auto mb-4 bg-green-100 rounded-full flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Hızlı Çözüm</h3>
                    <p class="text-gray-600">Aynı gün tespit ve hızlı tamir hizmeti</p>
                </div>
                <!-- Feature 3 -->
                <div class="text-center p-6">
                    <div class="w-16 h-16 mx-auto mb-4 bg-purple-100 rounded-full flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Güvenilir Hizmet</h3>
                    <p class="text-gray-600">Garantili ve şeffaf tamir süreci</p>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="<?php echo url('assets/js/swiper-bundle.min.js'); ?>"></script>
    <script>
        const swiper = new Swiper('.heroSwiper', {
            loop: true,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
        });
    </script>
</body>
</html> 