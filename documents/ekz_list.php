<?php
require_once('../../../modules/russian_date.php');
require_once('../../../modules/mysql.php');
require_once('../class/catalog.class.php');
require_once('../class/pdf.class.php');
require_once('../../conf.php');

$msl = new dMysql();

$req = getarray("SELECT * FROM reg_request 
WHERE id = ".$_REQUEST['request_id'].";");

$applicant_id = $req['applicant_id'];

// --- Базовый запрос (сведения об абитуриенте) --- //
$r = $msl->getarray("SELECT surname,name,second_name,sex,doc_serie,doc_number,doc_date,doc_issued,`e-mail` FROM reg_applicant 
WHERE reg_applicant.id = ".$applicant_id.";");

$mdl_user = $msl->getarray("SELECT id FROM education.`edu_user` WHERE `email`='".$r['e-mail']."' LIMIT 1");

if ($mdl_user['id'] > 0) {
$mdl_grades = $msl->getarray("SELECT b.id,a.grade,b.min,c.surname,c.name,c.second_name FROM education.`edu_quiz_grades` a 
	      		      LEFT JOIN admission.`reg_subjects` b ON a.quiz=b.mid 
			      LEFT JOIN admission.`reg_teachers` c ON b.`teacher_id`=c.id WHERE a.`userid`='".$mdl_user['id']."' ORDER BY b.id;",1);
}
// initiate PDF
$pdf = new PDF();
$pdf->SetMargins(PDF_MARGIN_LEFT, 40, 0);
$pdf->SetAutoPageBreak(true, 0);
$pdf->setSourceFile('ekz_list.pdf');

// add a page
$pdf->AddPage();
$pdf->useTemplate($pdf->importPage(1));

$catalog = new Catalog();
$rval = $catalog->getInfo($req['catalog']);
unset($catalog);

$pdf->SetFont("times", "", 14);

if ($rval['typen'] == 1) {
    $rval['type'] = "Специальность";
} else {
    $rval['type'] = "Направление подготовки";
}
$pdf->splitText($rval['type']." \"".$rval['name']."\"", array(array(30,47),array(30,57)), 66, 1);

$pdf->Text(52, 68.2, $r['surname']);
$pdf->Text(40, 77.6, $r['name']);
$pdf->Text(123, 77.6, $r['second_name']);

$pdf->splitText("Паспорт: серия ".$r['doc_serie']." №".$r['doc_number'].", выдан: ".$r['doc_issued'].", ".date('d.m.Y', strtotime($r['doc_date'])), array(array(75,90.4),array(75,100),array(75,110.2)), array(52,54,52), 1);

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
    if ($r['sex'] == 'M') {
        $text = "прошел";
    } else {
        $text = "прошла";
    }
    if ($pass == 0) $text = "не ".$text;
    $pdf->Text(112, 228, $text);
}

$pdf->Output('ekz_list.pdf', 'D');
?>
