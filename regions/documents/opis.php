<?php
// just require TCPDF instead of FPDF
require_once('../../../../modules/tcpdf/tcpdf.php');
require_once('../../../../modules/fpdi/fpdi.php');
require_once('../../../../modules/russian_date.php');
require_once('../../class/mysql.class.php');
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
$r = $msl->getarray("SELECT region,surname,name,second_name,edu_doc,edu_serie,edu_number FROM partner_applicant WHERE id = ".$applicant_id.";");
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
$pdf->SetXY(61, 31.9);
$pdf->Write(0, $r['surname']." ".$r['name']." ".$r['second_name']);

$pdf->SetFont("times", "I", 13);

switch($r['edu_doc']) {
    case 1:
        // аттестат 
        $pdf->SetXY(73.8, 92.6);  
       	$pdf->Write(0, $r['edu_serie']);
        $pdf->SetXY(92.8, 92.6); 
        $pdf->Write(0, $r['edu_number']);
        $pdf->SetXY(100.1, 100.6); 
        $pdf->Write(0, $r['edu_serie']);
        $pdf->SetXY(117.1, 100.6); 
        $pdf->Write(0, $r['edu_number']);
	break;

    default:
        $pdf->SetXY(73.8, 107.6);  
	$pdf->Write(0, $r['edu_serie']);
	$pdf->SetXY(92.8, 107.6); 
	$pdf->Write(0, $r['edu_number']);
	$pdf->SetXY(100, 114.6); 
	$pdf->Write(0, $r['edu_serie']);
	$pdf->SetXY(117.1, 114.6); 
	$pdf->Write(0, $r['edu_number']);
}


$pdf->SetFont("times", "", 11);
$pdf->Text(152.6, 181.2, "3"); // количество договоров мы-студент

$pdf->SetFont("times", "", 14);
$pdf->Text(36.8, 257, mb_strtolower(russian_date( mktime(), 'j           F' ), 'UTF-8'));
$pdf->Text(85.8, 257, substr(date('y'),1));

$pdf->Output('opis.pdf', 'D');
?>
