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
$pdf->printCenter(42.8, 31, $appl->surname." ".$appl->name." ".$appl->second_name, 0, 139.7);

$pdf->SetFont("times", "I", 12);
switch($val['edu_doc']) {
    case 1:
        // аттестат 
        $pdf->Text(60.8, 137.1, $val['serie']);
        $pdf->Text(78.8, 137.1, $val['number']);
        $pdf->Text(87.1, 143.6, $val['serie']);
        $pdf->Text(105.8, 143.6, $val['number']);
        break;

    case 6: 
        // диплом о неполном
        $pdf->Text(86.8, 173.1, $val['serie']."   ".$val['number']);
	break;

    case 7: 
        // академическая справка
        $pdf->Text(86.8, 167.2, $val['number']);
        break;

    default:
        $pdf->Text(59.4, 149.5, $val['serie']);
	    $pdf->Text(76.4, 149.5, $val['number']);
        
        // с 01.01.2014 года выдаются дипломы, имеющие разные номера приложения и самого диплома
        if (date('Y', strtotime($val['date'])) < 2014) {
	        $pdf->Text(85, 155.3, $val['serie']);
	        $pdf->Text(103.4, 155.3, $val['number']);
        }
}

$pdf->SetFont("times", "", 11);
$pdf->Text(153, 222.5, $appl->connum); // количество договоров мы-студент

//$rval = $appl->getRups();
//if ($rval['rups'] > 0) {
//    $pdf->Text(156, 127.2, "1");
//}

//if ($rval['pay'] > 0) {
//    $pdf->Text(153, 230, $appl->connum." экз.");
//}

$pdf->Output('opis.pdf', 'D');

