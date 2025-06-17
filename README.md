# Teknik-Servis-Otomasyonu
Teknik servis işlemlerinin yönetilmesi ve takibi için geliştirilmiş bir web uygulamasıdır.
Kullanıcılar servis taleplerini oluşturabilir, taleplerinin durumunu sorgulayabilir ve servis sonrası raporları görüntüleyebilirler.
Yönetici paneli üzerinden talepler yönetilebilir, fatura oluşturulabilir ve PDF olarak indirilebilir.

Özellikler
Kullanıcılar için servis talebi oluşturma sorgulama 
Yönetici paneli ile talepleri onaylama düzenleme ve faturalandırma Fatura PDF çıktısı oluşturma 
Servis öncesi ve sonrası fotoğraf yükleme E-posta ile bilgilendirme (PHPMailer ile) Modern ve responsive arayüz (Tailwind CSS ile)

Kurulum 
Projeyi klonlayın:
git clone https://github.com/ahmetkaan244/Teknik-Servis-Otomasyonu.git
Gerekli PHP uzantılarını ve bağımlılıklarını yükleyin.
config/database.php dosyasını kendi veritabanı bilgilerinizle güncelleyin.
config/mail.php dosyasında $mail->Username = 'gmail adresi'; 
$mail->Password = 'gmail 6 haneli uygulama şifresi';  //Gmail şifresi 6 haneli uygulama şifresini girin
 $mail->setFrom('gmail adresi', 'Teknik Servis'); // gmailadresini yazın
teknik_servis.sql dosyasını veritabanınıza aktarın.
Web sunucunuzda projeyi çalıştırın.
Kullanılan Teknolojiler
PHP
MySQL
PHPMailer
TCPDF
Tailwind CSS
