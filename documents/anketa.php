<?php
require_once '../../conf.php';
require_once '../class/mysql.class.php';
require_once '../class/catalog.class.php';
require_once '../class/pdf.class.php';
require_once '../class/documents.class.php';

$msl = new dMysql();

$applicantId = $_REQUEST['applicant'];
new FabricApplicant($appl, $msl, $applicantId);

$r = $appl->getInfo();

$pdf = new PDF('pdf/anketa.pdf');

// Unique entrant's ID
$pdf->SetFont("times", "B", 12);
$pdf->Text(187, 10, $applicantId.($r['internet']?"И":""));

// Agreement about processing of personal data
$pdf->SetFont("times", "I", 14);
$pdf->Text(15, 16.2, $appl->surname." ".$appl->name." ".$appl->second_name);

// Entrant's full name
$pdf->Text(33, 131.6, $appl->surname);
$pdf->Text(33, 138.8, $appl->name);
$pdf->Text(33, 146.4, $appl->second_name);

if ($appl->sex == 'M') {
    $pdf->cross(35.45, 147.75, 4.2);
} else {
    $pdf->cross(60.45, 147.75, 4.2);
}

// date of birth
$pdf->Text(49.4, 159, date('d     m        y', strtotime($r['birthday'])));

// birth place
$pdf->SetFont('times', 'I', 12);
$pdf->splitText($r['birthplace'], array(array(42.0,166.1),array(9,171.3)), 30, 1);

// ||||||||||||||||||||||||||||||||||||||||||||||||||||

// гражданство
if ($r['citizenry'] == 'Российская Федерация') {
    $pdf->cross(134.85, 127.5, 4);
} else {
    $pdf->cross(134.85, 134.5, 4);
    $pdf->Text(140.45, 138, $r['citizenry']);
}

// паспорт
$pdf->SetFont("times", "I", 14);
$pdf->Text(118.2, 150, $r['doc_serie']);
$pdf->Text(148.4, 150, $r['doc_number']);

// кем выдан
$pdf->SetFont("times", "I", 12);
$pdf->splitText($r['doc_issued'], array(array(128.4,156.9),array(105.6,162.5)), 32, 1);

// код подразделения
$pdf->SetFont("times", "I", 14);
$pdf->Text(143.0, 169.9, $r['doc_code']);
$pdf->Text(172.8, 169.9, date('d  m  Y', strtotime($r['doc_date'])));

// ----------------------------------------------------
$addr = $appl->getAddress();

// Адрес регистрации и места фактического проживания (при наличии)
if (is_array($addr)) {
    $y = 183.9;
    foreach ($addr as $v) {
        $pdf->Text(44.6, $y, $v['index']);
        if ($v['country'] == 1) {
            $pdf->Text(100, $y, $v['region']);
            $pdf->Text(10.4, $y+6.58, $v['regionname']);
            $pdf->Text(104.4, $y+6.58, $v['city']);
        } else {
            $pdf->Text(104.4, $y+6.58, $v['countryname'].", ".$v['city']);
        }
        $pdf->Text(41.4, $y+13.78, $v['street']);

        if ($v['home'] > 0) {
            $pdf->Text(128.2, $y+13.77, $v['home']);
        }
        if ($v['building'] > 0 || strlen($v['building']) > 0) {
            $pdf->Text(157, $y+13.8, $v['building']);
        }
        if ($v['flat'] > 0) {
            $pdf->Text(186.6, $y+13.88, $v['flat']);
        }
        $y += 25.4;
    }
}

if ($r['homephone_code'] != 0) {
    $pdf->Text(58, 230.6, $r['homephone_code']);// телефон-домашний-код.
    $pdf->Text(77.8, 230.6, $r['homephone']);   // телефон-домашний.
}

if ($r['mobile_code'] != 0) {
    $pdf->Text(143, 230.6, $r['mobile_code']);
    $pdf->Text(163.3, 230.6, $r['mobile']);
}

// адрес e-mail:
$pdf->Text(27, 237.7, $r['e-mail']);

if ($appl->isAdult() === true) {
    $pdf->Text(148, 255, $appl->getShort());
}

$rval = $appl->getEduDoc(); // Документ об образовании.

// тип документа об образовании
switch($rval['edu_doc']) {
case 1:
    $pdf->cross(10.47, 268.8); // общеобразовательное
    break;
case 2:
    $pdf->cross(85, 268.8);    // начальное профессиональное образование
    break;
case 3:
    $pdf->cross(10.47, 279.3); // среднее профессиональное образование
    break;
case 5:
    $pdf->cross(85, 281.5);    // диплом ВПО
    break;
default:
    $pdf->cross(160, 281.5);   // другое
}

$pdf->newPage();
$pdf->SetFont("times", "I", 14);

$pdf->Text(23, 21.6, $rval['serie']); // диплом-серия
$pdf->Text(51.6, 21.6, $rval['number']); // диплом-номер
if ($rval['date'] != 0) {
    $pdf->Text(119.6, 21.6, date('d    m    Y', strtotime($rval['date']))); // диплом-выдан
}

if (isset($rval['copy']) === true && 
    $rval['copy'] === TRUE) {
    $pdf->cross(190.4, 17.85, 4.3); // копия
}

switch ($r['language']) {
case 2:
    $pdf->cross(72.98, 42.4, 4.2); // немецкий
    break;
default:
    $pdf->cross(22.98, 42.4, 4.2); // английский
}

// ------------------------------------------------------------

$catalog = new Catalog($msl);
$cat = $catalog->getInfo($appl->catalog, $appl->profile);
$subjects = $catalog->getSubjects($appl->catalog);
unset($catalog);

$pdf->SetFont("times", "I", 13);
$pdf->Text(19, 135.2, $cat['code']); // специальность - код.
$pdf->Text(45, 135.2, $cat['name']); // специальность - название.

// ------------------------------------------------------------

$pdf->newPage();
$pdf->SetFont("times", "", 12);
$ege = $appl->getEge();

if ($ege != 0) {
    $y = 40;
    foreach ($ege as $v) {
        $pdf->Text(11.9, $y, $v['name']);
        $pdf->Text(83, $y, $v['score']);
        if ($v['year'] > 0) {
            $pdf->Text(122, $y, "20".$v['year']);
        }
        $y += 9.7;
    }
} 

if ($ege == 0 ||
    sizeof($ege) < 3) {
    $pdf->cross(14.85, 172.5, 4.2);
    if ($rval['edu_doc'] == 1) {
        $pdf->Text(74, 181, "аттестата");
    } elseif ($rval['edu_doc'] != 4) {
        $pdf->Text(74, 181, "диплома");
    }
}
// добавить список предметов на поступление
$array = array();

foreach ($subjects as $val) {
    $array[] = $val['subject'];
}

$pdf->Text(51, 208.6, implode(', ', $array));

$pdf->newPage();
$pdf->Line(10, 58, 201.9, 58, array('width' => 0.3));

$pdf->cross(107.12, 15.08, 4.2);
$pdf->cross(107.12, 34.58, 4.2);

//ob_end_clean();

$pdf->Output('anketa.pdf', 'D');
