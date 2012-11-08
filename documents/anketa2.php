<?php
require_once('../../../modules/russian_date.php');
require_once('../../conf.php');
require_once('../class/catalog.class.php');
require_once('../class/pdf.class.php');
require_once('../class/mysql.class.php');
require_once('../class/documents.class.php');
$msl = new dMysql();

$applicant_id = $_REQUEST['applicant'];
$appl = new Applicant($msl, $applicant_id);

$r = $appl->getInfo();

$pdf = new PDF('pdf/document_2.pdf');

$pdf->SetFont("times", "B", 13);
$pdf->Text(190.5, 7.8, $applicant_id.($r['internet']?"И":""));

$pdf->SetFont("times", "", 13);
$pdf->Text(14.4, 27.1, $appl->surname." ".$appl->name." ".$appl->second_name); // ФИО - полные

$pdf->Text(147.2, 129.1, $appl->getShort()); // ФИО = инициалы (подпись) 128.8

$pdf->Text(32.4, 145.3, $appl->surname); // Фамилия - 27 + 118,4
$pdf->Text(25.4, 152.5, $appl->name); // Имя
$pdf->Text(30.4, 159.9, $appl->second_name); // Отчество

// пол
if ($appl->sex == 'M') {
    $pdf->cross(35.5, 161.35);
} else {
    $pdf->cross(73, 161.35);
}

$pdf->Text(48.4, 172.3, date('d      m        y', strtotime($r['birthday'])));

$pdf->SetFont("times", "", 12);
$pdf->splitText($r['birthplace'], array(array(42,179.7),array(10,185.1)), 30, 1);

// ||||||||||||||||||||||||||||||||||||||||||||||||||||

// гражданство
if ($r['citizenry'] == 'Российская Федерация') {
    $pdf->cross(134.7, 141.1);
} else {
    $pdf->cross(134.7, 148.05);
    $pdf->Text(140.5, 151.7, $r['citizenry']);
}

// паспорт
$pdf->SetFont("times", "", 13);
$pdf->Text(120.4, 163.7, $r['doc_serie']);
$pdf->Text(152, 163.7, $r['doc_number']);

$y = 124.5;

$pdf->SetFont("times", "", 12);
$pdf->splitText($r['doc_issued'], array(array(128.4,170.7),array(107,176.7)), 32, 1);

$pdf->Text(142.4, 183.6, $r['doc_code']); // код подразделения
$pdf->Text(173.4, 183.6, date('d   m   Y', strtotime($r['doc_date'])));

// ----------------------------------------------------

$addr = $appl->getAddress();
$ad = end($addr);

$pdf->SetFont("times", "", 12);
$pdf->Text(67, 191.2, $ad['index']); // индекс
$pdf->Text(116, 191.2, $ad['region']); // код региона 


$rval  = $msl->getarray("SELECT reg_rf_subject.name FROM reg_rf_subject WHERE reg_rf_subject.id='".$ad['region']."'");
$pdf->Text(10, 197.2, $rval['name']); // субъект РФ
$pdf->Text(128.5, 197.2, $ad['city']); // населенный пункт

$pdf->Text(40,  204.3, $ad['street']); // улица
$pdf->Text(142, 204.3, $ad['home']); // дом
if ( $ad['building'] != 0 ) $pdf->Text(165, 204.3, $ad['building']); // корпус
if ( $ad['flat'] != 0) {
   $pdf->Text(190, 204.3, $ad['flat']); // квартира
}

if ( $r['homephone_code'] != 0 ) $pdf->Text(51, 211.0, $r['homephone_code']); // телефон-домашний-код
if ( $r['homephone'] != 0 ) $pdf->Text(71, 211.0, $r['homephone']); // телефон-домашний-номер
$pdf->Text(132, 211.0, $r['mobile_code']); // телефон-мобильный-код
$pdf->Text(151, 211.0, $r['mobile']); // телефон-мобильный-номер

// изучаемый язык
switch ($r['language']) {
case 3: 
    $pdf->cross(72.8, 217.88); // французский
    break;

case 2:
    $pdf->cross(122.8, 217.88); // немецкий
    break;

default:
    $pdf->cross(22.75, 217.88); // английский
    break;
}

$rval = $appl->getEduDoc(); 
$ival = $appl->getRups();

$pdf->SetFont("times", "", 12);
$pdf->Text(76, 233, $rval['institution']);

$pdf->splitText($rval['specialty'], array(array(88,237.7),array(10,242.7)), 66, 1);

//
// ------------------------------------------------------------
//
$pdf->newPage(); 

$pdf->SetFont("times", "", 12);
$pdf->Text(186.2, 37.2, $appl->semestr); // семестр
$pdf->Text(29.2, 42, ceil($appl->semestr/2)); // курса

// специальность
$cat = new Catalog(&$msl);
$rval = $cat->getInfo($appl->catalog, $appl->profile);
unset($cat);

$pdf->Text(11.2, 51.8, $rval['spec_code']); // специальность - код
$pdf->Text(41.8, 51.2, $rval['name']); // специальность - название

// факультета
$pdf->Text(54.2, 60.2, "ЦИТО");

$pdf->cross(94.2, 63.6);
$pdf->cross(72.0, 70.6);

// высшее профессиональное образование получаю
$pdf->cross(($r['highedu'])?145.2:117.0, 96.2); // впервые/невпервые

// ---------------------------------

$pdf->SetFont("times", "", 13);
$pdf->Text(69.8, 159.4, $ival['rups']); // РУПы

$pdf->Text(146.2, 169.2, $appl->semestr); // семестр
$pdf->Text(179.2, 169.2, ceil($appl->semestr/2)); // курса

$pdf->Text(11.2, 176.4, "заочной"); 
$pdf->Text(97.2, 176.4, "платной договорной"); 
if (isset($rval['specialty'])) $pdf->Text(61.8, 183.4, $rval['specialty']); // специальность - название

$pdf->Line(10, 192.8, 157.5, 192.8, array('width' => 0.4));

//$pdf->Text(140.4, 257.8, mb_strtolower(russian_date( strtotime($ival['date']), 'j        F' ), 'UTF-8')); 
//$pdf->Text(189, 257.8, substr($ival['date'], 2,2));
$pdf->Output('newpdf.pdf', 'D');
?>
