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
            $this->setSourceFile('opis.pdf');
            $this->_tplIdx = $this->importPage(1);
        }
    }
    
    function Footer() {}
}

if (!is_numeric($_REQUEST['applicant'])) {exit(0);}
$applicant_id = $_REQUEST['applicant'];
$msl = new dMysql();
// --- Базовый запрос (сведения об абитуриенте) --- //
$r =  $msl->getarray("SELECT region,catalog,surname,name,second_name,edu_doc,edu_serie,edu_number,semestr FROM partner_applicant WHERE id = ".$applicant_id.";");
$pr = $msl->getarray("SELECT b.name FROM `reg_ege_minscores` a LEFT JOIN `reg_subjects` b ON a.subject=b.id WHERE a.subject != 2 AND a.specialty = (SELECT specialty FROM `catalogs` WHERE id=".$r['catalog']." LIMIT 1)");
unset($msl);
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

$pdf->SetFont("times", "I", 13);
$pdf->Text(61, 35.9, $r['surname']." ".$r['name']." ".$r['second_name']);

$pdf->SetFont("times", "", 11);
if ($r['semestr'] == 1) {
    $pdf->Text(154.8, 63, "1"); // заявление о зачислении
} else if ($r['semestr'] > 1) {
    $pdf->Text(154.8, 69, "1"); // заявление о восстановлении
}
$pdf->Text(154.8, 75, "1"); // анкета поступающего

$pdf->SetFont("times", "I", 11);
$pdf->Text(50.4, 98.4, mb_strtolower($pr[0]['name'], 'UTF-8')); // второй предмет
$pdf->Text(50.4, 104,  mb_strtolower($pr[1]['name'], 'UTF-8')); // третий предмет

$pdf->SetFont("times", "I", 13);
switch($r['edu_doc']) {
    case 1:
        // аттестат 
        $pdf->Text(72.4, 127.5, $r['edu_serie']);
	$pdf->Text(89.6, 127.5, $r['edu_number']);
	$pdf->Text(96.4, 134, $r['edu_serie']);
	$pdf->Text(113.6, 134, $r['edu_number']);
	break;

    default:
        $pdf->Text(71, 139.8, $r['edu_serie']);
	$pdf->Text(88, 139.8, $r['edu_number']);
	$pdf->Text(95.4, 145.8, $r['edu_serie']);
	$pdf->Text(112.6, 145.8, $r['edu_number']);
}


$pdf->SetFont("times", "", 11);
$pdf->Text(151.8, 207.2, "3"); // количество договоров мы-студент

$pdf->SetFont("times", "", 14);
$pdf->Text(35.8, 262.4, mb_strtolower(russian_date( mktime(), 'j           F' ), 'UTF-8'));
$pdf->Text(84.8, 262.4, substr(date('y'),1));

$pdf->Output('opis.pdf', 'D');
?>
