<?php
require_once('../../../modules/russian_date.php');
require_once('../class/mysql.class.php');
require_once('../class/pdf.class.php');
require_once('../../conf.php');

$msl = new dMysql();

$applicant_id = $_REQUEST['applicant_id'];

// --- Базовый запрос (сведения об абитуриенте) --- //
$r = $msl->getarray("SELECT surname,name,second_name,regaddress,doc_serie,doc_number,doc_date,doc_issued FROM reg_applicant 
WHERE reg_applicant.id = ".$applicant_id.";");

// initiate PDF
$pdf = new PDF();
$pdf->SetMargins(PDF_MARGIN_LEFT, 40, 0);
$pdf->SetAutoPageBreak(true, 0);
$pdf->setSourceFile('opd.pdf');

// add a page
$pdf->AddPage();
$pdf->useTemplate($pdf->importPage(1));

$pdf->SetFont("times", "", 13);
$pdf->splitText($r['surname']." ".$r['name']." ".$r['second_name'], array(array(100.4,57.8),array(100.4,63),array(100.4,68.2)), array(47,47), 1);
$pdf->splitText($r['regaddress'], array(array(100.4,77.2),array(100.4,82.4),array(100.4,87.6)), array(47,47), 1);
$pdf->splitText($r['doc_serie']." ".$r['doc_number'].", выдан ".$r['doc_issued'].", ".date('d.m.Y', strtotime($r['doc_date'])), array(array(100.4,100.6),array(100.4,105.8),array(100.4,111)), array(43,43), 1);

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
