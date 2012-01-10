<?php
// just require TCPDF instead of FPDF
require_once('../../../modules/tcpdf/tcpdf.php');
require_once('../../../modules/fpdi/fpdi.php');
require_once('../../../modules/russian_date.php');
require_once('../../../modules/mysql.php');
require_once('../class/catalog.class.php');
require_once('../../conf.php');

$msl = new dMysql();

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
            $this->setSourceFile('diplom.pdf');
            $this->_tplIdx = $this->importPage(1);
        }
    }
    
    function Footer() {}
}



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

$pdf->SetFont("times", "", 13);

$pdf->Text(110, 45, $r['surname']);
$pdf->Text(110, 53.6, $r['name']." ".$r['second_name']);

$rval = getarray("SELECT catalog FROM reg_request WHERE applicant_id='".$applicant_id."' LIMIT 1;", 0);
$cat = new Catalog();
$spc = $cat->getInfo($rval['catalog']);

$pdf->SetFont("times", "", 12);
$pdf->Text(109.8, 62.4, $spc['type']);

$pdf->SetFont("times", "", 13);

$arr = splitstring($spc['name'], 50);
$pdf->Text(110, 69.4, $arr[0]);
$pdf->Text(110, 76.4, $arr[1]);


$rval = $msl->getarray("SELECT b.name_rp,serie,number,institution,date FROM reg_applicant_edu_doc a LEFT JOIN reg_edu_doc b ON a.edu_doc=b.id WHERE applicant='".$applicant_id."' AND `primary`='1'");

$pdf->SetFont("times", "", 14);

$arr = splitstring($rval['name_rp'], array(34,85));
$pdf->Text(115.0, 111.2, $arr[0]);
$pdf->Text(30, 123.6, $arr[1]);
$pdf->Text(30, 135.6, $arr[2]);

$pdf->Text(119.8, 135.6, $rval['serie']);
$pdf->Text(160.5, 135.6, $rval['number']);


$arr = splitstring($rval['institution'].", ".mb_strtolower(russian_date( strtotime($rval['date']), 'j F Y' ), 'UTF-8'), 60);
$pdf->Text(54, 144.2, $arr[0]);
$pdf->Text(30, 156.2, $arr[1]);

$pdf->Output('zayavl.pdf', 'D');
?>
