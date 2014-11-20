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
$pdf = new PDF('pdf/'.$appl->filePrefix.'/dog_mami.pdf');
$pdf->SetFont("times", "I", 12);

$catalog = new Catalog($msl);
$rval = $catalog->getInfo($appl->catalog, $appl->profile);
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
$pdf->Text(99.9, 17.6, "403        -7-14/15");

$pdf->SetFont("times", "I", 12);

$pdf->printCenter(18, 65, $appl->surname." ".$appl->name." ".$appl->second_name);

if ($r['highedu'] == 0) {
    $pdf->Text(162.2, 97.1, "впервые");
} else {
    $pdf->Text(162.2, 97.1, "не впервые");
}

$pdf->Text(133.0, 107.8, $rval['code']); // код
$pdf->printCenter(18, 110.6, $rval['name']); // наименование

$pdf->Text(149.2, 127.4, getTerm($rval['normterm'])); // нормативный срок

$startDate = strtotime($startDate);
$finalDate = strtotime("+".$rval['indterm'][0]." years + ".$rval['indterm'][1]." months - 1 day", $startDate);

$pdf->Text(165.2, 132, getTerm($rval['indterm'])); // индивидуальный срок

if ($showDate === true) {
    $pdf->Text(23.6, 136.6, mb_strtolower(russianDate('d      F', $startDate), 'UTF-8')); // с.
    $pdf->Text(73.6, 136.6, mb_strtolower(russianDate('d      F', $finalDate), 'UTF-8')); // по.
    $pdf->Text(108.2, 136.6, substr(date('y', $finalDate), 1));
}

$pdf->Text(109.2, 193, $rval['qualify']); // квалификация.

$pdf->newPage();
$pdf->Text(132.4, 250, ($price*2));
$pdf->Text(18.6, 256.6, num2str($price*2));

$pdf->newPage();
if ($showDate === true) {
    $pdf->Text(21.6, 31.5, date('d.m.Y', $startDate)." г.");
}
$pdf->Text(92.4, 31.5, ($price*2));
$pdf->Text(77.8, 35.9, (int)$price);

if ($showDate === true) {
    $pdf->Text(142.4, 55.2, date('d.m.Y', $startDate)." г.");
}

$pdf->Text(106.0, 59.6, ($price*($rval['indterm'][0]*2+$rval['indterm'][1]/6)));

$pdf->newPage();
$addr = $appl->getAddress();

$pdf->SetFont("times", "", 10);

$string = '';
if ($r['homephone_code'] != 0) {
    $code = $appl->getPhoneCode();
    $string = "<br>Дом. телефон: +".$code['phone_code']." (".$r['homephone_code'].") ".$r['homephone'].".";
}

$pdf->WriteHtmlCell(59, 100, 139, 133.6, $appl->surname." ".$appl->name." ".$appl->second_name."<br>
Дата рождения: ".mb_strtolower(russianDate('d F Y', strtotime($r['birthday'])), 'UTF-8').".<br>
Паспорт: ".$r['doc_serie']." ".$r['doc_number'].", выдан ".$r['doc_issued'].", ".date('d.m.Y', strtotime($r['doc_date'])).".<br>
Адрес постоянного места жительства: ".$appl->makeAddress(end($addr)).".<br>
Моб. телефон: +7 (".$r['mobile_code'].") ".$r['mobile'].".".$string, 0, 0, false, true, 'L');

$pdf->SetFont("times", "", 11);
$pdf->Text(163.7, 250.8, "(".$appl->getShortR().")");

$pdf->Output('dogovor.pdf', 'D');
