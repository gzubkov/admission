<?php
// just require TCPDF instead of FPDF
require_once('../../../modules/tcpdf/tcpdf.php');
require_once('../../../modules/fpdi/fpdi.php');
require_once('../../../modules/russian_date.php');
require_once('../../../modules/mysql.php');
require_once('../../conf.php');

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

$msl = new dMysql();
$applicant_id = $_REQUEST['applicant_id'];

// --- Базовый запрос (сведения об абитуриенте) --- //
$r = $msl->getarray("SELECT surname,name,second_name FROM reg_applicant 
WHERE reg_applicant.id = ".$applicant_id.";");

// initiate PDF
$pdf = new PDF();
$pdf->SetMargins(PDF_MARGIN_LEFT, 40, 0);
$pdf->SetAutoPageBreak(true, 0);

// add a page
$pdf->AddPage();
$pdf->useTemplate($pdf->_tplIdx);

$pdf->SetFont("times", "I", 13);
$pdf->Text(61, 35.9, $r['surname']." ".$r['name']." ".$r['second_name']);

$rval = $msl->getarray("SELECT edu_doc, serie, number FROM reg_applicant_edu_doc 
                        WHERE applicant='".$applicant_id."' AND `primary`='1'",1);

$pdf->SetFont("times", "I", 13);

if (!is_array ($rval)) die("Нет добавленных документов.");
foreach($rval as $key=>$val) {
    switch($val['edu_doc']) {
        case 1:
            // аттестат 
            $pdf->Text(72.8, 132.2, $val['serie']);
            $pdf->Text(90.8, 132.2, $val['number']);
            $pdf->Text(97.1, 138.5, $val['serie']);
            $pdf->Text(112.8, 138.5, $val['number']);
	    break;

        case 6: 
            $pdf->Text(94.8, 121.6, $val['number']);
	    break;

        case 7: 
            $pdf->SetXY(94.8, 128.4); 
	    $pdf->Write(0, $val['number']);
	    break;

        default:
            $pdf->Text(72.4, 144.4, $val['serie']);
	    $pdf->Text(89.4, 144.4, $val['number']);
	    $pdf->Text(96, 150.2, $val['serie']);
	    $pdf->Text(113.4, 150.2, $val['number']);
    }
}

$pdf->SetFont("times", "", 11);
$pdf->Text(153, 217.5, "2"); // количество договоров мы-студент

$rval = $msl->getarray("SELECT rups, pay FROM reg_institution_additional a LEFT JOIN reg_request b ON a.request_id=b.id 
                        WHERE b.applicant_id='".$applicant_id."' LIMIT 1",0);
if ($rval['rups'] > 0) {
    $pdf->Text(156, 120.5, "1");
}

if ($rval['pay'] > 0) {
    $pdf->Text(153, 224.5, "2 экз.");
}

$pdf->SetFont("times", "", 14);
//$pdf->Text(36.8, 257, mb_strtolower(russian_date( mktime(), 'j           F' ), 'UTF-8'));
//$pdf->Text(85.8, 257, substr(date('y'),1));

$pdf->Output('opis.pdf', 'D');
?>
