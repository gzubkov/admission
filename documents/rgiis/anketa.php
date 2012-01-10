<?php
// just require TCPDF instead of FPDF
require_once('../../../../modules/tcpdf/tcpdf.php');
require_once('../../../../modules/fpdi/fpdi.php');
require_once('../../../../modules/russian_date.php');
require_once('../../../../modules/mysql.php');
require_once('../../../conf.php');
require_once('../../class/catalog.class.php');
require_once('../../class/rights.class.php');

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
            $this->setSourceFile('anketa.pdf');
            $this->_tplIdx = $this->importPage(1);
        }
    }
    
    function Footer() {}
}

$rg = new Rights();
if (is_numeric($_REQUEST['applicant'])) {
    if (!$rg->checkApplicant($applicant_id,'printdocuments')) exit(0);
    $applicant_id = $_REQUEST['applicant'];
    $type = "self";
} elseif (is_numeric($_REQUEST['id'])) {
    $applicant_id = $_REQUEST['id'];
    $type = "region";
} else {
    $rg->printError();
    exit(0);
}

$msl = new dMysql();
// --- Базовый запрос (сведения об абитуриенте, регион, цена (руб., коп.)) --- //
$r = $msl->getarray("SELECT surname,name,second_name,region,`homeaddress-region`,`homeaddress-index`,`homeaddress-city`,`homeaddress-street`,`homeaddress-home`,`homeaddress-building`,`homeaddress-flat`,regaddress,homephone_code,homephone,mobile_code,mobile FROM ".(($type == "self") ? "reg" : "partner")."_applicant WHERE id = ".$applicant_id.";");

if ($type == "region" && !$rg->checkRegion($r['region'],'printdocuments')) {
    $rg->printError();
    exit(0);
} 
unset($rg);

// initiate PDF
$pdf = new PDF();
$pdf->SetMargins(PDF_MARGIN_LEFT, 40, 0);
$pdf->SetAutoPageBreak(true, 0);

// add a page
$pdf->AddPage();
$pdf->useTemplate($pdf->_tplIdx);

$pdf->SetFont("times", "B", 13);
$pdf->Text(190.5, 12.8, $applicant_id); // номер анкеты

$pdf->SetFont("times", "", 14);
$arr = splitstring($r['surname']." ".$r['name']." ".$r['second_name'], 44, 1); 
$pdf->Text(84.5, 68.4, $arr[0]);
$pdf->Text(30,   78.4, $arr[1]);

$rval = $msl->getarray("SELECT reg_rf_subject.name FROM reg_rf_subject WHERE reg_rf_subject.id='".$r['homeaddress-region']."'");
$pdf->SetFont("times", "", 13);
$string = $r['homeaddress-index'].", ".$rval['name'].", ".$r['homeaddress-city'];
if ($r['homeaddress-street']   != "") $string .= ", ".$r['homeaddress-street'];
if ($r['homeaddress-home']     != "") $string .= ", дом ".$r['homeaddress-home'];
if ($r['homeaddress-building'] != "") $string .= "/".$r['homeaddress-building'];
if ($r['homeaddress-flat']      > 0 ) $string .= ", ".$r['homeaddress-flat'];

if ($string == $r['regaddress']) {
    $arr = splitstring($string, 60, 1); 
    $pdf->Text(31, 112.5, $arr[0]);
    $pdf->Text(31, 117.5, $arr[1]);
} else {
    $arr = splitstring("Проживания: ".$string.";", 60, 1);
    $y = 112.5; 
    foreach($arr as $v) {
        $pdf->Text(31, $y, $v);
        $y += 5;
    }
    
    $y+=5;
    $arr = splitstring("Регистрации: ".$r['regaddress'].".", 60, 1);
    foreach($arr as $v) {
        $pdf->Text(31, $y, $v);
        $y += 5;
    }
}

$pdf->Text(151, 115, "+7 (".$r['homephone_code'].") ".$r['homephone']);
$pdf->Text(151, 124, "+7 (".$r['mobile_code'].") ".$r['mobile']);

//
// ------------------------------------------------------------
//
$pdf->addPage(); 
$pdf->useTemplate($pdf->importPage(2));
$pdf->SetFont("times", "I", 14);
$pdf->Text(21,107.4, mb_strtolower(russian_date( time(), 'j        F' ), 'UTF-8'));
$pdf->Text(75.5,107.4, substr(date('y'),1));

$pdf->Output('RGIISanketa.pdf', 'D');
?>
