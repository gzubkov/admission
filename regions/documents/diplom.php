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
            $this->setSourceFile('diplom.pdf');
            $this->_tplIdx = $this->importPage(1);
        }
    }
    
    function Footer() {}
}


if (!is_numeric($_REQUEST['applicant'])) {exit(0);}
$applicant_id = $_REQUEST['applicant'];
$msl = new dMysql();
// --- Базовый запрос (сведения об абитуриенте) --- //
$r = $msl->getarray("SELECT region,surname,name,second_name,edu_doc,edu_serie,edu_number,edu_date,edu_institution FROM partner_applicant WHERE id = ".$applicant_id.";");
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

$pdf->SetFont("times", "", 13);
$pdf->SetXY(132, 43.3);
$pdf->Write(0, $r['surname']);

$pdf->SetXY(125, 51.6); // Имя
$pdf->Write(0, $r['name']." ".$r['second_name']);

$pdf->SetFont("times", "", 13);
if ($r['edu_doc'] == 1) {
   $pdf->SetXY(104.3, 84); // аттестат
   $pdf->Write(0, "аттестата");
} else {
   $pdf->SetXY(104.3, 84); // диплом
   $pdf->Write(0, "диплома");
}

$pdf->SetFont("times", "", 12);
$pdf->SetXY(140, 94.6); // диплом-серия
$pdf->Write(0, $r['edu_serie']);
$pdf->SetXY(178, 94.6); // диплом-номер
$pdf->Write(0, $r['edu_number']);


$arr = splitstring($r['edu_institution'].", ".mb_strtolower(russian_date( strtotime($r['edu_date']), 'j F Y' ), 'UTF-8'), array(70,85,85));
$pdf->SetXY(51, 101.7);
$pdf->Write(0, $arr[0]);
$pdf->SetXY(29.5, 111.7);
$pdf->Write(0, $arr[1]);
$pdf->SetXY(29.5, 119.0);
$pdf->Write(0, $arr[2]);
$pdf->SetXY(29.5, 126.4);
$pdf->Write(0, $arr[3]);

$pdf->Output('zayavl.pdf', 'D');
?>
