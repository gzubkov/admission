<?php
require_once('../../../modules/russian_date.php');
require_once('../class/mysql.class.php');
require_once('../class/catalog.class.php');
require_once('../class/pdf.class.php');
require_once('../class/documents.class.php');
require_once('../../conf.php');

$msl = new dMysql();
$appl = new Applicant($msl, $_REQUEST['applicant_id']);

$applicant_id = $_REQUEST['applicant_id'];

$pdf = new PDF('pdf/diplom.pdf');

$pdf->SetFont("times", "", 13);
$pdf->Text(110, 45, $appl->surname);
$pdf->Text(110, 53.6, $appl->name." ".$appl->second_name);

$cat = new Catalog(&$msl);
$spc = $cat->getInfo($appl->catalog);
$univ = $cat->getUniversityInfo($appl->catalog);

$pdf->SetFont("times", "", 12);
$pdf->SetXY(70, 23.3);
//$pdf->MultiCell(125,30, $univ['type']." «".$univ['name']."» ".$univ['rsurname_rp']." ".substr($univ['rname_rp'],0,2).". ".substr($univ['rsecond_name_rp'],0,2).".", 0, 'R');


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
