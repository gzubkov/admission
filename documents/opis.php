<?php
require_once('../../conf.php');
require_once('../class/mysql.class.php');
require_once('../class/pdf.class.php');
require_once('../class/documents.class.php');

$msl = new dMysql();
$applicant_id = $_REQUEST['applicant_id'];

new FabricApplicant($appl, $msl, $applicant_id);
$val = $appl->getEduDoc();

if (!is_array ($val)) die("Нет добавленных документов.");

$pdf = new PDF('pdf/opis.pdf');

$pdf->SetFont("times", "I", 13);
$pdf->Text(61, 35.9, $appl->surname." ".$appl->name." ".$appl->second_name);

$pdf->SetFont("times", "I", 13);

//foreach($rval as $key=>$val) {
    switch($val['edu_doc']) {
        case 1:
            // аттестат 
            $pdf->Text(60.8, 138.6, $val['serie']);
            $pdf->Text(78.8, 138.6, $val['number']);
            $pdf->Text(87.1, 145.2, $val['serie']);
            $pdf->Text(105.8, 145.2, $val['number']);
	    break;

        case 6: // диплом о неполном
            $pdf->Text(86.8, 169.2, $val['number']);
	    break;

        case 7: // академическая справка
            $pdf->Text(86.8, 163, $val['number']);
	    break;

        default:
            $pdf->Text(59.4, 151, $val['serie']);
	    $pdf->Text(76.4, 151, $val['number']);
	    $pdf->Text(85, 157.2, $val['serie']);
	    $pdf->Text(103.4, 157.2, $val['number']);
    }
//}

$pdf->SetFont("times", "", 11);
$pdf->Text(153, 212.5, $appl->connum); // количество договоров мы-студент

$rval = $appl->getRups();
if ($rval['rups'] > 0) {
    $pdf->Text(156, 127.2, "1");
}

if ($rval['pay'] > 0) {
    $pdf->Text(153, 220, $appl->connum." экз.");
}

$pdf->SetFont("times", "", 14);

$pdf->Output('opis.pdf', 'D');
?>
