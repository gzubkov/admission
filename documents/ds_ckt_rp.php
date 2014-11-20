<?php
require_once('../../conf.php');
require_once('../class/mysql.class.php');
require_once('../class/price.class.php');
require_once('../class/pdf.class.php');
require_once('../class/documents.class.php');

$applicant_id = $_REQUEST['applicant'];

$msl = new dMysql();
new FabricApplicant($appl, $msl, $applicant_id);

$r = $appl->getInfo();
$rval = $appl->getRegion(); 

$pdf = new PDF('pdf/ds_ckt_rp.pdf');

$pdf->SetFont("times", "", 11);
$pdf->splitText($rval['longfirm'], array(array(30,54.7),array(19,59.0)), 96, 1);

$pdf->Text(32.6, 62.9, $rval['gposrp']." ".$rval['name_rp']);

$pdf->Text(49, 77.2, $appl->surname." ".$appl->name." ".$appl->second_name);

if ($r['pay'] > 0) {
    $pdf->Text(158.4, 115.8, $r['pay']);

    $price = new Price($msl);
    $pay = $price->getPriceByPgid($rval['pgid'], $r['catalog'], 3, $r['pay'], 0, 1);
    unset($price);

    $pdf->Text(79, 153, $pay[0]+$pay[1]);
    $pdf->Text(52, 166.2, $pay[0]);
    $pdf->Text(65, 175.2, $pay[1]);
}

$pdf->Text(119.2, 237.0, mb_convert_case($rval['gpos'], MB_CASE_TITLE, "UTF-8"));
$pdf->Text(157.6, 246.0, substr($rval['rname'],0,2).".".(($rval['rsecond_name'] != "")?substr($rval['rsecond_name'],0,2).". ":" ").$rval['rsurname']);
$pdf->Text(67.4,  280.2, $appl->getShortR());

unset($msl);
$pdf->Output('ds.pdf', 'D');
?>
