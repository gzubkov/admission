<?php
require_once('../../../modules/tcpdf/tcpdf.php');
require_once('../../../modules/fpdi/fpdi.php');
require_once('../../../modules/russian_date.php');
require_once('../../../modules/mysql.php');
require_once('../../conf.php');

$msl = new dMysql();

class PDF extends FPDI {
    function Header() {
        $this->setSourceFile('opd.pdf');
    }
    
    function Footer() {}
}



$applicant_id = $_REQUEST['applicant_id'];

// --- Базовый запрос (сведения об абитуриенте) --- //
$r = $msl->getarray("SELECT surname,name,second_name,regaddress,doc_serie,doc_number,doc_date,doc_issued FROM reg_applicant 
WHERE reg_applicant.id = ".$applicant_id.";");

// initiate PDF
$pdf = new PDF();
$pdf->SetMargins(PDF_MARGIN_LEFT, 40, 0);
$pdf->SetAutoPageBreak(true, 0);

// add a page
$pdf->AddPage();
$pdf->useTemplate($pdf->importPage(1));

$pdf->SetFont("times", "", 13);

$arr = splitstring($r['surname']." ".$r['name']." ".$r['second_name'], array(47,47));
$yo = 57.8;
foreach($arr as $v) {
    $pdf->Text(100.4, $yo, $v);
    $yo += 5.2;
}

$arr = splitstring($r['regaddress'], array(47,47,47));
$yo = 77.2;
foreach($arr as $v) {
    $pdf->Text(100.4, $yo, $v);
    $yo += 5.2;
}

$arr = splitstring($r['doc_serie']." ".$r['doc_number'].", выдан ".$r['doc_issued'].", ".date('d.m.y', strtotime($r['doc_date'])), array(43,43,43));
$yo = 100.6;
foreach($arr as $v) {
    $pdf->Text(100.4, $yo, $v);
    $yo += 5.2;
}

// add a page
$pdf->AddPage();
$pdf->useTemplate($pdf->importPage(2));

if ($r['second_name'] != "") {
   $pdf->Text(133.4, 164, $r['surname']." ".substr($r['name'],0,2).".".substr($r['second_name'],0,2).".");
} else {
   $pdf->Text(133.4, 164, $r['surname']." ".substr($r['name'],0,2).".");
}

$pdf->Output('opd.pdf', 'D');
?>
