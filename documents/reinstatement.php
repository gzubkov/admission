<?php
require_once '../../conf.php';
require_once '../class/mysql.class.php';
require_once '../class/catalog.class.php';
require_once '../class/pdf.class.php';
require_once '../class/documents.class.php';
require_once '../class/russian.class.php';

$msl = new dMysql();

$applicantId = $_REQUEST['applicant'];
new FabricApplicant($appl, $msl, $applicantId);

$r = $appl->getInfo();

$pdf = new PDF('pdf/reinstatement.pdf');

// Unique entrant's ID
$pdf->SetFont("times", "B", 12);
$pdf->Text(187, 10, $applicantId.($r['internet']?"И":""));

$pdf->SetFont("times", "I", 14);
$pdf->printCenter(111, 28.2, inflect($appl->surname, 'Р'), 0, 83);
$pdf->printCenter(106, 37.6, inflect($appl->name, 'Р'), 0, 86);
$pdf->printCenter(106, 47.0, inflect($appl->second_name, 'Р'), 0, 86);

if ($r['mobile_code'] != 0) {
    $pdf->Text(147.6, 65.1, $r['mobile_code']);
    $pdf->Text(159.6, 65.1, $r['mobile']);
}

$catalog = new Catalog($msl);
$cat = $catalog->getInfo($appl->catalog, $appl->profile);
unset($catalog);

$pdf->SetFont("times", "I", 13);
$pdf->Text(30.2, 87.4, $cat['code']." ".$cat['name']);

if ($appl->semestr < $cat['maxsemestr']) {
    $pdf->cross(43.45, 106.4, 2.8);
    $pdf->Text(111.8, 109.6, $appl->semestr);
    $pdf->Text(140, 109.6, floor($appl->semestr/2));
}

// 138 - высота предыдущей специальности

$pdf->SetFont("times", "I", 12);
$pdf->Text(78.8, 243.6, $appl->semestr);
$pdf->Text(103, 243.6, floor($appl->semestr/2));
$pdf->Text(30.2, 248.4, $cat['code']." ".$cat['name']);
// ------------------------------------------------------------
//echo 'ss';
if ($appl->semestr == $cat['maxsemestr']) {
    $pdf->newPage();
}

//ob_end_clean();

$pdf->Output('reinstatement.pdf', 'D');
