<?php
require_once('../../../modules/russian_date.php');
require_once('../../../modules/mysql.php');
require_once('../class/catalog.class.php');
require_once('../class/pdf.class.php');
require_once('../../conf.php');

$msl = new dMysql();

$applicant_id = $_REQUEST['applicant_id'];

// --- Базовый запрос (сведения об абитуриенте) --- //
$r = $msl->getarray("SELECT surname,name,second_name FROM reg_applicant 
WHERE reg_applicant.id = ".$applicant_id.";");

// initiate PDF
$pdf = new PDF();
$pdf->SetMargins(PDF_MARGIN_LEFT, 40, 0);
$pdf->SetAutoPageBreak(true, 0);
$pdf->setSourceFile('diplom.pdf');

// add a page
$pdf->AddPage();
$pdf->useTemplate($pdf->importPage(1));

$pdf->SetFont("times", "", 13);

$pdf->Text(110, 45, $r['surname']);
$pdf->Text(110, 53.6, $r['name']." ".$r['second_name']);

$rval = getarray("SELECT catalog FROM reg_request WHERE applicant_id='".$applicant_id."' LIMIT 1;", 0);
$cat = new Catalog();
$spc = $cat->getInfo($rval['catalog']);

$pdf->SetFont("times", "", 12);
$pdf->Text(109.8, 62.4, $spc['type']);

$pdf->SetFont("times", "", 13);

$pdf->splitText($spc['name'], array(array(110,69.4),array(110,76.4)), 50, 1);

$rval = $msl->getarray("SELECT b.name_rp,serie,number,institution,date FROM reg_applicant_edu_doc a LEFT JOIN reg_edu_doc b ON a.edu_doc=b.id WHERE applicant='".$applicant_id."' AND `primary`='1'");

$pdf->SetFont("times", "", 14);

$pdf->splitText($rval['name_rp'], array(array(115,111.2),array(30,123.6)),array(30,135.6), 1);

$pdf->Text(119.8, 135.6, $rval['serie']);
$pdf->Text(160.5, 135.6, $rval['number']);

$pdf->splitText($rval['institution'].", ".mb_strtolower(russian_date( strtotime($rval['date']), 'j F Y' ), 'UTF-8'), array(array(54,144.2),array(30,156.2)), 60, 1);

$pdf->Output('zayavl.pdf', 'D');
?>
