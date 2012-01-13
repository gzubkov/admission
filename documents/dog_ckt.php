<?php
// just require TCPDF instead of FPDF
require_once('../../../modules/tcpdf/tcpdf.php');
require_once('../../../modules/fpdi/fpdi.php');
require_once('../../../modules/russian_date.php');
require_once('../../../modules/mysql.php');
require_once('../../conf.php');
require_once('../class/catalog.class.php');

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

$pdf->SetFont("times", "I", 13);
$pdf->Text(55, 114, $r['surname']." ".$r['name']." ".$r['second_name']);

$catalog = new Catalog();
$rval = $catalog->getInfo($req['catalog'], $req['profile']);
unset($catalog);

if ($rval['shortname'] != '') $pdf->Text(115, 17.8, "-".$rval['shortname']."-");

/*
if ($rval['typen'] == 2) {
    $pdf->Rect( 19, 153, 178, 5, 'F', 0, array(255,255,255)); 
    $pdf->SetFont("times", "", 12);
    $pdf->Text(20, 156.8, "тельных        технологий        на        платной        основе        по        направлению        подготовки"); 
}
*/
if ($rval['typen'] == 1) { 
    $rval['name'] = "специальности ".$rval['name'];
    $pdf->Text(140.2, 192, "5 лет"); // специальность - нормативный срок
    $pdf->Text(167, 196.8, $rval['term']." лет"); // специальность - срок
} else {
    $rval['name'] = "направлению подготовки ".$rval['name'];
/*    if ($rval['profile'] != '') {
        $rval['name'] .= " (профиль - ".$rval['profile'].")";
    } */
    $pdf->Text(140.2, 192, "4 года"); 
    $pdf->Text(167, 196.8, floor($rval['term'])." года ".(12*($rval['term'] - floor($rval['term'])))." мес"); // специальность - срок
}


$pdf->SetFont("times", "I", 13);
$arr = splitstring($rval['name'], 85, 1); 
$pdf->Text(22, 166.8, $arr[0]); 
if (isset($arr[1])) $pdf->Text(22, 171.8, $arr[1]); 

$pdf->Text(166.2, 211.6, $rval['qualify']); // специальность - квалификация

$pdf->Text(75.8, 264.1, $req['semestr']); // семестр
$pdf->Text(101, 264.1, ceil($req['semestr']/2)); // курс

$pdf->addPage(); 
$pdf->useTemplate($pdf->importPage(2));

$pdf->addPage(); 
$pdf->useTemplate($pdf->importPage(3));

$pdf->addPage(); 
$pdf->useTemplate($pdf->importPage(4));

$pdf->SetFont("times", "I", 13);
$pdf->Text(64, 82.4, $r['surname']." ".$r['name']." ".$r['second_name']);

$pdf->Text(50, 92.2, $r['citizenry']);
$pdf->Text(160, 92.2, date('d.m.Y', strtotime($r['birthday'])));

// паспорт
$pdf->Text(58.2, 99.6, $r['doc_serie']."           ".$r['doc_number']);
$pdf->Text(137, 99.6, date('d.m.Y', strtotime($r['doc_date'])));

$arr = splitstring($r['doc_issued'], 68, 1); 

$pdf->Text(16, 104, $arr[0]);
$pdf->Text(16, 111.2, $arr[1]);


$rval = getarray("SELECT name FROM reg_rf_subject WHERE reg_rf_subject.id='".$r['homeaddress-region']."'");

$string = $r['homeaddress-index'].", ".$rval['name'].", ".$r['homeaddress-city'].", ";
if ($r['homeaddress-street'] != '') $string.=$r['homeaddress-street'].", ";
$string .= $r['homeaddress-home'];
if ($r['homeaddress-building'] != '') $string .= "/".$r['homeaddress-building'];
if ($r['homeaddress-flat'] != '' && $r['homeaddress-flat'] != 0) $string .= ", ".$r['homeaddress-flat'];

$arr = splitstring($r['regaddress'], 55, 1); 

$pdf->Text(75.4, 118.2, $arr[0]);
$pdf->Text(16, 125, $arr[1]);

$arr = splitstring($string, 58, 1); 
$pdf->Text(61, 132, $arr[0]);
$pdf->Text(16, 139, $arr[1]);


$pdf->SetXY(63, 141.8); 
if ($r['homephone_code'] != 0) {
   $pdf->Write(0, "+7 (".$r['homephone_code'].") ".$r['homephone']);
   if ($r['mobile_code'] != 0) $pdf->Write(0, ", ");
}
if ($r['mobile_code'] != 0) $pdf->Write(0, "+7 (".$r['mobile_code'].") ".$r['mobile']);

$pdf->SetFont("times", "", 13);
$pdf->SetXY(160, 257.5); 
if ($r['second_name'] != "") {
   $pdf->Write(0, substr($r['name'],0,2).".".substr($r['second_name'],0,2).". ".$r['surname']);
} else {
   $pdf->Write(0, substr($r['name'],0,2).". ".$r['surname']);
}

$pdf->Output('dogovor.pdf', 'D');
?>
