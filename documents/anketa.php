<?php
require_once('../../conf.php');
require_once('../class/mysql.class.php');
require_once('../class/catalog.class.php');
require_once('../class/pdf.class.php');
require_once('../class/documents.class.php');

$msl = new dMysql();

$applicant_id = $_REQUEST['applicant'];

new FabricApplicant($appl, $msl, $applicant_id);

$r = $appl->getInfo();

$pdf = new PDF('pdf/anketa.pdf');

$pdf->SetFont("times", "B", 12);
$pdf->Text(187, 10, $applicant_id.($r['internet']?"И":""));

$pdf->SetFont("times", "", 13);
$pdf->Text(32.4, 33.4, $appl->surname);
$pdf->Text(32.4, 39.2, $appl->name);
$pdf->Text(32.4, 45.2, $appl->second_name);

// пол
if ($appl->sex == 'M') {
    $pdf->cross(37.32, 47.25, 4);
} else {
    $pdf->cross(74.8, 47.25, 4);
}

$pdf->SetFont("times", "", 12);

$pdf->Text(41.4, 56.8, date('d      m        y', strtotime($r['birthday'])));
$pdf->splitText($r['birthplace'], array(array(43.4,62.8),array(11,68.8)), 30, 1);

// ||||||||||||||||||||||||||||||||||||||||||||||||||||

// гражданство
if ($r['citizenry'] == 'Российская Федерация') {
    $pdf->cross(134.85, 29.5, 4);
} else {
    $pdf->cross(134.6, 35.4, 4);
    $pdf->Text(140.45, 39.1, $r['citizenry']);
}

// паспорт
$pdf->SetFont("times", "", 13);
$pdf->Text(118.4, 50.9, $r['doc_serie']);
$pdf->Text(154, 50.9, $r['doc_number']);

$pdf->SetFont("times", "", 12);
$pdf->splitText($r['doc_issued'], array(array(127,56.8),array(105.6,62.8)), 32, 1);

$pdf->Text(142.4, 68.78, $r['doc_code']); // код подразделения
$pdf->Text(168.8, 68.78, date('d   m   Y', strtotime($r['doc_date'])));

// ----------------------------------------------------
$addr = $appl->getAddress();

if (is_array($addr)) {
    $y = 88.9;
    foreach ($addr as $v) {
        $pdf->Text(43.2, $y, $v['index']); // индекс
    	$pdf->Text(82.4, $y, $v['region']); // код региона 
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

if ($r['homephone_code'] != 0) {
    $pdf->Text(31.2, 145.5, $r['homephone_code']); // телефон-домашний-код
    $pdf->Text(51, 145.5, $r['homephone']); // телефон-домашний
}
if ($r['mobile_code'] != 0) {
    $pdf->Text(118.2, 145.5, $r['mobile_code']); 
    $pdf->Text(137.8, 145.5, $r['mobile']); 
}
$pdf->Text(24, 152.4, $r['e-mail']);

$pdf->SetFont("times", "", 12);
$rval = $appl->getEduDoc();

if ($rval != 0) {
    if ($rval['edu_doc'] == 1) {
        $pdf->cross(28.8, 162.78, 3.9); // аттестат
    } else {
        $pdf->cross(52.4, 162.78, 3.9); // диплом
    }

    $pdf->Text(71.4, 166.4, $rval['serie']); // диплом-серия
    $pdf->Text(92, 166.4, $rval['number']); // диплом-номер
    $pdf->Text(131.1, 166.4, date('d     m     Y', strtotime($rval['date']))); // диплом-выдан

    if ($rval['copy']) {
//        $pdf->cross(194.68, 162.78, 3.9); // копия
    }
}
// -----------------------------------------------------

switch ($r['language']) {
case 2:
    $pdf->cross(72.9, 244.03, 4); // немецкий
    break;

default:
    $pdf->cross(22.85, 244.03, 4); // английский
    break;
}

// тип учебного заведения
switch($r['edu_base']) {
case 1:
    $pdf->cross(10.47, 222.5, 3.8); // общеобразовательное
    break;

case 3:
    $pdf->cross(107.35, 222.5, 3.8); // начальное профессиональное образование
    break;

case 2:
    $pdf->cross(10.47, 227.35, 3.8); // среднее профессиональное образование
    break;

case 4:
    $pdf->cross(107.35, 227.35, 3.8); // другое
    break;
}

//
// ------------------------------------------------------------
//
$pdf->newPage(); 

$cat = new Catalog(&$msl);
$rval = $cat->getInfo($appl->catalog, $appl->profile);
unset($cat);

if (isset($rval['profile'])) $rval['name'] .= " (".$rval['profile'].")";

$pdf->SetFont("times", "", 12);
$pdf->Text(12, 65.9, $rval['spec_code']); // специальность - код
$pdf->Text(43, 65.9, $rval['name']); // специальность - название

if ($r['spo']) {
    $pdf->cross(10.3, 79.6);
}

$rval = $appl->getEge();

if ($rval != 0) {
    $pdf->cross(15.1, 150.5, 3.8); 
    $y = 169.4;
    foreach($rval as $v) {
        $pdf->Text(10.6, $y, $v['name']);
	$pdf->Text(80, $y, $v['score']);
	$pdf->Text(111.8, $y, $v['document']); // номер документа
	$y += 5.2;
    }
} else {
    $pdf->cross(15.1, 136.3, 3.8); 
}

// высшее профессиональное образование получаю
$pdf->SetFont("verdana", "B", 14);
if ($r['highedu'] == 0) {
   $pdf->cross(117.12, 191.5, 3.8); // впервые 
} else {
   $pdf->cross(145.25, 191.5, 3.8); // невпервые
}

$pdf->Output('newpdf.pdf', 'D');
?>
