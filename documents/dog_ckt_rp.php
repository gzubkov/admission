<?php
require_once('../../conf.php');
require_once('../class/mysql.class.php');
require_once('../class/catalog.class.php');
require_once('../class/price.class.php');
require_once('../class/pdf.class.php');
require_once('../class/documents.class.php');

$applicant_id = $_REQUEST['applicant'];

$msl = new dMysql();
new FabricApplicant($appl, $msl, $applicant_id);

$r = $appl->getInfo('passport');

$addr = $appl->getRegAddress();
$rval = $appl->getRegion();

$cat = new Catalog($msl);
$kval = $cat->getInfo($appl->catalog);
unset($cat);

$prc = new Price($msl);
$cval = $prc->getPricePercentByPgid($rval['pgid'], $appl->catalog,1,1,0,1,1);
unset($prc);

// initiate PDF
$pdf = new PDF('pdf/dog_ckt_rp.pdf');

$pdf->SetFont("times", "", 12);
$pdf->splitText($rval['longfirm'], array(array(25,79.2),array(15,   83.4)), 89, 1);

$pdf->Text(62, 87.4,   $rval['gposrp']." ".$rval['name_rp']);
$pdf->Text(78, 91.6,   mb_convert_case($rval['orgdoc'], MB_CASE_TITLE, "UTF-8"));

$pdf->Text(165.2, 91.6,   $rval['ckt_num']);
$pdf->Text(15.6, 95.6,   date('d.m.Y', strtotime($rval['ckt_date'])));

$pdf->Text(45, 101.8, $appl->surname." ".$appl->name." ".$appl->second_name);

$pdf->Text(143, 167.8, $kval['term']);
$pdf->Text(69, 141, $kval['name']);

$pdf->newPage(); 
$pdf->newPage(); 

$pdf->Text(155, 62.7, $cval['price']);

$pdf->newPage(); 

$pdf->SetFont("times", "", 11);
$pdf->splitText("ИНН ".$rval['inn'].", КПП ".$rval['kpp'].", ".$rval['firm'].".", array(array(62.2, 125.33),array(15,   130.33)), 63, 1);

$pdf->Text(24.8, 134.6, "Юридический адрес: ".$rval['legaladdress'].".");

$pdf->splitText("Р/счет ".$rval['rs']." в ".$rval['bank'].", кор./счет ".$rval['ks'].", БИК ".$rval['bik'].".", array(array(25, 139.2),array(15,   144.6)), 94, 1); 

$pdf->Text(58.8, 150.4, $appl->surname." ".$appl->name." ".$appl->second_name.".");
$pdf->Text(24.8, 155.4, "Адрес регистрации: ".$addr.".");

$pdf->splitText("Паспорт: ".$r['doc_serie']." № ".$r['doc_number'].", выдан ".date('d.m.Y', strtotime($r['doc_date'])).", ".$r['doc_issued'].".", array(array(25, 160.4),array(15,   165.0)), 96, 1);

$pdf->Text(127, 191.4, mb_convert_case($rval['gpos'], MB_CASE_TITLE, "UTF-8")); // 151.2
$pdf->Text(164.4, 200.8, substr($rval['rname'],0,2).".".substr($rval['rsecond_name'],0,2).". ".$rval['rsurname']);

$pdf->Text(65.4, 242, $appl->getShortR());
unset($msl);
$pdf->Output('dogovor_pl_usl.pdf', 'D');
?>
