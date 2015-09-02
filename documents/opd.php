<?php
require_once('../class/mysql.class.php');
require_once('../class/pdf.class.php');
require_once('../class/documents.class.php');
require_once('../../conf.php');

$msl = new dMysql();

$pdf = new PDF('pdf/opd.pdf');
new FabricApplicant($appl, $msl, $_REQUEST['applicant_id']);

$r = $appl->getInfo('passport');
$addr = $appl->getAddress();

$pdf->SetFont("times", "", 13);
$pdf->splitText($appl->surname." ".$appl->name." ".$appl->second_name, array(array(100.4,57.8),array(100.4,63),array(100.4,68.2)), array(47,47), 1);
$pdf->splitText($appl->makeAddress($addr[0]), array(array(100.4,77.2),array(100.4,82.4),array(100.4,87.6)), array(47,47), 1);
$pdf->splitText($r['doc_serie']." ".$r['doc_number'].", выдан ".$r['doc_issued'].", ".date('d.m.Y', strtotime($r['doc_date'])), array(array(100.4,100.6),array(100.4,105.8),array(100.4,111),array(100.4,116.2)), array(46,47,47), 1);

$pdf->newPage();
$pdf->Text(133.4, 164, $appl->getShort());

$pdf->Output('opd.pdf', 'D');
?>