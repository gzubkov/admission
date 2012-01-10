<?php
// just require TCPDF instead of FPDF
require_once('../../../../modules/tcpdf/tcpdf.php');
require_once('../../../../modules/fpdi/fpdi.php');
require_once('../../../../modules/russian_date.php');
require_once('../../../../modules/mysql.php');
require_once('../../../conf.php');
require_once('../../class/catalog.class.php');
require_once('../../class/rights.class.php');

class PDF extends FPDI {
    /**
     * "Remembers" the template id of the imported page
     */
    var $_tplIdx;
    
    function Header() {}    
    function Footer() {}
}

function cross($x,$y,$d=5) {
    global $pdf;
    $pdf->Line($x, $y, $x+$d, $y+$d, array('width' => 0.3));
    $pdf->Line($x, $y+$d, $x+$d, $y, array('width' => 0.3));
}

$rg = new Rights();
if (is_numeric($_REQUEST['applicant'])) {
    if (!$rg->checkApplicant($applicant_id,'printdocuments')) exit(0);
    $applicant_id = $_REQUEST['applicant'];
    $type = "self";
} elseif (is_numeric($_REQUEST['id'])) {
    $applicant_id = $_REQUEST['id'];
    $type = "region";
} else {
    $rg->printError();
    exit(0);
}

$msl = new dMysql();
// --- Базовый запрос (сведения об абитуриенте, регион, цена (руб., коп.)) --- //
$r = $msl->getarray("SELECT * FROM partner_applicant WHERE id = ".$applicant_id.";");

if ($type == "region" && !$rg->checkRegion($r['region'],'printdocuments')) {
    $rg->printError();
    exit(0);
} 
unset($rg);

// initiate PDF
$pdf = new PDF();
$pdf->SetMargins(PDF_MARGIN_LEFT, 40, 0);
$pdf->SetAutoPageBreak(true, 0);

if ($r['semestr'] == 1) {
    $pdf->setSourceFile('zayavl.pdf');
} else {
    $pdf->setSourceFile('zayavl2.pdf');
}

// add a page
$pdf->AddPage();
$pdf->useTemplate($pdf->importPage(1));

$pdf->SetFont("times", "B", 13);
$pdf->SetXY(190.5, 7.8); // номер анкеты
$pdf->Write(0, $applicant_id);

$pdf->SetFont("times", "", 13);
$pdf->Text(52.6,55,   $r['surname']);
$pdf->Text(43.4,61.8, $r['name']);
$pdf->Text(52.6,68.6, $r['second_name']);

$pdf->SetFont("times", "", 13);
$pdf->Text(62, 75.3, date('d.m.Y', strtotime($r['birthday'])));

$arr = splitstring($r['birthplace'], array(20,39,39), 1); 
$x = 65;
$y = 82.2;
foreach ($arr as $v) {
    $pdf->Text($x, $y, $v);
    $y += 6.8;
    $x = 33.4;
}

$pdf->Text(142,55,$r['citizenry']);
$pdf->Text(117.4,68.6,$r['citizenry']);

$pdf->Text(128,75.3,$r['doc_serie']);
$pdf->Text(156,75.3,$r['doc_number']);

$pdf->Text(145,82.2, mb_strtolower(russian_date(strtotime($r['doc_date']) , 'j        F             y' ), 'UTF-8'));

$arr = splitstring($r['doc_issued'], 32);
$pdf->Text(125, 89, $arr[0]);
$pdf->Text(117.4, 95.8, $arr[1]);

// ----------------------------------------------------
$rval = $msl->getarray("SELECT reg_rf_subject.name FROM reg_rf_subject WHERE reg_rf_subject.id='".$r['homeaddress-region']."'");
$string = $r['homeaddress-index'].", ".$rval['name'].", ".$r['homeaddress-city'];
if ($r['homeaddress-street']   != "") $string .= ", ".$r['homeaddress-street'];
if ($r['homeaddress-home']     != "") $string .= ", дом ".$r['homeaddress-home'];
if ($r['homeaddress-building'] != "") $string .= "/".$r['homeaddress-building'];
if ($r['homeaddress-flat']      > 0 ) $string .= ", ".$r['homeaddress-flat'];

