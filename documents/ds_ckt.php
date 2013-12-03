<?php
require_once('../../conf.php');
require_once('../class/price.class.php');
require_once('../class/mysql.class.php');
require_once('../class/pdf.class.php');
require_once('../class/documents.class.php');
$msl = new dMysql();

new FabricApplicant($appl, $msl, $_REQUEST['applicant']);
$r = $appl->getInfo('region');

$pdf = new PDF('pdf/ds_ckt.pdf');

$pdf->SetFont("times", "", 12);
$pdf->Text(52, 60.2, $appl->surname." ".$appl->name." ".$appl->second_name);

$ival = $appl->getRups();
$pdf->Text(162, 102.2, $ival['pay']);

$price = new Price($msl);
$pay = $price->getPriceByRegion($r['region'], $appl->catalog, 3, $ival['pay'], 0, 0, 1);
unset($price);

$pdf->Text(80, 138.2, $pay[0]);

$pdf->Text(160, 227, $appl->getShortR());

$pdf->Output('ds.pdf', 'D');
?>
