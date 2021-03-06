<?php
require_once('../../conf.php');
require_once('../class/mysql.class.php');
require_once('../class/catalog.class.php');
require_once('../class/pdf.class.php');
require_once('../class/documents.class.php');

if (!is_numeric($_REQUEST['request'])) exit(0);
$request_id = $_REQUEST['request'];
$msl = new dMysql();

$req = $msl->getarray("SELECT * FROM reg_request WHERE id='".$request_id."' LIMIT 1");

$applicant_id = $req['applicant_id'];
$appl = new Applicant($msl, $req['applicant_id']);

//if ($_SESSION['applicant_id'] != $applicant_id && $_SESSION['rights'] != "admin") exit(0);

// --- Базовый запрос (сведения об абитуриенте, регион, цена (руб., коп.)) --- //
$r = $msl->getarray(
"SELECT reg_applicant.*
FROM reg_applicant 
WHERE reg_applicant.id = ".$applicant_id." LIMIT 1;");

$pdf = new PDF('pdf/document.pdf');

$pdf->SetFont("times", "B", 13);
$pdf->Text(187, 10, $applicant_id.($req['internet']?"И":""));

$pdf->SetFont("times", "", 13);
$pdf->Text(14.9, 27.1, $appl->surname." ".$appl->name." ".$appl->second_name);
$pdf->Text(146.9, 133.1, $appl->getShort()); 

$pdf->Text(32.4, 149.9, $appl->surname);
$pdf->Text(24.4, 157.1, $appl->name);
$pdf->Text(30.4, 164.5, $appl->second_name);

// пол
if ($r['sex'] == 'M') {
    $pdf->cross(35.5, 166.1);
} else {
    $pdf->cross(73, 166.1);
}

$pdf->SetFont("times", "", 13);
$pdf->SetXY(48, 173.1); // дата рождения
$pdf->Write(0, date('d      m         y', strtotime($r['birthday'])));

$pdf->SetFont("times", "", 12);
$pdf->splitText($r['birthplace'], array(array(41.6,184.3),array(10,189.6)), 30, 1);

// ||||||||||||||||||||||||||||||||||||||||||||||||||||

// гражданство
$pdf->SetFont("verdana", "B", 13);
if ($r['citizenry'] == 'Российская Федерация') {
   $pdf->SetXY(134.1, 145.1); // Российская федерация
   $pdf->Write(0, "X");
} else {
   $pdf->SetXY(134.1, 152.2); // другое
   $pdf->Write(0, "X");

   $pdf->SetFont("times", "", 13);
   $pdf->SetXY(139.8, 152.3); // другое
   $pdf->Write(0, $r['citizenry']);
}

// паспорт
$pdf->SetFont("times", "", 13);
$pdf->SetXY(122, 164.4); // паспорт-серия
$pdf->Write(0, $r['doc_serie']);
$pdf->SetXY(155, 164); // паспорт-номер
$pdf->Write(0, $r['doc_number']);

$pdf->SetFont("times", "", 12);
$pdf->splitText($r['doc_issued'], array(array(128,175.4),array(106.8,181.3)), 32, 1);

$pdf->SetXY(144, 184.1); // код подразделения
$pdf->Write(0, $r['doc_code']);
$pdf->SetXY(172.8, 184.2); // дата
$pdf->Write(0, date('d   m   Y', strtotime($r['doc_date'])));

// ----------------------------------------------------
$pdf->SetFont("times", "", 12);
$pdf->SetXY(67, 191.8); // индекс
$pdf->Write(0, $r['homeaddress-index']);
$pdf->SetXY(116, 191.8); // код региона - 73
$pdf->Write(0, $r['homeaddress-region']);


$rval  = $msl->getarray("SELECT reg_rf_subject.name FROM reg_rf_subject WHERE reg_rf_subject.id='".$r['homeaddress-region']."'");
$pdf->SetXY(9, 197.5); // субъект РФ
$pdf->Write(0, $rval['name']);
$pdf->SetXY(97, 197.5); // населенный пункт
$pdf->Write(0, $r['homeaddress-city']);

$pdf->SetXY(40, 204.8); // улица
$pdf->Write(0, $r['homeaddress-street']);
if ($r['homeaddress-home'] > 0) {
    $pdf->SetXY(134, 204.8); // дом
    $pdf->Write(0, $r['homeaddress-home']);
}
if ($r['homeaddress-building'] > 0) {
    $pdf->SetXY(159, 204.8); // корпус
    $pdf->Write(0, $r['homeaddress-building']);
}
if ($r['homeaddress-flat'] > 0) {
$pdf->SetXY(187, 204.8); // квартира
$pdf->Write(0, $r['homeaddress-flat']);
}

if ($r['homephone_code'] != 0) {
    $pdf->SetXY(50, 211.5); // телефон-домашний-код
    $pdf->Write(0, $r['homephone_code']);
}
if ($r['homephone'] != 0) {
    $pdf->SetXY(70, 211.5); // телефон-домашний-номер
    $pdf->Write(0, $r['homephone']);
}
$pdf->SetXY(131, 211.5); // телефон-мобильный-код
$pdf->Write(0, $r['mobile_code']);
$pdf->SetXY(150, 211.5); // телефон-мобильный-номер
$pdf->Write(0, $r['mobile']);

$rval = $msl->getarray("SELECT edu_doc,serie,number,date,copy FROM reg_applicant_edu_doc WHERE applicant='".$applicant_id."' AND `primary`='1' LIMIT 1;");
if ($rval != 0) {
$pdf->SetFont("verdana", "B", 13);
if ($rval['edu_doc'] == 1) {
   $pdf->SetXY(28, 218); // аттестат
   $pdf->Write(0, "X");
} else {
   $pdf->SetXY(52.3, 218); // диплом
   $pdf->Write(0, "X");
}

$pdf->SetFont("times", "", 12);
$pdf->SetXY(71, 218.6); // диплом-серия
$pdf->Write(0, $rval['serie']);
$pdf->SetXY(92, 218.6); // диплом-номер
$pdf->Write(0, $rval['number']);
$pdf->SetXY(134, 218.6); // диплом-выдан
$pdf->Write(0, date('d   m   Y', strtotime($rval['date'])));

if ($rval['copy']) {
   $pdf->SetFont("verdana", "B", 13);
   $pdf->SetXY(194, 218); // диплом-копия
   $pdf->Write(0, "X");
}
}
// -----------------------------------------------------

// изучаемый язык
$pdf->SetFont("verdana", "B", 13);
switch ($r['language']) {
   case 3: 
      $pdf->SetXY(72.2, 229.3); // французский
      $pdf->Write(0, "X");
      break;
   case 2:
      $pdf->SetXY(122.2, 229.3); // немецкий
      $pdf->Write(0, "X");
      break;
   default:
      $pdf->SetXY(22, 229.3); // английский
      $pdf->Write(0, "X");
      break;
}

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

// дополнительные сведения
if (0) {
if ($rarr['school'] > 0) {
   $pdf->SetFont("verdana", "B", 13);
   $pdf->SetXY(9.5, 256.2); // подшефная школа
   $pdf->Write(0, "X");
   $pdf->SetFont("times", "", 12);
   $pdf->SetXY(75.5, 256.8); // подшефная школа №
   $pdf->Write(0, $rarr['school']);
}

if ($rarr['tclistener']) {
   $pdf->SetFont("verdana", "B", 13);
   $pdf->SetXY(100.7, 256.2); // подготовительные курсы
   $pdf->Write(0, "X");
}

$pdf->SetFont("times", "", 12);
$pdf->SetXY(101.5, 262.2); // олимпиада
$pdf->Write(0, $rarr['olymp']);
$pdf->SetXY(91.5, 268.4); // диплом победителя (призера)
$pdf->Write(0, $rarr['olymp_details']);
$pdf->SetXY(94.5, 274.6); // льготы
$pdf->Write(0, $rarr['facilities']);
$pdf->SetXY(112.8, 281); // льготы
$pdf->Write(0, $rarr['facilities_details']);
}
//
// ------------------------------------------------------------
//
$pdf->addPage(); 
$pdf->useTemplate($pdf->importPage(2));

// специальность

$cat = new Catalog($msl);
$rval = $cat->getInfo($req['catalog'], $req['profile']);
unset($cat);

if (isset($rval['profile'])) $rval['name'] .= " (".$rval['profile'].")";

$pdf->SetFont("times", "", 12);
$pdf->SetXY(10.5, 41.6); // специальность - код
$pdf->Write(0, $rval['spec_code']);
$pdf->SetXY(41.5, 41.2); // специальность - название
$pdf->Write(0, $rval['name']);

$pdf->cross(180.8, 52.8); // Факультет ЦИТО

if ($req['spo']) {
   $pdf->SetFont("verdana", "B", 13);
   $pdf->SetXY(9.5, 61.2); // имею СПО по профилю
   $pdf->Write(0, "X");
}

// ------------------------------------------------------------
$pdf->Line(46.5, 95, 197.5, 95, array('width' => 0.6));
$pdf->Line(46.5, 100, 197.5, 100, array('width' => 0.6));
$pdf->Line(46.5, 105, 101.4, 105, array('width' => 0.6));
$pdf->SetFont("times", "", 12);
$pdf->SetXY(147.1, 102.2); // приоритет
$pdf->Write(0, "1");

/*if ($rarr['other_konkurs']) {
   $pdf->SetFont("verdana", "B", 13);
   $pdf->SetXY(9.5, 107.9); // в случае незачисления
   $pdf->Write(0, "X");
}*/

// баллы ЕГЭ
// XX-XXXXXXXXX-XX
// Первые две цифры - регион выдачи свидетельства (77 - Москва и т.д.), дальше номер свидетельства из 9 цияр, дальше выдачи год свидетельства (08, 09, 10).
$rval = $msl->getarray("SELECT name, score, document FROM reg_applicant_scores LEFT JOIN reg_subjects ON reg_applicant_scores.subject = reg_subjects.id WHERE `request_id` = ".$request_id." AND `ege` = 1 ORDER BY subject ASC", 1);

if ($rval != 0) {
$pdf->SetFont("times", "", 13);
$pdf->SetXY(61, 132.5); // оценка - Русский язык
$pdf->Write(0, $rval[0]['score']);

$pdf->SetXY(100, 132.5); // номер документа - Русский язык
$pdf->Write(0, $rval[0]['document']);

// ------------------------------

$pdf->SetFont("times", "", 12);
$pdf->SetXY(7.4, 139.8); // оценка - 2
$pdf->Write(0, $rval[1]['name']);

$pdf->SetFont("times", "", 13);
$pdf->SetXY(61, 139.8); // оценка - 2
$pdf->Write(0, $rval[1]['score']);

$pdf->SetXY(99, 139.8); // номер документа - 2
$pdf->Write(0, $rval[1]['document']);

// ------------------------------

$pdf->SetFont("times", "", 12);
$pdf->SetXY(7.4, 147); // предмет - 3
$pdf->Write(0, $rval[2]['name']);

$pdf->SetFont("times", "", 13);
$pdf->SetXY(61, 147); // оценка - 3
$pdf->Write(0, $rval[2]['score']);

$pdf->SetXY(99, 147); // номер документа - 3
$pdf->Write(0, $rval[2]['document']);
}
// -------------------------------

/*$rval = $msl->getarray("SELECT a.name as 1name, b.name as 2name, c.name as 3name, ppe FROM reg_applicant_ppe LEFT JOIN reg_subjects a ON 1subject=a.id LEFT JOIN reg_subjects b ON 2subject=b.id LEFT JOIN reg_subjects c ON 3subject=c.id WHERE request_id = ".$request_id);

if (is_array($rval)) {
   $pdf->SetFont("verdana", "B", 14);
   $pdf->SetXY(13.8, 164.95); // а) разрешить участвовать в ЕГЭ
   $pdf->Write(0, "X");

   $pdf->SetFont("times", "", 13);
   $pdf->SetXY(13.2, 176); // предмет - 1
   $pdf->Write(0, $rval['1name']);
   $pdf->SetXY(77, 176); // предмет - 2
   $pdf->Write(0, $rval['2name']);
   $pdf->SetXY(142, 176); // предмет - 3
   $pdf->Write(0, $rval['3name']);

   $pdf->SetFont("times", "", 13);
   $pdf->SetXY(34, 182.8); // сдаю ЕГЭ в... институт
   $pdf->Write(0, $rval['ppe']);
} 
*/
if ($req['traditional_form']) {
   $pdf->SetFont("verdana", "B", 14);
   $pdf->SetXY(13.8, 193.75); // б) допустить меня до...
   $pdf->Write(0, "X");
}

// высшее профессиональное образование получаю
$pdf->SetFont("verdana", "B", 14);
if ($r['highedu'] == 0) {
   $pdf->SetXY(116.15, 217.6); // впервые
   $pdf->Write(0, "X");
} else {
   $pdf->SetXY(144.45, 217.6); // невпервые
   $pdf->Write(0, "X");
}

$pdf->Output('newpdf.pdf', 'D');
?>