$arr = splitstring($string, 53);
$pdf->Text(85.0, 107.8, $arr[0]);
$pdf->Text(31.4, 114.6, $arr[1]);

$pdf->Text(47.6, 121.6, "+7 (".$r['homephone_code'].") ".$r['homephone'].", +7 (".$r['mobile_code'].") ".$r['mobile']);

$cat = new Catalog();
$rval = $cat->getInfo($r['catalog']);
unset($cat);

if ($r['semestr'] == 1) {
    $pdf->Text(67.8, 144.15, $rval['type']." ".$rval['spec_code']." ".$rval['name']); // специальность - код

    // баллы ЕГЭ
    $rval = $msl->getarray("SELECT name, b.code, score, document FROM partner_applicant_scores a LEFT JOIN reg_subjects b ON a.subject = b.id WHERE `applicant_id` = ".$applicant_id." AND `subject` = 2 AND `ege` = 1 ORDER BY b.id LIMIT 3", 1);

    /*$pdf->SetFont("times", "", 12);
    $pdf->Text(80, 170.4, "1");
    $pdf->Text(105.5, 170.4, $rval[0]['score']); // оценка - Русский язык
    $pdf->Text(119.6, 170.4, "Свидетельство о ЕГЭ, ".$rval['document']);
*/
    $y = 175.2; 

    foreach($rval as $v) {
        $pdf->Text(31.6, $y, $v['name']);
    	$pdf->Text(80, $y, $v['code']);
    	$pdf->Text(105.5, $y, $v['score']);
    	$pdf->Text(119.6, $y, "Свидетельство о ЕГЭ, ".$v['document']);
    	$y += 5.2;
    }

    if ($rval == 0 && $r['traditional_form']) cross(125.22,186.7);
} else {
    $pdf->Text(189, 144.2, $r['semestr']);
    $pdf->Text(48, 148.9, ceil($r['semestr']/2));

    $pdf->Text(69.6, 148.9, $rval['type']." ".$rval['spec_code']." ".$rval['name'].":"); // специальность - код

}
//
// ------------------------------------------------------------
//
$pdf->addPage(); 
$pdf->useTemplate($pdf->importPage(2));


$pdf->Text(40, 31, date('Y', strtotime($r['edu_date'])));

switch($r['edu_base']) {
case 1:
    cross(80.7,33.2); // общеобразовательное
    break;

case 3:
    cross(153.1,39.6); // НПО
    break;

case 2:
    cross(148.6,45.9); // СПО
    break;
}
//cross(148.4,52.4);
//cross(84.6,58.8);

if ($rval['edu_doc'] == 1) {
   cross(31.2,69); // аттестат
} else {
   cross(54.4,69); // диплом
}

$pdf->Text(75,72.8,$r['edu_serie']); // диплом-серия
$pdf->Text(95.5,72.8,$r['edu_number']); // диплом-номер

switch ($r['language']) { // изучаемый язык
case 1: 
    cross(37.25, 99.7);
    break;

case 2: 
    cross(64.4, 99.7);
    break;

case 3:
    cross(97.5, 99.7);
    break;
}


if (0) {

// тип учебного заведения
switch($r['edu_base']) {
   case 1:
   	$pdf->SetXY(9.7, 239.1); // общеобразовательное
   	$pdf->Write(0, "X");
	break;
   case 3:
   	$pdf->SetXY(106.5, 239.1); // начальное профессиональное образование
	$pdf->Write(0, "X");
	break;
   case 2:
   	$pdf->SetXY(9.7, 244); // среднее профессиональное образование
	$pdf->Write(0, "X");
	break;
   default:
   	$pdf->SetXY(106.5, 244); // другое
	$pdf->Write(0, "X");
}
}

$pdf->SetFont("times", "", 13);
$pdf->Text(18.5,140.4, mb_strtolower(russian_date( time(), 'j       F' ), 'UTF-8'));
$pdf->Text(63,140.4, date('y'));
// высшее профессиональное образование получаю
if ($r['highedu'] == 0) {
    cross(31.3, 160.2);
} else {
    cross(61, 160.2); // невпервые
}

$pdf->Output('RGIISzayavl.pdf', 'D');
?>
