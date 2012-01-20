<?php
require_once('../../../modules/russian_date.php');
require_once('../../conf.php');
require_once('../class/catalog.class.php');
require_once('../class/pdf.class.php');
require_once('../class/mysql.class.php');

if (!is_numeric($_REQUEST['request'])) exit(0);
$request_id = $_REQUEST['request'];

$msl = new dMysql();
$req = $msl->getarray("SELECT * FROM reg_request WHERE id='".$request_id."'");

$applicant_id = $req['applicant_id'];

//if ($_SESSION['applicant_id'] != $applicant_id && $_SESSION['rights'] != "admin") exit(0);

// --- Базовый запрос (сведения об абитуриенте, регион, цена (руб., коп.)) --- //
$r = $msl->getarray(
"SELECT reg_applicant.*
FROM reg_applicant 
WHERE reg_applicant.id = ".$applicant_id.";");

// initiate PDF
$pdf = new PDF();
$pdf->SetMargins(PDF_MARGIN_LEFT, 40, 0);
$pdf->SetAutoPageBreak(true, 0);
$pdf->setSourceFile('document_2.pdf');

// add a page
$pdf->AddPage();
$pdf->useTemplate($pdf->importPage(1));

$pdf->SetFont("times", "B", 13);
$pdf->SetXY(190.5, 7.8); // номер анкеты
$pdf->Write(0, $applicant_id.($req['internet']?"И":""));

$pdf->SetFont("times", "", 13);
$pdf->SetXY(14.5, 22.8); // ФИО - полные
$pdf->Write(0, $r['surname']." ".$r['name']." ".$r['second_name']);

$pdf->SetXY(146.5, 124.8); // ФИО = инициалы (подпись) 128.8
if ($r['second_name'] != "") {
   $pdf->Write(0, $r['surname']." ".substr($r['name'],0,2).".".substr($r['second_name'],0,2).".");
} else {
   $pdf->Write(0, $r['surname']." ".substr($r['name'],0,2).".");
}


$pdf->SetXY(32, 141); // Фамилия - 27 + 118,4
$pdf->Write(0, $r['surname']);

$pdf->SetXY(25, 148.2); // Имя
$pdf->Write(0, $r['name']);

$pdf->SetXY(30, 155.6); // Отчество
$pdf->Write(0, $r['second_name']);

// пол
$pdf->SetFont("verdana", "B", 14);
if ($r['sex'] == 'M') {
$pdf->SetXY(34.7, 160.6); // мужской 165.2
$pdf->Write(0, "X");
} else {
$pdf->SetXY(72.2, 160.6); // женский
$pdf->Write(0, "X");
}

$pdf->SetFont("times", "", 13);
$pdf->SetXY(48, 168.5); // дата рождения 173.1
$pdf->Write(0, date('d      m         y', strtotime($r['birthday'])));

$pdf->SetFont("times", "", 12);
$pdf->splitText($r['birthplace'], array(array(42,179.8),array(10,135)), 30, 1);

// ||||||||||||||||||||||||||||||||||||||||||||||||||||

// гражданство
$pdf->SetFont("verdana", "B", 13);
if ($r['citizenry'] == 'Российская Федерация') {
   $pdf->Text(135.2, 144.8, "X"); // Российская федерация 145.1
} else {
   $pdf->Text(135.2, 151.8, "X"); // другое

   $pdf->SetFont("times", "", 12);
   $pdf->Text(139.8, 151.8, $r['citizenry']); // другое
}

// паспорт
$pdf->SetFont("times", "", 13);
$pdf->Text(122, 163.9, $r['doc_serie']); // паспорт-серия
$pdf->Text(155, 163.8, $r['doc_number']); // паспорт-номер

$pdf->SetFont("times", "", 12);
$pdf->splitText($r['doc_issued'], array(array(128,170.6),array(106.8,176.3)), 32, 1);

$pdf->Text(144, 183.8, $r['doc_code']); // код подразделения
$pdf->Text(173.4, 184, date('d   m   Y', strtotime($r['doc_date']))); // дата

// ----------------------------------------------------
$pdf->SetFont("times", "", 12);
$pdf->Text(67, 191.2, $r['homeaddress-index']); // индекс
$pdf->Text(116, 191.2, $r['homeaddress-region']); // код региона 


$rval  = $msl->getarray("SELECT reg_rf_subject.name FROM reg_rf_subject WHERE reg_rf_subject.id='".$r['homeaddress-region']."'");
$pdf->Text(10, 197.2, $rval['name']); // субъект РФ
$pdf->Text(128.5, 197.2, $r['homeaddress-city']); // населенный пункт

$pdf->Text(40,  204.3, $r['homeaddress-street']); // улица
$pdf->Text(142, 204.3, $r['homeaddress-home']); // дом
if ( $r['homeaddress-building'] != 0 ) $pdf->Text(165, 204.3, $r['homeaddress-building']); // корпус
if ( $r['homeaddress-flat'] != 0) {
   $pdf->Text(190, 204.3, $r['homeaddress-flat']); // квартира
}

if ( $r['homephone_code'] != 0 ) $pdf->Text(51, 211.0, $r['homephone_code']); // телефон-домашний-код
if ( $r['homephone'] != 0 ) $pdf->Text(71, 211.0, $r['homephone']); // телефон-домашний-номер
$pdf->Text(132, 211.0, $r['mobile_code']); // телефон-мобильный-код
$pdf->Text(151, 211.0, $r['mobile']); // телефон-мобильный-номер

// изучаемый язык
$pdf->SetFont("verdana", "B", 13);
switch ($r['language']) {
   case 3: 
      $pdf->Text(73.2, 221.7, "X"); // французский
      break;
   case 2:
      $pdf->Text(123.2, 221.7, "X"); // немецкий
      break;
   default:
      $pdf->Text(23.2, 221.7, "X"); // английский
      break;
}

$rval = $msl->getarray("SELECT edu_doc,serie,number,institution,specialty,date,copy FROM reg_applicant_edu_doc WHERE applicant='".$applicant_id."' AND `primary`=1 AND `edu_doc`>1");
$ival = $msl->getarray("SELECT * FROM reg_institution_additional WHERE `request_id`='".$request_id."'");

$pdf->SetFont("times", "", 12);
$pdf->Text(76, 233, $rval['institution']);

$pdf->splitText($rval['specialty'], array(array(88,237.7),array(10,242.7)), 66, 1);

// форма обучения
$pdf->SetFont("verdana", "B", 13);
if (0) {
switch($ival['form']) {
   case 1:
      $pdf->Text(43, 248.6, "X"); // очная
      break;
   case 2:
      $pdf->Text(61.4, 248.6, "X"); // очно-заочная
      break;
   case 3:
      $pdf->Text(93.6, 248.6, "X"); // заочная
      break;
   default:
      $pdf->Text(115.6, 248.6, "X"); // другая
      $pdf->SetFont("times", "", 12);
      $pdf->Text(135.2, 248.6, $ival['form']); // другая
}

$pdf->SetFont("verdana", "B", 13);
$pdf->Text(($ival['base'])?72.4:44, 255.6, "X"); // бюджетная

if ($ival['semestr'] > 0) {
   $pdf->SetFont("times", "", 12);
   $pdf->Text(92.2, 262.6, $ival['semestr']); // количество полностью закрытых семестров
}

$pdf->SetFont("verdana", "B", 13);
switch($rval['edu_doc']) {
   case 7:
      $pdf->Text(54.5, 269.5, "X"); // академическая справка
      break;
   case 6:
      $pdf->Text(110.2, 269.5, "X"); // диплом о неполном ВПО
      break;
   case 5:
      $pdf->Text(161.4, 269.5, "X"); // диплом о полном ВПО
      break;
}

$pdf->SetFont("times", "", 12);
$pdf->Text(22.2, 277.0, $rval['serie']);  // серия
$pdf->Text(42.2, 277.0, $rval['number']); // номер
if ($rval['date'] > 0) {
$pdf->Text(92, 277.0, date('d   m   Y', strtotime($rval['date'])));
}

if ($rval['copy']) {
   $pdf->SetFont("verdana", "B", 13);
   $pdf->Text(151.6, 276.6, "X"); 
   $pdf->SetFont("times", "", 12);
}

$pdf->Text(119.6, 283.8, $ival['akkr_serie']); // серия
$pdf->Text(133.6, 283.8, $ival['akkr_number']); // номер
$pdf->Text(163.6, 283.8, $ival['akkr_reg']); // регистрационный номер
}
//
// ------------------------------------------------------------
//
$pdf->addPage(); 
$pdf->useTemplate($pdf->importPage(2));

$pdf->SetFont("times", "", 12);
$pdf->Text(186.2, 37.2, $req['semestr']); // семестр
$pdf->Text(29.2, 42, ceil($req['semestr']/2)); // курса

// специальность
$cat = new Catalog(&$msl);
$rval = $cat->getInfo($req['catalog']);
unset($cat);

$pdf->Text(11.2, 51.8, $rval['spec_code']); // специальность - код
$pdf->Text(41.8, 51.2, $rval['name']); // специальность - название

// факультета
$pdf->Text(54.2, 60.2, "ЦИТО");

$pdf->SetFont("verdana", "B", 13);
$pdf->Text(94.6, 67.4, "X");
$pdf->Text(72.4, 74.4, "X");

// высшее профессиональное образование получаю
$pdf->SetFont("verdana", "B", 13);
$pdf->Text(($r['highedu'])?145.6:117.4, 100, "X"); // впервые/невпервые

// ---------------------------------

$pdf->SetFont("times", "", 13);
$pdf->Text(69.8, 159.4, $ival['rups']); // РУПы

$pdf->Text(146.2, 169.2, $req['semestr']); // семестр
$pdf->Text(179.2, 169.2, ceil($req['semestr']/2)); // курса

$pdf->Text(11.2, 176.4, "заочной"); 
$pdf->Text(97.2, 176.4, "платной договорной"); 
if (isset($rval['specialty'])) $pdf->Text(61.8, 183.4, $rval['specialty']); // специальность - название

$pdf->Line(10, 192.8, 157.5, 192.8, array('width' => 0.4));

//$pdf->Text(140.4, 257.8, mb_strtolower(russian_date( strtotime($ival['date']), 'j        F' ), 'UTF-8')); 
//$pdf->Text(189, 257.8, substr($ival['date'], 2,2));
$pdf->Output('newpdf.pdf', 'D');
?>
