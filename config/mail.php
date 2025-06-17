<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

function send_mail($to, $subject, $text_message, $html_message = '') {
    try {
        $mail = new PHPMailer(true);
        
        // Debug modu aktif
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        $mail->Debugoutput = function($str, $level) {
            error_log("PHPMailer: " . $str);
        };

        // SMTP ayarları
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = '';  //Gmail adresi
        $mail->Password = '';  //Gmail şifresi 6 haneli uygulama şifresi
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // Genel ayarlar
        $mail->CharSet = 'UTF-8';
        $mail->setFrom('', 'Teknik Servis'); //Gmail adresi
        
        // Alıcı ayarları
        $mail->clearAddresses();
        $mail->addAddress($to);
        $mail->Subject = $subject;
        
        // HTML ve düz metin içerik ayarları
        $mail->isHTML(true);
        $mail->Body = $html_message;
        $mail->AltBody = $text_message;
        
        error_log("Mail gönderme başlatılıyor - Alıcı: " . $to);
        $sonuc = $mail->send();
        error_log("Mail gönderme sonucu: " . ($sonuc ? "Başarılı" : "Başarısız"));
        
        return $sonuc;
    } catch (Exception $e) {
        error_log("Mail gönderme hatası: " . $e->getMessage());
        throw new Exception("Mail gönderilemedi: " . $e->getMessage());
    }
}
?> 