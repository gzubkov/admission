<?php
// just require TCPDF instead of FPDF
require_once('../../../modules/tcpdf/tcpdf.php');
require_once('../../../modules/fpdi/fpdi.php');
require_once('../../../modules/russian_date.php');
require_once('../../../modules/mysql.php');
require_once('../class/catalog.class.php');
require_once('../../conf.php');

$msl = new dMysql();

class PDF extends FPDI {
    function Header() {
        $this->setSourceFile('perez.pdf');
    }
    
    function Footer() {}
}



$applicant_id = $_REQUEST['applicant_id'];

// --- Базовый запрос (сведения об абитуриенте) --- //
$r = $msl->getarray("SELECT surname,name,second_name FROM reg_applicant 
WHERE reg_applicant.id = ".$applicant_id.";");

// initiate PDF
$pdf = new PDF();
$pdf->SetMargins(PDF_MARGIN_LEFT, 40, 0);
$pdf->SetAutoPageBreak(true, 0);

// add a page
$pdf->AddPage();
$pdf->useTemplate($pdf->importPage(1));

$pdf->SetFont("times", "", 13);

$pdf->Text(112, 42.8, $r['surname']);
$pdf->Text(107, 53.2, $r['name']." ".$r['second_name']);

$pdf->Output('perez.pdf', 'D');
?>
