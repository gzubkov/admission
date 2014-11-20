<?php
require_once '../../conf.php';
require_once '../class/mysql.class.php';
require_once '../class/catalog.class.php';
require_once '../class/price.class.php';
require_once '../class/pdf.class.php';
require_once '../class/documents.class.php';

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
$cval = $prc->newgetPrice($appl->catalog, $appl->region); //getPricePercentByPgid($rval['pgid'], $appl->catalog,1,1,0,1,1);
unset($prc);

// initiate PDF
$pdf = new PDF('pdf/'.$appl->filePrefix.'/dog_rp.pdf');

$pdf->SetFont("times", "", 12);
$pdf->splitText($rval['longfirm'], array(array(25, 78.4),array(15, 82.6)), 82, 1); //74.2 78.4

$pdf->Text(64, 86.6, $rval['gposrp']." ".$rval['name_rp']); // 82.4
$pdf->Text(77, 90.8, mb_convert_case($rval['orgdoc'], MB_CASE_TITLE, "UTF-8")); // 86.6

$contract = $msl->getarray("SELECT num, `date` FROM `partner_contract` WHERE id='".$appl->region."' AND `agent`='".$appl->agent."' LIMIT 1;");
if ($contract != 0) {
    $pdf->Text(165.2, 90.8, $contract['num']);
    $pdf->Text(20.6, 94.8, date('d.m.Y', strtotime($contract['date'])));
}

$pdf->Text(45, 101, $appl->surname." ".$appl->name." ".$appl->second_name);
$pdf->Text(62, 142, $kval['name']);

$pdf->Text(142.7, 168 , $kval['termtext']);

$pdf->newPage();
$pdf->newPage();

$pdf->Text(149, 25, (int)$cval['price']);

$pdf->newPage(); 

$pdf->SetFont("times", "", 11);

$str = "ИНН ".$rval['inn'].", ";
if ($rval['kpp'] > 0) {
    $str .= "КПП ".$rval['kpp'].", ";
}

$str .= $rval['firm'].".";

$pdf->splitText($str, array(array(57.6, 95.3),array(10.0, 99.8)), 69, 1);

$pdf->Text(20.2, 104.5, "Юридический адрес: ".$rval['legaladdress'].".");

$pdf->splitText("Р/счет ".$rval['rs']." в ".$rval['bank'].", к/сч. № ".$rval['ks'].", БИК ".$rval['bik'].".", array(array(20.2, 109),array(10, 113.5)), 94, 1); 

$pdf->Text(53.8, 122.2, $appl->surname." ".$appl->name." ".$appl->second_name.".");
$pdf->Text(20.2, 126.7, "Адрес регистрации: ".$addr.".");

$pdf->splitText("Паспорт: ".$r['doc_serie']." № ".$r['doc_number'].", выдан ".date('d.m.Y', strtotime($r['doc_date'])).", ".$r['doc_issued'].".", array(array(20.2, 131.2),array(10, 135.7)), 96, 1);

$pdf->Text(122.4, 165.5, mb_convert_case($rval['gpos'], MB_CASE_TITLE, "UTF-8"));
$pdf->Text(160, 174.4, substr($rval['rname'],0,2).".".substr($rval['rsecond_name'],0,2).". ".$rval['rsurname']);

$pdf->Text(60.4, 217.6, $appl->getShortR());
unset($msl);
$pdf->Output('dogovor_pl_usl.pdf', 'D');
