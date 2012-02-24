<?php
require_once('../../conf.php');
require_once('../class/mysql.class.php');
require_once('../class/pdf.class.php');

class Applicant
{
    var $_msl;
    var $_id;
    var $dog_num = 2; // количество договоров мы-студент

    public function __construct(&$msl, $id) {
        $this->_msl = $msl; 
	$this->_id = $id;
        return true;
    }

    public function get_surnamens() {
        return $this->_msl->getarray("SELECT surname,name,second_name FROM reg_applicant WHERE reg_applicant.id = ".$this->_id.";");
    }

    public function get_edu_doc() {
        return $this->_msl->getarray("SELECT edu_doc, serie, number FROM reg_applicant_edu_doc WHERE applicant='".$this->_id."' AND `primary`='1'",1);
    }

    public function get_rups() {
        return $this->_msl->getarray("SELECT rups, pay FROM reg_institution_additional a LEFT JOIN reg_request b ON a.request_id=b.id WHERE b.applicant_id='".$this->_id."' LIMIT 1",0);
    }
}

$msl = new dMysql();
$applicant_id = $_REQUEST['applicant_id'];

$appl = new Applicant($msl, $applicant_id);
$r = $appl->get_surnamens();
$rval = $appl->get_edu_doc();

if (!is_array ($rval)) die("Нет добавленных документов.");

$pdf = new PDF();
$pdf->SetMargins(PDF_MARGIN_LEFT, 40, 0);
$pdf->SetAutoPageBreak(true, 0);
$pdf->setSourceFile('opis.pdf');

$pdf->AddPage();
$pdf->useTemplate($pdf->importPage(1));

$pdf->SetFont("times", "I", 13);
$pdf->Text(61, 35.9, $r['surname']." ".$r['name']." ".$r['second_name']);

$pdf->SetFont("times", "I", 13);

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
$pdf->Text(153, 217.5, $appl->dog_num); // количество договоров мы-студент

$rval = $appl->get_rups();
if ($rval['rups'] > 0) {
    $pdf->Text(156, 120.5, "1");
}

if ($rval['pay'] > 0) {
    $pdf->Text(153, 224.5, $appl->dog_num." экз.");
}

$pdf->SetFont("times", "", 14);

$pdf->Output('opis.pdf', 'D');
?>
