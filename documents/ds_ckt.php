<?php
// just require TCPDF instead of FPDF
require_once('../../../modules/tcpdf/tcpdf.php');
require_once('../../../modules/fpdi/fpdi.php');
require_once('../../../modules/russian_date.php');
require_once('../../../modules/mysql.php');
require_once('../../conf.php');
require_once('../class/price.class.php');

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
            $this->setSourceFile('ds_ckt.pdf');
            $this->_tplIdx = $this->importPage(1);
        }
    }
    
    function Footer() {}
}


$req = getarray("SELECT * FROM reg_request 
WHERE id = ".$_REQUEST['request_id'].";");

$applicant_id = $req['applicant_id'];

// --- Базовый запрос (сведения об абитуриенте) --- //
$r = getarray("SELECT * FROM reg_applicant 
WHERE reg_applicant.id = ".$applicant_id.";");

// initiate PDF
$pdf = new PDF();
$pdf->SetMargins(PDF_MARGIN_LEFT, 40, 0);
$pdf->SetAutoPageBreak(true, 0);

// add a page
$pdf->AddPage();
$pdf->useTemplate($pdf->_tplIdx);

$pdf->SetFont("times", "", 12);
$pdf->Text(52, 60.2, $r['surname']." ".$r['name']." ".$r['second_name']);

$ival = getarray("SELECT pay FROM reg_institution_additional WHERE `request_id`='".$_REQUEST['request_id']."'");
$pdf->Text(162, 102.2, $ival['pay']);

$price = new Price();
$pay = $price->getPriceByRegion($r['region'], $req['catalog'], 3, $ival['pay'], 0, 0);
unset($price);

$pdf->Text(80, 138.2, $pay[0]);

$pdf->Text(160, 227, substr($r['name'],0,2).".".(($r['second_name'] != "")?substr($r['second_name'],0,2).". ":" ").$r['surname']);
//$pdf->Text(126, 234, mb_strtolower(russian_date( mktime(), 'j        F' ), 'UTF-8'));
//$pdf->Text(164.6, 234, substr(date('y'),1));

$pdf->Output('ds.pdf', 'D');
?>
