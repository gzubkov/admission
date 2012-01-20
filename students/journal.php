<?php
require_once('../../conf.php');
require_once('../class/pdf.class.php');
require_once('../class/mysql.class.php');

class PDF2 extends PDF {
    function Footer() {
        $this->SetY(-12);
        $this->SetFont('verdana', '', 8);
	$this->Cell(0, 10, date('d.m.Y'), 0, 0, 'C');
	$this->Cell(0, 10, 'Страница '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, 0, 'R');
    }
}


// initiate PDF
$pdf = new PDF2();

$pdf->SetMargins(10, 40, 10);
$pdf->SetAutoPageBreak(true, 8);
$pdf->setPrintFooter(true);
// add a page
$pdf->AddPage();

class Verification
{
    private $_id;
    private $_type;
	
    public function __construct($id, $type) {
        $this->_id   = $id;
	$this->_type = $type;
	return true;
    }

    public function verify($method) {
        

    }
}

if (isset($_REQUEST['mid'])) {
    if ($_REQUEST['mhash'] == md5(md5('moodle.ins-iit.rudddddsdsd'.$_REQUEST['mid']))) {
        $student_id = $_REQUEST['mid'];
    }	
} else {
    if (isset($_SESSION['rights']) && isset($_SESSION['md_rights'])) {
        if ($_SESSION['rights'] == 'admin' && $_SESSION['md_rights'] == md5($CFG_salted.$_SESSION['rights'])) {
            if (isset($_REQUEST['student'])) {
	        $student_id = $_REQUEST['student'];
	    } else $student_id = $_SESSION['student_id'];
        }
    } else {
        $student_id = $_SESSION['student_id'];
    }
}

if (!is_numeric($student_id)) exit(0);

$msl = new dMysql();

$r = $msl->getarray("SELECT surname,name,second_name,catalog,region FROM students_base.student WHERE id='".$student_id."'");
$pdf->Line(10, 10, 197.5, 10, array('width' => 0.2));
$pdf->SetFont("times", "", 18);
$pdf->Text(70, 16, "Ведомость успеваемости");
$pdf->SetFont("times", "", 11);
$pdf->Text(70, 21, $student_id." ".$r['surname']." ".$r['name']." ".$r['second_name']);

$spec = $msl->getarray("SELECT f.abbreviation, b.name FROM admission.catalogs a 
                  LEFT JOIN admission.specialties b ON a.specialty=b.id 
                  LEFT JOIN admission.`universities_departments` c ON b.department=c.id 
                  LEFT JOIN admission.`universities_faculties` d ON c.faculty=d.id 
		  LEFT JOIN admission.`universities` f ON d.university=f.id 		  
                  WHERE a.base_id='".$r['catalog']."'");
$pdf->Text(70, 25, $spec['abbreviation']." ".$spec['name']);

switch($r['region']) 
{
    case '1':
        $pdf->Text(70, 29, "Регион Москва");
	break;

    case '176':
        $pdf->Text(70, 29, "Регион Индивидуалы");
	break;

    default:
        $reg = $msl->getarray("SELECT name FROM admission.partner_regions WHERE id='".$r['region']."'");
        $pdf->Text(70, 29, "Регион ".$reg['name']);
}

$pdf->Line(10, 30, 197.5, 30, array('width' => 0.1));
$pdf->SetFont("times", "B", 8);
$pdf->Text(40,  32.6, "Дисциплина");
$pdf->Text(98, 32.6, "Часы");
$pdf->Text(110, 32.6, "Вид контроля");
$pdf->Text(140, 32.6, "Дата сдачи");
$pdf->Text(160, 32.6, "Оценка");
$pdf->Line(10, 33.6, 197.5, 33.6, array('width' => 0.1));

$pdf->SetFont("times", "", 10);
$pdf->SetFillColor(224, 235, 255);

$k = $msl->getarray("SELECT a.control_type,a.mark,a.date,a.hours,a.semestr,a.type,b.name as discipline,c.name as markname 
               FROM `students_base`.journal a 
               LEFT JOIN `students_base`.disciplines b ON a.discipline=b.id 
               LEFT JOIN `students_base`.marks c ON a.mark=c.id 
               WHERE a.id='".$student_id."' ORDER BY a.semestr, b.name ASC",1);


$i = 1;
$y = 34;
$sem = 0;
foreach($k as $v) {
    if ($v['semestr'] != $sem) {
        $sem = $v['semestr'];
	$i = 1;
        $pdf->SetXY(10, $y);
	$pdf->SetTextColor(0);
	$pdf->SetLineStyle(array('width' => 0.1, 'dash' => 0));
	$pdf->Cell(25, 4, $sem." семестр", 1, 1, 'C', 1, 0); 
	$y += 8;
    }

    switch($v['mark'])
    {
        case -1: case 2:
            $pdf->SetTextColor(255,0,0);
	    break;

	case -3:
	    $pdf->SetTextColor(0,255,0);
	    break;

	default:
	    $pdf->SetTextColor(0);
    }
    $pdf->Text(10, $y, $i);
    $pdf->Text(15, $y, mb_substr($v['discipline'], 0, 90));
    $pdf->Text(100, $y, $v['hours']);
    $pdf->Text(110, $y, $v['control_type']);
    if (!is_null($v['date'])) { 
        $pdf->Text(140, $y, date('d.m.Y', strtotime($v['date'])));
    }
    
    if (!is_null($v['type'])) {
        $pdf->SetFont("times", "I", 10);
        if (!is_null($v['markname'])) {
             $pdf->Text(160, $y, $v['markname']." (".$v['type'].")");
	} else {
             $pdf->Text(160, $y, $v['type']);	     
	}
	$pdf->SetFont("times", "", 10);
    } else {
        $pdf->Text(160, $y, $v['markname']);
    }
    $pdf->Line(10, $y+0.9, 197.5, $y+0.9, array('width' => '0.01', 'dash' => '1,4'));
    $i++;
    $y += 3.8;

    if ($y >= 280) {
        $y = 10;
        $pdf->AddPage();
    } 
}

$pdf->Line(10, $y+0.9, 197.5, $y+0.9, array('width' => '0.1', 'dash' => '0'));
$pdf->Output('journal.pdf', 'D');
?>
