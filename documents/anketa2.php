<?php
require_once('../../../modules/russian_date.php');
require_once('../../conf.php');
require_once('../class/catalog.class.php');
require_once('../class/pdf.class.php');
require_once('../class/mysql.class.php');
require_once('../class/documents.class.php');
$msl = new dMysql();

$applicant_id = $_REQUEST['applicant'];

new FabricApplicant($appl, $msl, $applicant_id);

$r = $appl->getInfo();

$pdf = new PDF('pdf/anketa_2.pdf');

$pdf->SetFont("times", "B", 13);
$pdf->Text(190.5, 7.8, $applicant_id.($r['internet']?"И":""));

$pdf->SetFont("times", "", 13);

$pdf->Text(32.4, 33.4, $appl->surname); // Фамилия - 27 + 118,4 //145.3
$pdf->Text(25.4, 39.2, $appl->name); // Имя
$pdf->Text(30.4, 45.2, $appl->second_name); // Отчество

// пол
if ($appl->sex == 'M') {
    $pdf->cross(37.2, 47.35);
} else {
    $pdf->cross(74.6, 47.35);
}

$pdf->Text(42.4, 57, date('d    m       y', strtotime($r['birthday'])));

$pdf->SetFont("times", "", 12);
$pdf->splitText($r['birthplace'], array(array(43,63),array(10,68.8)), 30, 1); //179.1,185.1

// ||||||||||||||||||||||||||||||||||||||||||||||||||||

// гражданство
if ($r['citizenry'] == 'Российская Федерация') {
    $pdf->cross(134.7, 29.55);
} else {
    $pdf->cross(134.7, 35.7);
    $pdf->Text(140.5, 39.1, $r['citizenry']);
}

// паспорт
$pdf->SetFont("times", "", 13);
$pdf->Text(124.4, 51.1, "паспорт");
$pdf->Text(118.4, 56.9, $r['doc_serie']);
$pdf->Text(154.6, 56.9, $r['doc_number']); //163.7

$pdf->SetFont("times", "", 12);
$pdf->splitText($r['doc_issued'], array(array(126.4,63.0),array(106,68.8)), 34, 1);

$pdf->Text(142.2, 74.7, $r['doc_code']); // код подразделения
$pdf->Text(169.2, 74.7, date('d   m   Y', strtotime($r['doc_date'])));

// ----------------------------------------------------

$addr = $appl->getAddress();
if (is_array($addr)) {
    $y = 92.7;
    foreach ($addr as $v) {
        $pdf->Text(43.2, $y, $v['index']); // индекс
    	$pdf->Text(96.4, $y, $v['region']); // код региона 
    	$pdf->Text(10, $y+5.9, $v['regionname']); // регион 
    	$pdf->Text(111.4, $y+5.9, $v['city']); // населенный пункт 
    	$pdf->Text(39.6, $y+12.9, $v['street']); // улица 

    	if ($v['home'] > 0) {
            $pdf->Text(134.2, $y+12.9, $v['home']); // дом 
        }
    	if ($v['building'] > 0) {
            $pdf->Text(160.5, $y+12.9, $v['building']); // строение 
    	}
        if ($v['flat'] > 0) {
            $pdf->Text(188.2, $y+12.9, $v['flat']); // квартира 
        }
        $y += 29.55;
    }
}

if ( $r['homephone_code'] != 0 ) $pdf->Text(33, 149.2, $r['homephone_code']); // телефон-домашний-код
if ( $r['homephone'] != 0 ) $pdf->Text(53, 149.2, $r['homephone']); // телефон-домашний-номер
$pdf->Text(117.8, 149.2, $r['mobile_code']); // телефон-мобильный-код
$pdf->Text(138, 149.2, $r['mobile']); // телефон-мобильный-номер
$pdf->Text(23.4, 155.8, $r['e-mail']);

$rval = $appl->getEduDoc(); 

if ($rval != 0) {
    if ($rval['edu_doc'] == 1) {
        $pdf->cross(28.8, 162.38, 3.9); // аттестат
    } else {
        $pdf->cross(52.4, 162.38, 3.9); // диплом
    }

    $pdf->Text(71.4, 165.9, $rval['serie']); // диплом-серия
    $pdf->Text(95, 165.9, $rval['number']); // диплом-номер
    $pdf->Text(135.1, 165.9, date('d   m    Y', strtotime($rval['date']))); // диплом-выдан

    $pdf->splitText($rval['institution'], array(array(132,172.38),array(9.7,179.4)), 32, 1);
    $pdf->splitText($rval['city'], array(array(146,189.38),array(9.7,196.4)), 30, 1);
}

$pdf->SetFont("times", "", 12);
//$pdf->Text(90, 218.7, $rval['institution']);

//$pdf->splitText($rval['specialty'], array(array(86,224.4),array(10,229.4)), 66, 1); 


// изучаемый язык
switch ($r['language']) {
case 2:
    $pdf->cross(72.8, 282.92); // немецкий
    break;

default:
    $pdf->cross(22.75, 282.92); // английский
    break;
}


//
// ------------------------------------------------------------
//
$pdf->newPage(); 

$pdf->SetFont("times", "", 12);
if ($appl->semestr != 0) {
    $pdf->Text(66.4, 55, $appl->semestr); // семестр
    $pdf->Text(99.6, 55, ceil($appl->semestr/2)); // курса
}

// специальность
$cat = new Catalog($msl);
$rval = $cat->getInfo($appl->catalog, $appl->profile);
unset($cat);

if (isset($rval['profile'])) $rval['name'] .= " (".$rval['profile'].")";

$pdf->Text(12, 64.7, $rval['spec_code']); // специальность - код
$pdf->Text(43.2, 64.7, $rval['name']); // специальность - название

// высшее профессиональное образование получаю
$pdf->cross(($r['highedu'])?146.4:118.2, 78.6); // впервые/невпервые

// ---------------------------------
$ival = $appl->getRups();

$pdf->SetFont("times", "", 13);
$pdf->Text(69.8, 173.4, $ival['rups']); // РУПы

if ($appl->semestr != 0) {
    $pdf->Text(124, 183.2, $appl->semestr); // семестр
    $pdf->Text(151, 183.2, ceil($appl->semestr/2)); // курса
}

$pdf->Line(10, 207, 137, 207, array('width' => 0.4));

$pdf->Output('anketa.pdf', 'D');
?>
