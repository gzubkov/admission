<?php
require_once('../../conf.php');
require_once('../class/mysql.class.php');
require_once('../class/pdf.class.php');
require_once('../class/documents.class.php');

$msl = new dMysql();
$applicant_id = $_REQUEST['applicant_id'];

$appl = new Applicant($msl, $applicant_id);
$val = $appl->getEduDoc();

if (!is_array ($val)) die("Нет добавленных документов.");

$pdf = new PDF('pdf/opis.pdf');

$pdf->SetFont("times", "I", 13);
$pdf->Text(61, 35.9, $appl->surname." ".$appl->name." ".$appl->second_name);

$pdf->SetFillColor(255,255,255);
$pdf->Rect(150,164.4,10,4,'F');


$pdf->SetFont("times", "I", 13);

//foreach($rval as $key=>$val) {
    switch($val['edu_doc']) {
        case 1:
            // аттестат 
            $pdf->Text(72.8, 132.2, $val['serie']);
            $pdf->Text(90.8, 132.2, $val['number']);
            $pdf->Text(97.1, 138.5, $val['serie']);
            $pdf->Text(112.8, 138.5, $val['number']);
	    break;

        case 6: 
            $pdf->Text(94.8, 121.6, $val['number']);
	    break;

        case 7: 
            $pdf->Text(94.8, 128.4, $val['number']);
	    break;

        default:
            $pdf->Text(72.4, 144.4, $val['serie']);
	    $pdf->Text(89.4, 144.4, $val['number']);
	    $pdf->Text(96, 150.2, $val['serie']);
	    $pdf->Text(113.4, 150.2, $val['number']);
    }
//}

$pdf->SetFont("times", "", 11);
$pdf->Text(153, 217.5, $appl->connum); // количество договоров мы-студент

$rval = $appl->getRups();
if ($rval['rups'] > 0) {
    $pdf->Text(156, 120.5, "1");
}

if ($rval['pay'] > 0) {
    $pdf->Text(153, 224.5, $appl->connum." экз.");
}

$pdf->SetFont("times", "", 14);

$pdf->Output('opis.pdf', 'D');
?>
