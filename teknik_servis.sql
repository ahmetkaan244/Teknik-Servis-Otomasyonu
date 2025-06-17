-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 25 May 2025, 22:59:20
-- Sunucu sürümü: 10.4.32-MariaDB
-- PHP Sürümü: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `teknik_servis`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `email_onay_kodlari`
--

CREATE TABLE `email_onay_kodlari` (
  `id` int(11) NOT NULL,
  `talep_id` int(11) NOT NULL,
  `onay_kodu` varchar(6) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Tablo döküm verisi `email_onay_kodlari`
--

INSERT INTO `email_onay_kodlari` (`id`, `talep_id`, `onay_kodu`, `created_at`) VALUES
(15, 16, '315168', '2025-05-25 16:34:34'),
(16, 17, '169660', '2025-05-25 16:35:10');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `faturalar`
--

CREATE TABLE `faturalar` (
  `id` int(11) NOT NULL,
  `talep_id` int(11) NOT NULL,
  `fatura_no` varchar(15) NOT NULL,
  `fatura_tipi` enum('bireysel','kurumsal') NOT NULL,
  `vergi_no` varchar(20) NOT NULL,
  `fatura_adres` text NOT NULL,
  `islem_aciklamasi` text NOT NULL,
  `tutar` decimal(10,2) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `faturalar`
--

INSERT INTO `faturalar` (`id`, `talep_id`, `fatura_no`, `fatura_tipi`, `vergi_no`, `fatura_adres`, `islem_aciklamasi`, `tutar`, `created_at`) VALUES
(1, 17, '202505-0001', 'bireysel', '10094161754', 'asdscsvsd', 'silah çekildi', 500.00, '2025-05-25 19:46:18'),
(2, 17, '202505-0002', 'bireysel', '10094161754', 'asdscsvsd', 'silah çekildi', 500.00, '2025-05-25 19:48:27'),
(3, 17, '202505-0003', 'bireysel', '10094161754', 'asdscsvsd', '600', 221.00, '2025-05-25 19:51:21'),
(4, 17, '202505-0004', 'bireysel', '10094161754', 'asdscsvsd', 'silah çekildi', 500.00, '2025-05-25 23:08:48'),
(5, 17, '202505-0005', 'bireysel', '10094161754', 'asdscsvsd', 'silah çekildi', 500.00, '2025-05-25 23:09:06'),
(6, 17, '202505-0006', 'bireysel', '10094161754', 'asdscsvsd', 'silah çekildi', 500.00, '2025-05-25 23:09:22');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `servis_talepleri`
--

CREATE TABLE `servis_talepleri` (
  `id` int(11) NOT NULL,
  `musteri_adi` varchar(100) NOT NULL,
  `telefon` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `email_onaylandi` tinyint(1) DEFAULT 0,
  `adres` text DEFAULT NULL,
  `teslimat_yontemi` enum('Adrese Servis','Kargo') NOT NULL DEFAULT 'Adrese Servis',
  `kargo_kodu` varchar(50) DEFAULT NULL,
  `cihaz_turu` varchar(50) NOT NULL,
  `marka_model` varchar(100) NOT NULL,
  `sorun_aciklamasi` text NOT NULL,
  `servis_oncesi_foto` varchar(255) DEFAULT NULL,
  `servis_sonrasi_foto` varchar(255) DEFAULT NULL,
  `durum` enum('Beklemede','İşlemde','Tamamlandı','İptal') NOT NULL DEFAULT 'Beklemede',
  `onay_durumu` enum('Bekliyor','Onaylandı') NOT NULL DEFAULT 'Bekliyor',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Tablo döküm verisi `servis_talepleri`
--

INSERT INTO `servis_talepleri` (`id`, `musteri_adi`, `telefon`, `email`, `email_onaylandi`, `adres`, `teslimat_yontemi`, `kargo_kodu`, `cihaz_turu`, `marka_model`, `sorun_aciklamasi`, `servis_oncesi_foto`, `servis_sonrasi_foto`, `durum`, `onay_durumu`, `created_at`) VALUES
(16, 'İbrahim Atakan Erdem', '05317763110', 'ahmetkaanmialtan@gmail.com', 0, 'asdscsvsd', 'Adrese Servis', '', 'Diğer', 'asdsadsa', 'asfddsfdfsfsfdsf', 'servis_oncesi_6833469a707ab.jpg', NULL, 'İptal', 'Bekliyor', '2025-05-25 16:34:34'),
(17, 'İbrahim Atakan Erdem', '05317763110', 'ahmetkaanmialtan@gmail.com', 0, 'asdscsvsd', 'Adrese Servis', '', 'Diğer', 'asdsadsa', 'asfddsfdfsfsfdsf', 'servis_oncesi_683346be0a99f.jpg', 'servis_sonrasi_683349d252f5e.jpg', 'Tamamlandı', 'Onaylandı', '2025-05-25 16:35:10');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `yoneticiler`
--

CREATE TABLE `yoneticiler` (
  `id` int(11) NOT NULL,
  `kullanici_adi` varchar(50) NOT NULL,
  `sifre` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Tablo döküm verisi `yoneticiler`
--

INSERT INTO `yoneticiler` (`id`, `kullanici_adi`, `sifre`, `created_at`) VALUES
(1, 'admin', 'admin123', '2025-05-24 11:16:02');

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `email_onay_kodlari`
--
ALTER TABLE `email_onay_kodlari`
  ADD PRIMARY KEY (`id`),
  ADD KEY `talep_id` (`talep_id`);

--
-- Tablo için indeksler `faturalar`
--
ALTER TABLE `faturalar`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `fatura_no` (`fatura_no`),
  ADD KEY `talep_id` (`talep_id`);

--
-- Tablo için indeksler `servis_talepleri`
--
ALTER TABLE `servis_talepleri`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `yoneticiler`
--
ALTER TABLE `yoneticiler`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kullanici_adi` (`kullanici_adi`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `email_onay_kodlari`
--
ALTER TABLE `email_onay_kodlari`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Tablo için AUTO_INCREMENT değeri `faturalar`
--
ALTER TABLE `faturalar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Tablo için AUTO_INCREMENT değeri `servis_talepleri`
--
ALTER TABLE `servis_talepleri`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Tablo için AUTO_INCREMENT değeri `yoneticiler`
--
ALTER TABLE `yoneticiler`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `email_onay_kodlari`
--
ALTER TABLE `email_onay_kodlari`
  ADD CONSTRAINT `email_onay_kodlari_ibfk_1` FOREIGN KEY (`talep_id`) REFERENCES `servis_talepleri` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `faturalar`
--
ALTER TABLE `faturalar`
  ADD CONSTRAINT `faturalar_ibfk_1` FOREIGN KEY (`talep_id`) REFERENCES `servis_talepleri` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
