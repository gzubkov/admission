<?php
// just require TCPDF instead of FPDF
require_once('../../../../modules/tcpdf/tcpdf.php');
require_once('../../../../modules/fpdi/fpdi.php');
require_once('../../../../modules/russian_date.php');
require_once('../../../../modules/mysql.php');
require_once('../../../conf.php');
require_once('../../class/catalog.class.php');

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
$r = $msl->getarray("SELECT catalog,region,surname,name,second_name,edu_doc,edu_serie,edu_number,edu_date,edu_institution FROM partner_applicant WHERE id = ".$applicant_id.";");

$cat = new Catalog();
$univ = $cat->getUniversityInfo($r['catalog']);
unset($cat);
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

$pdf->SetFont("times", "", 12);
$pdf->SetXY(70, 23.3);
$pdf->MultiCell(125,30, $univ['type']." «".$univ['name']."» ".$univ['rsurname_rp']." ".substr($univ['rname_rp'],0,2).". ".substr($univ['rsecond_name_rp'],0,2).".", 0, R);

$pdf->SetFont("times", "", 13);

$pdf->SetXY(132, 43.3);
$pdf->Write(0, $r['surname']);

$pdf->SetXY(125, 51.6); // Имя
$pdf->Write(0, $r['name']." ".$r['second_name']);

$pdf->SetFont("times", "", 13);
if ($r['edu_doc'] == 1) {
   $pdf->Text(104.3, 89, "аттестата"); // аттестат
} else {
   $pdf->Text(104.3, 89, "диплома"); // диплом
}

$pdf->SetFont("times", "", 12);
$pdf->Text(131, 100.2, $r['edu_serie']); // диплом-серия
$pdf->Text(168, 100.2, $r['edu_number']); // диплом-номер

$arr = splitstring($r['edu_institution'].",", 72);
$pdf->Text(51, 107.4, $arr[0]);
$pdf->Text(30, 118.4, $arr[1]);

$pdf->Text(141, 118.4, mb_strtolower(russian_date( strtotime($r['edu_date']), 'j F Y' ), 'UTF-8'));

$pdf->Output('zayavl.pdf', 'D');
?>
