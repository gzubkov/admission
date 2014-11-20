<?php
require_once '../../conf.php';
require_once '../class/mysql.class.php';
require_once '../class/price.class.php';
require_once '../class/catalog.class.php';
require_once '../class/pdf.class.php';
require_once '../class/documents.class.php';
require_once '../class/russian.class.php';
$msl = new dMysql();

/***
Дата начала
***/
$startDate = "01-12-2014";

if (isset($startDate) === false) {
    $showDate = false;
} else {
    $showDate = true;
}

new FabricApplicant($appl, $msl, $_REQUEST['applicant']);

$r = $appl->getInfo();

// initiate PDF
$pdf = new PDF('pdf/dogovor.pdf');

$catalog = new Catalog($msl);
$rval = $catalog->getInfo($appl->catalog, $appl->profile, $appl->semestr);
unset($catalog);

$price = new Price($msl);
$appl->price = $price->newgetPrice($appl->catalog, $appl->region);
unset($price);

$price = $appl->price['price_university'];

/*
if ($r['num'] > 0) {
    $pdf->Text(99.2, 18.1, sprintf("0510%1$04d-03-13/14", $r['num']));
} */
$pdf->SetFont("times", "BI", 14);
$pdf->Text(104.8, 18.8, "403        -7-14/15");

$pdf->SetFont("times", "I", 12);

$pdf->printCenter(18, 73.4, $appl->surname." ".$appl->name." ".$appl->second_name);

if ($r['highedu'] == 1) {
    $pdf->Line(164, 104.2, 178, 104.2, array('width' => 0.3));
} else {
    $pdf->Line(178, 104.2, 197.9, 104.2, array('width' => 0.3));
}

$pdf->Text(133.0, 116, $rval['code']); // код
$pdf->printCenter(18, 121.6, $rval['name']); // наименование

$pdf->Text(166.6, 142.6, getTerm($rval['normterm'])); // нормативный срок 127.4

$startDate = strtotime($startDate);
$finalDate = strtotime("+".$rval['indterm'][0]." years + ".$rval['indterm'][1]." months - 1 day", $startDate);

$pdf->Text(168, 148, getTerm($rval['indterm'])); // индивидуальный срок

if ($showDate === true) {
    $pdf->Text(23.6, 153.4, mb_strtolower(russianDate('d    F', $startDate), 'UTF-8')); // с.
    $pdf->Text(60, 153.4, date('y', $startDate));
    $pdf->Text(78.6, 153.4, mb_strtolower(russianDate('d    F', $finalDate), 'UTF-8')); // по.
    $pdf->Text(114.2, 153.4, date('y', $finalDate));
}

// на высоте 235 будет номер договора

$pdf->Text(109.2, 268, $rval['qualify']); // квалификация.

$pdf->newPage();
$pdf->newPage();

if ($price > 1) {
    $pdf->Text(136.4, 218, ($price*2)); // 250
    $pdf->splitText(num2str($price*2), array(array(158.4,218),array(17.6,223.6)), 15, 1);

    $pdf->Text(125.8, 223.6, (int)$price);
    $pdf->splitText(num2str($price), array(array(147.8,223.6),array(17.6,228.8)), 20, 1);

    if ($showDate === true) {
        $pdf->Text(26, 275.3, mb_strtolower(russianDate('d      F', $startDate), 'UTF-8'));
        $pdf->Text(64.2, 275.3, substr(date('y', $startDate), 1));
    }

    $pdf->Text(97.4, 275.3, ($price*2));
    $pdf->Text(79, 280.6, (int)$price);
}

$pdf->newPage();
$pdf->newPage();

$addr = $appl->getAddress();

$pdf->SetFont("times", "", 10.2);

$string = '';
if ($r['homephone_code'] != 0) {
    $code = $appl->getPhoneCode();
    $string = "<br>Дом. телефон: +".$code['phone_code']." (".$r['homephone_code'].") ".$r['homephone'].".";
}

if ($r['mobile_code'] != 0) {
    $string .= "<br>Моб. телефон: +7 (".$r['mobile_code'].") ".$r['mobile'].".";
}

$pdf->WriteHtmlCell(59, 100, 139, 131, $appl->surname." ".$appl->name." ".$appl->second_name."<br>
Дата рождения: ".mb_strtolower(russianDate('d F Y', strtotime($r['birthday'])), 'UTF-8').".<br>
Паспорт: ".$r['doc_serie']." ".$r['doc_number'].", выдан ".$r['doc_issued'].", ".date('d.m.Y', strtotime($r['doc_date'])).".<br>
Адрес постоянного места жительства: ".$appl->makeAddress(end($addr)).".".$string, 0, 0, false, true, 'L');

$pdf->SetFont("times", "", 11);
$pdf->Text(163.7, 263.4, "/".$appl->getShortR()."/");

$pdf->Output('dogovor.pdf', 'D');
