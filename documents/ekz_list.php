<?php
require_once('../class/mysql.class.php');
require_once('../class/catalog.class.php');
require_once('../class/pdf.class.php');
require_once('../class/moodle.class.php');
require_once('../class/documents.class.php');
require_once('../../conf.php');

$msl = new dMysql();

$appl = new Applicant(&$msl, $_REQUEST['applicant']);
$r = $appl->getInfo('passport','email');

$catalog = new Catalog(&$msl);
$rval = $catalog->getInfo($appl->catalog);
$univ = $catalog->getUniversityInfo($appl->catalog);
unset($catalog);

$mdl = new Moodle(&$msl);
$mdl_id = $mdl->searchUser($r['e-mail']);
$mdl_grades = $mdl->getGrades($mdl_id);
unset($mdl);

$pdf = new PDF('pdf/ekz_list.pdf');

$pdf->SetFont("times", "", 12);
$pdf->WriteHtmlCell(160,0, 35, 14, $univ['longtype']."<BR>«".$univ['name']."»",0,0,false,true,'C');


$pdf->SetFont("times", "", 14);

if ($rval['typen'] == 1) {
    $rval['type'] = "Специальность";
} else {
    $rval['type'] = "Направление подготовки";
}
$pdf->splitText($rval['type']." «".$rval['name']."»", array(array(30,47),array(30,57)), 66, 1);

$pdf->Text(52, 68.2, $appl->surname);
$pdf->Text(40, 77.6, $appl->name);
$pdf->Text(123, 77.6, $appl->second_name);

$pdf->splitText("Паспорт: серия ".$r['doc_serie']." №".$r['doc_number'].", выдан: ".$r['doc_issued'].", ".date('d.m.Y', strtotime($r['doc_date'])), array(array(75,90.4),array(75,100),array(75,110.2)), array(52,54,52));

if (is_array($mdl_grades)) {
    $num[2] = 0;
    $num[1] = 1;
    $num[4] = 2;
    $num[5] = 3;
    $num[7] = 4;
    $num[8] = 5;
    $num[3] = 100;

    $pass = 1;
    $pdf->SetFont("times", "", 12);
    foreach($mdl_grades as $v) {
        $y = 184+6.6*$num[$v['id']];
    	$pdf->Text(110.4, $y, round($v['grade'])); 
    	$pdf->Text(125.4, $y, $v['surname']." ".substr($v['name'],0,2).".".substr($v['second_name'],0,2)."."); 
    	if ($v['grade'] < $v['min']) $pass = 0;
    }

    $pdf->SetFont("times", "I", 12);
    if ($appl->sex == 'M') {
        $text = "прошел";
    } else {
        $text = "прошла";
    }
//    if ($pass == 0) $text = "не ".$text;
    $pdf->Text(112+6, 228, $text);
}

$pdf->Output('ekz_list.pdf', 'D');
?>
