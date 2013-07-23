<?php
require_once('../../conf.php');
require_once('../class/mysql.class.php');
require_once('../class/price.class.php');
require_once('../class/catalog.class.php');
require_once('../class/pdf.class.php');
require_once('../class/documents.class.php');
$msl = new dMysql();

$appl = new Applicant($msl, $_REQUEST['applicant']);
$r = $appl->getInfo();

$pdf = new PDF('pdf/dog_ckt_s.pdf');

$pdf->SetFont("times", "I", 12);
$pdf->Text(44, 87.8, $appl->surname." ".$appl->name." ".$appl->second_name);

$cat = new Catalog($msl);
$rval = $cat->getInfo($appl->catalog, $appl->profile);

$price = new Price($msl);
$rval['price'] = $price->getPriceByRegion($r['region'],$appl->catalog, 1, 1, 0, 0, 1);

unset($msl);
unset($price);
unset($cat);


$pdf->Text(52, 127.4, $rval['name']); // специальность - название
$pdf->Text(142.2, 153.4, $rval['termtext']); // специальность - срок

$pdf->newPage();
$pdf->newPage();

$pdf->Text(177.8, 21.3, $rval['price'][0]); // специальность - оплата

$pdf->newPage();

$y = 28.6;
$pdf->SetFont("times", "", 11);
$pdf->Text(53.6, $y, $appl->surname." ".$appl->name." ".$appl->second_name.";");

$pdf->splitText("адрес постоянной регистрации: ".$appl->getRegAddress().";", array(array(10.2,$y+4.9), array(10.2,$y+10.9)), 108, 1);
// паспорт
$pdf->splitText("паспорт: серия ".$r['doc_serie']." № ".$r['doc_number'].", выдан ".date('d.m.Y', strtotime($r['doc_date'])).", ".$r['doc_issued'].".", array(array(10.2,$y+16.9),array(10.2,$y+22.9)), 108, 1);

$pdf->SetFont("times", "", 12);
$pdf->Text(158.4, 92.6, $appl->getShortR());

$pdf->Output('dogovor2.pdf', 'D');
?>
