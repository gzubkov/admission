<?php
require_once('../../conf.php');
require_once('../class/pdf.class.php');
require_once('../class/mysql.class.php');
require_once('../class/mssql.class.php');
require_once('../class/catalog.class.php');

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
$mssql = new dMssql();
$r = $mssql->getarray("SELECT surname,name,second_name,catalog,region FROM dbo.student WHERE id='".$student_id."'");

if ($r == 0) {
    print "Данный студент отсутствует в базе данных! Попробуйте зайти позднее.";
    exit(0);
}

$cat = new Catalog($msl);
$spec = $cat->getBaseInfo($r['catalog']);

switch($r['region']) 
{
    case '1':
        $region = "Москва";
    	break;

    case '176':
        $region = "Индивидуалы";
	    break;

    default:
        $reg = $msl->getarray("SELECT name FROM admission.partner_regions WHERE id='".$r['region']."'");
        $region = $reg['name'];
}

$k = $mssql->getarray("SELECT a.control_type,a.mark,a.date,a.hours,a.semestr,a.type,b.name as discipline,c.name as markname 
               FROM dbo.journal a 
               LEFT JOIN dbo.disciplines b ON a.discipline=b.id 
               LEFT JOIN dbo.marks c ON a.mark=c.id 
               WHERE a.id='".$student_id."' ORDER BY a.semestr, b.name ASC",1);

if (isset($_REQUEST['format']) === false) {
    $_REQUEST['format'] = 'PDF';
}

switch($_REQUEST['format']) 
{
    case 'HTML': case 'html':
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html lang="ru">

<head>
<title>Печать сводной ведомости успеваемости</title>
	<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
	<meta http-equiv="Content-Language" content="ru">

<style type="text/css">
body { background-color: white; margin: 0px; text-align: center; font-family: verdana; font-size: 9pt; }
input { font-family: Arial, sans-serif; font-size: 9pt; color: black; background-color: white; border: 1px solid #333; margin: 8pt 8pt 8pt 0; }
#toolbox { font-family: Arial, sans-serif; font-size: 9pt; border-bottom: dashed 1pt black; margin-bottom: 0; padding: 2mm 0 0 0; text-align: justify; }
</style>

<style type="text/css" media="print">
#toolbox { display: none; }
</style>

<style type="text/css">
#toolbox { width: 200mm; margin-left: auto; margin-right: auto; }
.topmargin { height: 12mm; }
</style>
</head>

<body>

<div id="toolbox"><p>Сводная ведомость успеваемости располагается на листе формата А4. Никаких особых настроек для печати документа обычно не требуется.</p><p>Для печати нажмите кнопку напечатать.</p><input type="button" value="Напечатать" onclick="window.print();">
<center><span style="font-size: 80%;">информационный блок от начала страницы до пунктирной линии на печать не выводится</span></center></div>

<CENTER>
<div style="width: 200mm; align: center;">
<table style="width: 100%; font-size: 9pt; border-collapse:collapse; border-bottom: black 1px solid;">

<TR><TD colspan=6 style="text-align: center; font-size: 14pt; border-top: black 1px solid;">Сводная ведомость успеваемости</TD></TR>

<?php 
    print "<TR><TD colspan=6>Номер договора: ".$student_id."</TD></TR><TR><TD colspan=6>".$r['surname']." ".$r['name']." ".$r['second_name']."</TD></TR>"; 
    print "<TR><TD colspan=6>".$spec['abbreviation']." ".$spec['name']."</TD></TR>";
    print "<TR><TD colspan=6 style=\"border-bottom: black 1px solid; line-height: 130%\">Регион ".$region."</TD></TR>";
    print "<TR><TD></TD><TD>Дисциплина</TD><TD>Часы</TD><TD>Вид контроля</TD><TD>Дата сдачи</TD><TD>Оценка</TD></TR>\n";

    $i = 1;
    $sem = 0;

    if (isset($k) && $k != 0) {
        foreach($k as $v) {
    	    if ($v['semestr'] != $sem) {
                $sem = $v['semestr'];
	            $i = 1;
                print "<TR><TD colspan=6 style=\"padding: 3px; font-weight: bold; background-color: #d0d0d0; border:1px solid #000000;\">&nbsp;".$sem." семестр</TD></TR>"; 
            }

	        print "<TR style=\"line-height: 150%; padding:40px; border-bottom: 1px dashed #000000; vertical-align: top;";
    	    
            switch($v['mark'])
    	    {
                case -1: case 2:
            	    print " color: #ff0000;";
	    	        break;

	            case -3:
	                print " color: #0000ff;";
	    	        break;
            }

	        print "\"><TD>".$i."</TD><TD>".$v['discipline']."</TD><TD>".$v['hours']."</TD><TD>".$v['control_type']."</TD><TD>";

   	        if (is_null($v['date']) === false) { 
                print date('d.m.Y', strtotime($v['date']));
            }
	        print "</TD><TD>";

	if (!is_null($v['type'])) {
            print "<I>";  
            if (!is_null($v['markname'])) {
                print $v['markname']." (".$v['type'].")";
	    } else {
                print $v['type'];	     
	    }
	    print "</I>";
	} else {
            print $v['markname'];
        }

	print "</TD></TR>\n";
    	$i++; 
        }
    } else {
        print "<TR><TD colspan=6 style=\"font-size: 14px; text-align: center;\"><b>Данные не могут быть сформированы, попробуйте позднее</b></td></tr>";
    }
    print "</table></div></CENTER></BODY></HTML>";
    break;

case 'PDF': case 'pdf': default:
    class PDF2 extends PDF {
        function Footer() {
            $this->SetY(-12);
            $this->SetFont('verdana', '', 8);
	    $this->Cell(0, 10, date('d.m.Y'), 0, 0, 'C');
	    $this->Cell(0, 10, 'Страница '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, 0, 'R');
        }
    }

    $pdf = new PDF2();

    $pdf->SetMargins(10, 40, 10);
    $pdf->SetAutoPageBreak(true, 8);
    $pdf->setPrintFooter(true);

    $pdf->AddPage();

    $pdf->Line(10, 10, 197.5, 10, array('width' => 0.2));
    $pdf->SetFont("times", "", 18);
    $pdf->Text(70, 16, "Ведомость успеваемости");
    $pdf->SetFont("times", "", 11);
    $pdf->Text(70, 21, $student_id." ".$r['surname']." ".$r['name']." ".$r['second_name']);

    $pdf->Text(70, 25, $spec['abbreviation']." ".$spec['name']);
    $pdf->Text(70, 29, "Регион ".$region);

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

    $i = 1;
    $y = 34;
    $sem = 0;
    if (isset($k) && $k != 0) {
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
    } else {
        $pdf->Text(70, $y+10, "Данные не могут быть сформированы, попробуйте позднее");
    }
    $pdf->Line(10, $y+0.9, 197.5, $y+0.9, array('width' => '0.1', 'dash' => '0'));
    $pdf->Output('journal.pdf', 'D');
}
?>
