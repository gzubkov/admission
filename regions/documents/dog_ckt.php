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
            $this->setSourceFile('dog_ckt.pdf');
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
$pdf->SetXY(51, 105.3);
$pdf->Write(0, $r['surname']." ".$r['name']." ".$r['second_name']);

$cat = new Catalog();
$rval = $cat->getInfo($r['catalog']);
unset($cat);

$pdf->SetXY(125, 17.8); // номер
if ($rval['shortname'] != '') $pdf->Write(0, "-".$rval['shortname']."-");

$pdf->SetFont("times", "I", 13);
$arr = splitstring($rval['name'], 85, 1); 
$pdf->SetXY(19, 157.8); // специальность - название
$pdf->Write(0, $arr[0]);
$pdf->SetXY(19, 162.7); // специальность - название
$pdf->Write(0, $arr[1]);

$pdf->SetXY(130.2, 182); // специальность - нормативный срок
$pdf->Write(0, "5 лет");

$pdf->SetXY(164.2, 186.8); // специальность - срок
$pdf->Write(0, $rval['term']." лет");

$pdf->SetXY(134.2, 201.4); // специальность - квалификация
$pdf->Write(0, $rval['qualify']);

$pdf->SetXY(139.8, 254.2); // семестр
$pdf->Write(0, $r['semestr']);

$pdf->SetXY(164, 254.2); // курс
$pdf->Write(0, ceil($r['semestr']/2));

$pdf->addPage(); 
$pdf->useTemplate($pdf->importPage(2));

$pdf->addPage(); 
$pdf->useTemplate($pdf->importPage(3));

$pdf->addPage(); 
$pdf->useTemplate($pdf->importPage(4));

$pdf->SetFont("times", "I", 13);
$pdf->SetXY(64.4, 76.4);
$pdf->Write(0, $r['surname']." ".$r['name']." ".$r['second_name']);

$pdf->SetXY(55, 86.2);
$pdf->Write(0, $r['citizenry']);

$pdf->SetXY(147, 86.2);
$pdf->Write(0, date('d.m.Y', strtotime($r['birthday'])));

// паспорт
$pdf->SetXY(51.5, 96.2);
$pdf->Write(0, $r['doc_serie']." ".$r['doc_number']);
$pdf->SetXY(126, 96.2);
$pdf->Write(0, date('d.m.Y', strtotime($r['doc_date'])));

$pdf->SetXY(51.3, 105.7);
$pdf->Write(0, $r['doc_issued']);


$regh = $msl->getarray("SELECT name FROM reg_rf_subject WHERE reg_rf_subject.id='".$r['homeaddress-region']."'");

$string = $r['homeaddress-index'].", ".$regh['name'].", ".$r['homeaddress-city'].", ";
if ($r['homeaddress-street'] != '') $string.=$r['homeaddress-street'].", ";
$string .= $r['homeaddress-home'];
if ($r['homeaddress-building'] != '') $string .= "/".$r['homeaddress-building'];
if ($r['homeaddress-flat'] != '' && $r['homeaddress-flat'] != 0) $string .= ", ".$r['homeaddress-flat'];

$arr = splitstring($r['regaddress'], 55, 1); 

$pdf->SetXY(80, 115.4); 
$pdf->Write(0, $arr[0]);
$pdf->SetXY(30, 120.4); 
$pdf->Write(0, $arr[1]);

$pdf->SetXY(65, 130); 
$pdf->Write(0, $string);

$pdf->SetXY(69, 139.7); 
if ($r['homephone_code'] != 0) {
   $pdf->Write(0, "+7 (".$r['homephone_code'].") ".$r['homephone']);
   if ($r['mobile_code'] != 0) $pdf->Write(0, ", ");
}
if ($r['mobile_code'] != 0) $pdf->Write(0, "+7 (".$r['mobile_code'].") ".$r['mobile']);

$pdf->SetFont("times", "", 13);
$pdf->SetXY(163, 245.7); 
if ($r['second_name'] != "") {
   $pdf->Write(0, substr($r['name'],0,2).".".substr($r['second_name'],0,2).". ".$r['surname']);
} else {
   $pdf->Write(0, substr($r['name'],0,2).". ".$r['surname']);
}
unset($msl);
$pdf->Output('dogovor.pdf', 'D');
?>
