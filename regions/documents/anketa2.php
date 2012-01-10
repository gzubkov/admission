<?php
// just require TCPDF instead of FPDF
require_once('../../../../modules/tcpdf/tcpdf.php');
require_once('../../../../modules/fpdi/fpdi.php');
require_once('../../../../modules/russian_date.php');
require_once('../../../../modules/mysql.php');
require_once('../../../conf.php');

class PDF extends FPDI {
    /**
     * "Remembers" the template id of the imported page
     */
    var $_tplIdx;
    
    /**
     * include a background template for every page
     */
    function Header() {
        if (is_null($this->_tplIdx)) {
            $this->setSourceFile('../../documents/document_2.pdf');
            $this->_tplIdx = $this->importPage(1);
        }
    }
    
    function Footer() {}
}


if (!is_numeric($_REQUEST['applicant'])) exit(0);
$applicant_id = $_REQUEST['applicant'];

$msl = new dMysql();
// --- Базовый запрос (сведения об абитуриенте, регион, цена (руб., коп.)) --- //
$r = $msl->getarray("SELECT * FROM admission.partner_applicant WHERE id = ".$applicant_id.";");

if ($r['region'] != $_SESSION['joomlaregion'] && $_SESSION['rights'] != "admin") {
    exit(0);
}
// initiate PDF
$pdf = new PDF();
$pdf->SetMargins(PDF_MARGIN_LEFT, 40, 0);
$pdf->SetAutoPageBreak(true, 0);

// add a page
$pdf->AddPage();
$pdf->useTemplate($pdf->_tplIdx);

$pdf->SetFont("times", "B", 13);
$pdf->SetXY(190.5, 7.8); // номер анкеты
$pdf->Write(0, $applicant_id);

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

$arr = splitstring($r['birthplace'], 30, 1); 

$pdf->SetFont("times", "", 12);
$pdf->Text(42, 179.8, $arr[0]); // место рождения 180.3
$pdf->Text(10, 185, $arr[1]); // место рождения2

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

$arr = splitstring($r['doc_issued'], 32);
$pdf->SetFont("times", "", 12);
$pdf->Text(128.2, 170.8, $arr[0]); // паспорт-кем выдан
$pdf->Text(106.2, 176.6, $arr[1]); // паспорт-кем выдан2

$pdf->Text(144, 183.8, $r['doc_code']); // код подразделения
$pdf->Text(173.4, 184, date('d   m   Y', strtotime($r['doc_date']))); // дата

// ----------------------------------------------------
$pdf->SetFont("times", "", 12);
$pdf->Text(67, 191.2, $r['homeaddress-index']); // индекс
$pdf->Text(116, 191.2, $r['homeaddress-region']); // код региона - 73


$rval  = $msl->getarray("SELECT reg_rf_subject.name FROM reg_rf_subject WHERE reg_rf_subject.id='".$r['homeaddress-region']."'");
$pdf->Text(10, 197.2, $rval['name']); // субъект РФ
$pdf->Text(128.5, 197.2, $r['homeaddress-city']); // населенный пункт

$pdf->Text(40,  204.3, $r['homeaddress-street']); // улица
$pdf->Text(142, 204.3, $r['homeaddress-home']); // дом
$pdf->Text(165, 204.3, $r['homeaddress-building']); // корпус
if ( $r['homeaddress-flat'] != 0) {
   $pdf->Text(190, 204.3, $r['homeaddress-flat']); // квартира
}

$pdf->Text(51, 211.0, $r['homephone_code']); // телефон-домашний-код
$pdf->Text(71, 211.0, $r['homephone']); // телефон-домашний-номер
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

//
// ------------------------------------------------------------
//
$pdf->addPage(); 
$pdf->useTemplate($pdf->importPage(2));
$pdf->SetFont("times", "", 12);
if ($r['semestr'] > 0) {
    $pdf->Text(186.2, 37.2, $r['semestr']); // семестр
    $pdf->Text(29.2, 42, ceil($r['semestr']/2)); // курса
}
// специальность
$rval = $msl->getarray("SELECT b.spec_code, b.name, a.baseedu FROM catalogs a LEFT JOIN specialties b ON a.specialty=b.id WHERE a.id='".$r['catalog']."'");
$pdf->SetFont("times", "", 12);
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
//$pdf->Text(69.8, 159.4, $ival['rups']); // РУПы

if ($r['semestr'] > 0) {
    $pdf->Text(146.2, 169.2, $r['semestr']); // семестр
    $pdf->Text(179.2, 169.2, ceil($r['semestr']/2)); // курса
}

$pdf->Text(11.2, 176.4, "заочной"); 
$pdf->Text(97.2, 176.4, "платной договорной"); 
$pdf->Text(61.8, 183.4, $rval['specialty']); // специальность - название

$pdf->Line(10, 192.8, 157.5, 192.8, array('width' => 0.4));

//$pdf->Text(140.4, 257.8, mb_strtolower(russian_date( strtotime($ival['date']), 'j        F' ), 'UTF-8')); 
//$pdf->Text(189, 257.8, substr($ival['date'], 2,2));
unset($msl);
$pdf->Output('MAMIanketa.pdf', 'D');
?>
