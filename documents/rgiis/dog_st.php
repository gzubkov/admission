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
            $this->setSourceFile('dog_st.pdf');
            $this->_tplIdx = $this->importPage(1);
        }
    }
    
    function Footer() {}
}


if (!is_numeric($_REQUEST['applicant'])) exit(0);
$applicant_id = $_REQUEST['applicant'];

$msl = new dMYsql();
$r = $msl->getarray("SELECT region,surname,name,second_name,birthday,citizenry,doc_type,doc_serie,doc_number,doc_issued,doc_date,semestr,`homeaddress-index`,`homeaddress-region`,`homeaddress-city`,`homeaddress-street`,`homeaddress-home`,`homeaddress-building`,`homeaddress-flat`,regaddress,homephone_code,homephone,mobile_code,mobile,catalog FROM partner_applicant WHERE id = ".$applicant_id.";");

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

$cat = new Catalog();
$rval = $cat->getInfo($r['catalog']);
unset($cat);

if ($rval['shortname'] != '') $pdf->Text(128, 13.8, "-".$rval['shortname']."-");

$pdf->Text(52, 100.8, $r['surname']." ".$r['name']." ".$r['second_name']);

$pdf->Text(20, 134.8, $rval['name']); // специальность - название
$pdf->Text(161.6, 159.4, $rval['term']." лет"); // специальность - срок
$pdf->Text(77, 174.0, $rval['qualify']); // специальность - квалификация

$pdf->addPage(); 
$pdf->useTemplate($pdf->importPage(2));

$pdf->Text(21, 246.6, $r['semestr']); // семестр
$pdf->Text(44.8, 246.6, ceil($r['semestr']/2)); // курс


$pdf->addPage(); 
$pdf->useTemplate($pdf->importPage(3));

$pdf->addPage(); 
$pdf->useTemplate($pdf->importPage(4));

$pdf->addPage(); 
$pdf->useTemplate($pdf->importPage(5));

$pdf->addPage(); 
$pdf->useTemplate($pdf->importPage(6));

$pdf->SetFont("times", "I", 13);
$pdf->Text(57.4, 135.6, $r['surname']." ".$r['name']." ".$r['second_name']);

$pdf->Text(55, 145.4, $r['citizenry']); // 86.2
$pdf->Text(147, 145.4, date('d.m.Y', strtotime($r['birthday'])));

// паспорт
$pdf->SetXY(51.5, 151.8);
$pdf->Write(0, $r['doc_serie']." ".$r['doc_number']);
$pdf->SetXY(125, 151.8);
$pdf->Write(0, date('d.m.Y', strtotime($r['doc_date'])));

$pdf->SetXY(50.3, 157.6);
$pdf->Write(0, $r['doc_issued']);

$arr = splitstring($r['regaddress'], 60, 1); 
$pdf->Text(80, 168.2, $arr[0]);
$pdf->Text(30, 173.8, $arr[1]);

$rval = $msl->getarray("SELECT reg_rf_subject.name FROM reg_rf_subject WHERE reg_rf_subject.id='".$r['homeaddress-region']."'");
$string = $r['homeaddress-index'].", ".$rval['name'].", ".$r['homeaddress-city'];
if ($r['homeaddress-street']   != "") $string .= ", ".$r['homeaddress-street'];
if ($r['homeaddress-home']     != "") $string .= ", дом ".$r['homeaddress-home'];
if ($r['homeaddress-building'] != "") $string .= "/".$r['homeaddress-building'];
if ($r['homeaddress-flat']      > 0 ) $string .= ", ".$r['homeaddress-flat'];

$arr = splitstring($string, 65, 1); 
$pdf->Text(66, 180, $arr[0]);
$pdf->Text(30, 185.8, $arr[1]);

$string = "";
if ($r['homephone_code'] != 0) {
   $string .= "+7 (".$r['homephone_code'].") ".$r['homephone'];
   if ($r['mobile_code'] != 0) $string .= ", ";
}
if ($r['mobile_code'] != 0) $string .= "+7 (".$r['mobile_code'].") ".$r['mobile'];

$pdf->Text(70, 191.8, $string);
   

$pdf->SetFont("times", "", 12);
if ($r['second_name'] != "") {
   $pdf->Text(140, 266.4, substr($r['name'],0,2).".".substr($r['second_name'],0,2).". ".$r['surname']);
} else {
   $pdf->Text(163, 259.7, substr($r['name'],0,2).". ".$r['surname']);
}
unset($msl);
$pdf->Output('dogovor.pdf', 'D');
?>
