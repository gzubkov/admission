<?php
require_once('../../conf.php');
require_once('../class/mysql.class.php');
require_once('../class/catalog.class.php');
require_once('../class/pdf.class.php');
require_once('../class/documents.class.php');

$msl = new dMysql();

$appl = new Applicant($msl, $_REQUEST['applicant']);

$r = $appl->getInfo();

// initiate PDF
$pdf = new PDF('pdf/dog_ckt.pdf');

$pdf->SetFont("times", "I", 13);
$pdf->Text(54, 114, $appl->surname." ".$appl->name." ".$appl->second_name);

$catalog = new Catalog(&$msl);
$rval = $catalog->getInfo($appl->catalog, $appl->profile);
unset($catalog);

//if ($rval['shortname'] != '') $pdf->Text(115, 17.8, "-".$rval['shortname']."-");

if ($r['num'] > 0) {
    $pdf->Text(99.2, 18.1, sprintf("0510%1$04d-03-13/14", $r['num']));
} else {
    $pdf->Text(99.2, 18.1, "0504          -03-13/14z");
}

if ($rval['typen'] == 1) { 
    $rval['name'] = "специальности ".$rval['name'];
    $pdf->Text(169.2, 192, "5 лет"); // специальность - нормативный срок
    $pdf->Text(167, 196.8, $rval['term']." лет"); // специальность - срок
} else {
    $rval['name'] = "направлению подготовки ".$rval['name'];
    $pdf->Text(169.2, 192, "4 года"); 
    $pdf->Text(167, 196.8, $rval['termtext']); // специальность - срок
}


$pdf->SetFont("times", "I", 13);
$pdf->splitText($rval['name'], array(array(107,166.8),array(22,172.6)), 35, 1);

$pdf->Text(166.2, 211.6, $rval['qualify']); // специальность - квалификация

if ($appl->semestr > 0) {
    $pdf->Text(69.2, 264.1, $appl->semestr); // семестр 
    $pdf->Text(94, 264.1, ceil($appl->semestr/2)); // курс
}

$pdf->newPage(); 
$pdf->newPage(); 
$pdf->newPage(); 

$pdf->SetFont("times", "I", 13);
$pdf->Text(64, 82.4, $appl->surname." ".$appl->name." ".$appl->second_name);

$pdf->Text(50, 92.2, $r['citizenry']);
$pdf->Text(140, 92.2, date('d.m.Y', strtotime($r['birthday'])));

// паспорт
$pdf->Text(52.2, 99.6, $r['doc_serie']);
$pdf->Text(87.2, 99.6, $r['doc_number']);
$pdf->Text(149, 99.6, date('d.m.Y', strtotime($r['doc_date'])));

$pdf->splitText($r['doc_issued'], array(array(36,106.2),array(16,113.4)), 60, 1); // 104, 111.2

$addr = $appl->getAddress();

$pdf->splitText($appl->makeAddress($addr[0]), array(array(75.4,120.4),array(16,127.2)), 53, 1);
$pdf->splitText($appl->makeAddress(end($addr)), array(array(61,134.2),array(16,141.2)), 58, 1);

$pdf->SetXY(63, 144); 
if ($r['homephone_code'] != 0) {
   $pdf->Write(0, "+7 (".$r['homephone_code'].") ".$r['homephone']);
   if ($r['mobile_code'] != 0) $pdf->Write(0, ", ");
}
if ($r['mobile_code'] != 0) $pdf->Write(0, "+7 (".$r['mobile_code'].") ".$r['mobile']);

$pdf->SetFont("times", "", 12);
$pdf->Text(165, 264.1, $appl->getShortR()); 
$pdf->Output('dogovor.pdf', 'D');
?>
