<?php
ob_start(); // Output buffer başlat
session_start();
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../vendor/tecnickcom/tcpdf/tcpdf.php';

// Oturum kontrolü
if(!isset($_SESSION['admin_id'])){
    header("location: giris.php");
    exit;
}

if(!isset($_GET['fatura_no'])){
    die("Fatura numarası belirtilmedi.");
}

$fatura_no = mysqli_real_escape_string($conn, $_GET['fatura_no']);

// Fatura bilgilerini al
$sql = "SELECT f.*, st.musteri_adi, st.telefon, st.email 
        FROM faturalar f
        JOIN servis_talepleri st ON f.talep_id = st.id
        WHERE f.fatura_no = ?";

if($stmt = mysqli_prepare($conn, $sql)){
    mysqli_stmt_bind_param($stmt, "s", $fatura_no);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $fatura = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    if(!$fatura){
        die("Fatura bulunamadı.");
    }
} else {
    die("Veritabanı hatası.");
}

// Çıktı tamponunu temizle
ob_clean();

// PDF oluştur
class MYPDF extends TCPDF {
    public function Header() {
        $this->SetFont('helvetica', 'B', 20);
        $this->Cell(0, 15, 'FATURA', 0, true, 'C', 0, '', 0, false, 'M', 'M');
        $this->SetFont('helvetica', '', 10);
        $this->Cell(0, 10, 'Teknik Servis', 0, true, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(10);
    }
    
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Sayfa '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

// PDF dokümanı oluştur
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Doküman bilgilerini ayarla
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Teknik Servis');
$pdf->SetTitle('Fatura - ' . $fatura_no);

// Varsayılan başlık ayarlarını kaldır
$pdf->setPrintHeader(true);
$pdf->setPrintFooter(true);

// Varsayılan kenar boşluklarını ayarla
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// Otomatik sayfa sonu
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// Font ayarları
$pdf->SetFont('helvetica', '', 10);

// Yeni sayfa ekle
$pdf->AddPage();

// Fatura içeriği
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'Fatura No: ' . $fatura_no, 0, 1, 'R');
$pdf->Cell(0, 10, 'Tarih: ' . date('d.m.Y', strtotime($fatura['created_at'])), 0, 1, 'R');
$pdf->Ln(10);

// Müşteri Bilgileri
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(0, 10, 'Müşteri Bilgileri:', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 7, 'Ad Soyad: ' . $fatura['musteri_adi'], 0, 1, 'L');
$pdf->Cell(0, 7, 'Vergi/TC No: ' . $fatura['vergi_no'], 0, 1, 'L');
$pdf->MultiCell(0, 7, 'Adres: ' . $fatura['fatura_adres'], 0, 'L');
$pdf->Cell(0, 7, 'Telefon: ' . $fatura['telefon'], 0, 1, 'L');
$pdf->Cell(0, 7, 'E-posta: ' . $fatura['email'], 0, 1, 'L');
$pdf->Ln(10);

// Hizmet Detayları
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(0, 10, 'Hizmet Detayları:', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);
$pdf->MultiCell(0, 7, $fatura['islem_aciklamasi'], 0, 'L');
$pdf->Ln(10);

// Tutar Bilgileri
$kdv_oran = 0.18;
$kdv_tutar = $fatura['tutar'] * $kdv_oran;
$toplam_tutar = $fatura['tutar'] + $kdv_tutar;

$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(120, 7, '', 0, 0);
$pdf->Cell(30, 7, 'Ara Toplam:', 0, 0, 'R');
$pdf->Cell(30, 7, number_format($fatura['tutar'], 2, ',', '.') . ' TL', 0, 1, 'R');

$pdf->Cell(120, 7, '', 0, 0);
$pdf->Cell(30, 7, 'KDV (%18):', 0, 0, 'R');
$pdf->Cell(30, 7, number_format($kdv_tutar, 2, ',', '.') . ' TL', 0, 1, 'R');

$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(120, 7, '', 0, 0);
$pdf->Cell(30, 7, 'Genel Toplam:', 0, 0, 'R');
$pdf->Cell(30, 7, number_format($toplam_tutar, 2, ',', '.') . ' TL', 0, 1, 'R');

// PDF'i indir
$pdf->Output('Fatura-' . $fatura_no . '.pdf', 'D');
?> 