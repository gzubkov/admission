<?php
// just require TCPDF instead of FPDF
require_once('../../../modules/tcpdf/tcpdf.php');
require_once('../../../modules/fpdi/fpdi.php');
require_once('../../../modules/russian_date.php');
require_once('../../../modules/mysql.php');
require_once('../../conf.php');
require_once('../class/price.class.php');
require_once('../class/catalog.class.php');

class PDF extends FPDI {
    function Header() {}
    function Footer() {}
}

$req = getarray("SELECT * FROM reg_request 
WHERE id = ".$_REQUEST['request_id'].";");

// --- Базовый запрос (сведения об абитуриенте) --- //
$r = getarray("SELECT * FROM reg_applicant 
WHERE reg_applicant.id = ".$req['applicant_id'].";");

// initiate PDF
$pdf = new PDF();
$pdf->SetMargins(PDF_MARGIN_LEFT, 40, 0);
$pdf->SetAutoPageBreak(true, 0);
$pdf->setSourceFile('dog_ckt_s.pdf');
// add a page
$pdf->AddPage();
$pdf->useTemplate($pdf->importPage(1));

$pdf->SetFont("times", "I", 12);
$pdf->Text(34.6, 71, $r['surname']." ".$r['name']." ".$r['second_name']);

$cat = new Catalog();
$rval = $cat->getInfo($req['catalog']);

$price = new Price();
$rval['price'] = $price->getPriceByRegion($r['region'],$req['catalog'], 1, 1, 0, 0);

unset($price);
unset($cat);


$pdf->Text(15, 107, $rval['name']); // специальность - название
$pdf->Text(155.2, 93.6, $rval['term']); // специальность - срок

$pdf->addPage(); 
$pdf->useTemplate($pdf->importPage(2));

$pdf->Text(172.8, 264.85, $rval['price'][0]); // специальность - оплата

$pdf->addPage(); 
$pdf->useTemplate($pdf->importPage(3));

$pdf->addPage(); 
$pdf->useTemplate($pdf->importPage(4));

$pdf->SetFont("times", "", 11);
$pdf->Text(53.2, 16.6, $r['surname']." ".$r['name']." ".$r['second_name'].";");

// паспорт
$arr = splitstring("паспорт: серия ".$r['doc_serie']." № ".$r['doc_number'].", выдан ".date('d.m.Y', strtotime($r['doc_date'])).", ".$r['doc_issued'].";", 108, 1); 
$pdf->Text(10, 21.3, $arr[0]);
if (isset($arr[1])) {
    $pdf->Text(10, 26.1, $arr[1]);
}

$pdf->Text(10, 30.9, "зарегистрирован по адресу: ".$r['regaddress'].".");

if ($r['second_name'] != "") {
    $pdf->Text(158.8, 71.6, substr($r['name'],0,2).".".substr($r['second_name'],0,2).". ".$r['surname']);
} else {
    $pdf->Text(158.8, 71.6, substr($r['name'],0,2).". ".$r['surname']);
}


$pdf->Output('dogovor2.pdf', 'D');
?>
