<?php
// just require TCPDF instead of FPDF
require_once('../../../modules/russian_date.php');
require_once('../../../modules/mysql.php');
require_once('../../conf.php');

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
        $student_id = $_REQUEST['student'];
    	if (!is_numeric($_REQUEST['student'])) $student_id = $_SESSION['student_id'];
    }
} else {
    $student_id = $_SESSION['student_id'];
}
}

if (!is_numeric($student_id)) exit(0);

$r = getarray("SELECT surname,name,second_name,catalog,region FROM students_base.student WHERE id='".$student_id."'");

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

$spec = getarray("SELECT f.abbreviation, b.name FROM admission.catalogs a 
                  LEFT JOIN admission.specialties b ON a.specialty=b.id 
                  LEFT JOIN admission.`universities_departments` c ON b.department=c.id 
                  LEFT JOIN admission.`universities_faculties` d ON c.faculty=d.id 
		  LEFT JOIN admission.`universities` f ON d.university=f.id 		  
                  WHERE a.base_id='".$r['catalog']."'");
print "<TR><TD colspan=6>".$spec['abbreviation']." ".$spec['name']."</TD></TR>";

print "<TR><TD colspan=6 style=\"border-bottom: black 1px solid; line-height: 130%\">Регион ";
switch($r['region']) 
{
    case '1':
        print "Москва";
	break;

    case '176':
        print "Индивидуалы";
	break;

    default:
        $reg = getarray("SELECT name FROM admission.partner_regions WHERE id='".$r['region']."'");
        print $reg['name'];
}
print "</TD></TR>";

print "<TR><TD></TD><TD>Дисциплина</TD><TD>Часы</TD><TD>Вид контроля</TD><TD>Дата сдачи</TD><TD>Оценка</TD></TR>\n";

$k = getarray("SELECT a.control_type,a.mark,a.date,a.hours,a.semestr,a.type,b.name as discipline,c.name as markname 
               FROM `students_base`.journal a 
               LEFT JOIN `students_base`.disciplines b ON a.discipline=b.id 
               LEFT JOIN `students_base`.marks c ON a.mark=c.id 
               WHERE a.id='".$student_id."' ORDER BY a.semestr, b.name ASC",1);

$i = 1;
$sem = 0;




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

	default:
	    //$pdf->SetTextColor(0);
    }
print "\"><TD>".$i."</TD><TD>".$v['discipline']."</TD><TD>".$v['hours']."</TD><TD>".$v['control_type']."</TD><TD>";
   if (!is_null($v['date'])) { 
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
print "</table>";
?>
</div></CENTER>
</BODY></HTML>