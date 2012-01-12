<?php
require_once('../../../modules/russian_date.php');
require_once('../../../modules/mysql.php');
require_once('../../conf.php');
require_once('../class/price.class.php');

if (!isset($_REQUEST['format']) || $_REQUEST['format'] != 'html') $_REQUEST['format'] = 'pdf';

class Receipt
{
    private $_id;
    private $_semestr;
    private $_region;
    private $_pgid = 0;

    public function __construct($id, $date=0) {
        $this->_id = $id;
	$this->_date = $date;
	return true;
    }

    public function getStudent($purpose=2, $count=1) {
        $student = array();
        $r = getarray("SELECT surname, name, second_name, address, semestr, catalog, iit_ckt, region FROM `students_base`.student WHERE id='".$this->_id."'");   
        
	if ($r['region'] == 1) {
	    $this->_region  = (($r['iit_ckt'] == 1) ? 4 : 3);
	} elseif($r['region'] == 176) {
	    $this->_region  = (($r['iit_ckt'] == 1) ? 2 : 1);
	} else {
	    $this->_region  = array((($r['iit_ckt'] == 1) ? 2 : 1), $r['region']);
	}
	$this->_semestr = $r['semestr']+1;

	$student['fio'] = $r['surname']." ".$r['name']." ".$r['second_name'];
	$student['address'] = $r['address'];
	$student['region'] = $this->getRegion();

	$price = new Price();
	$student['price'] = $price->getPriceByStudent($this->_id, $purpose, $count, $this->_date);
	$student['purpose_text'] = $this->getPurposeText($purpose);
        return $student;
    }

    public function getApplicant($purpose=1, $count=1) {
        $r = getarray("SELECT region,surname,name,second_name,regaddress,catalog,semestr,pay FROM partner_applicant WHERE id='".$this->_id."'");
	$this->_region = array(1, $r['region']);   
	
        if ($purpose == 3) {
            $count = $r['pay'];
        }
        $student['fio'] = $r['surname']." ".$r['name']." ".$r['second_name'];
	$student['address'] = $r['regaddress'];
	$student['region'] = $this->getRegion();

	$price = new Price();
	$student['price'] = $price->getPriceByPgid($this->_pgid, $r['catalog'], $purpose, $count, $this->_date);

        $student['purpose_text'] = $this->getPurposeText($purpose);
        return $student;
    }
    
    public function getSelfApplicant($purpose=1, $count=1) {
        $r = getarray("SELECT applicant_id,catalog FROM reg_request WHERE id='".$this->_id."' LIMIT 1;");
	$k = getarray("SELECT surname,name,second_name,regaddress,region FROM reg_applicant WHERE id='".$r['applicant_id']."' LIMIT 1;");
	$this->_region = $k['region'];   
	
        if ($purpose == 3) {
	    $m = getarray("SELECT pay FROM `reg_institution_additional` WHERE `request_id` = '".$this->_id."' LIMIT 1;");
            $count = $m['pay'];
        }
        $student['fio'] = $k['surname']." ".$k['name']." ".$k['second_name'];
	$student['address'] = $k['regaddress'];
	$student['region'] = $this->getRegion();

	$price = new Price();
	$student['price'] = $price->getPriceByPgid($this->_pgid, $r['catalog'], $purpose, $count, $this->_date, 0);
        $student['purpose_text'] = $this->getPurposeText($purpose);

        return $student;
    }
    
    public function getDefault($partner_id, $region, $catalog, $purpose=1, $count=1) {
        if ($partner_id != 0) {
             $this->_region = array($partner_id, $region);  
	} else {
             $this->_region = $region;  
	} 

        $student['fio'] = $_REQUEST['fio'];
	$student['address'] = $_REQUEST['address'];
	$student['region'] = $this->getRegion();

	$price = new Price();
	$student['price'] = $price->getPriceByPgid($this->_pgid, $catalog, $purpose, $count, $this->_date, sizeof($this->_region)-1);
// temp
	$msl = new dMysql();
	$r = $msl->getarray("SELECT `start_semestr`, term FROM admission.catalogs WHERE id=".$catalog." LIMIT 1;",0);
//	unset($msl);

	if ($purpose == 2 && $_REQUEST['s'] >= ($r['start_semestr'] + $r['term'] * 2)) {
	    $student['price'][0] = $student['price'][0]*1.5;
	    $this->_semestr = $r['start_semestr'] + $r['term'] * 2;
	} else {
	    $this->_semestr = $_REQUEST['s'];
	}
// temp

        $student['purpose_text'] = $this->getPurposeText($purpose);
        return $student;
    }

    public function getRegion() {
        $id = $this->_region;
        if (is_array($id)) {
    	    $r = getarray("SELECT * FROM admission.`partner_regions` WHERE partner_regions.id = ".$id[0]." OR partner_regions.id = ".$id[1]." ORDER BY id ASC;",1);
	    $this->_pgid = $r[1]['pgid'];	
      	} else { 
	    $r = getarray("SELECT * FROM admission.`partner_regions` WHERE partner_regions.id = ".$id." LIMIT 1;",1);
	    $this->_pgid = $r[0]['pgid'];
	}
	return $r;
    }

    public function getPurposeText($purpose) {
        $purpose = getarray("SELECT text FROM admission.receipt_purpose WHERE id='".$purpose."' LIMIT 1;");
        $string = $purpose['text']." НДС не облагается";

        if ($this->_id == '') {
            $string = str_replace("%dn%", "        ", $string);
        } else {
            $string = str_replace("%dn%", $this->_id, $string);
	}
	
        if ($this->_semestr == '') {
   	    $string = str_replace("%s%", "  ", $string);
	} else {
   	    $string = str_replace("%s%", $this->_semestr, $string);
	}
	return $string;
    }
}

if ($_REQUEST['count'] < 1) $_REQUEST['count'] = 1;
if (!is_numeric($_REQUEST['purpose'])) $_REQUEST['purpose'] = 1;

if (isset($_REQUEST['mid'])) {
    if ($_REQUEST['mhash'] == md5(md5("moodle.ins-iit.rudddddsdsd".$_REQUEST['mid'])) || $_SERVER['REMOTE_ADDR'] == $CFG_trustedip) {
        $_REQUEST['student'] = $_REQUEST['mid'];
    } else {
        exit(0);
    }
}

if ($_REQUEST['student'] > 0) {
    if (!is_numeric($_REQUEST['purpose'])) {
         $_REQUEST['purpose'] = 2;
    }
    $receipt = new Receipt($_REQUEST['student'], $_REQUEST['date']);
    $student = $receipt->getStudent($_REQUEST['purpose'], $_REQUEST['count']);

} else if ($_REQUEST['applicant'] > 0) {
    /*if ($_SESSION['joomlaregion'] > 0 && $_REQUEST['region_id'] != $_SESSION['joomlaregion']) {
        exit(0);
    }*/

    $receipt = new Receipt($_REQUEST['applicant'], $_REQUEST['date']);
    $student = $receipt->getApplicant($_REQUEST['purpose'], $_REQUEST['count']);
    
} else if ($_REQUEST['request_id'] > 0) {
    $receipt = new Receipt($_REQUEST['request_id']);
    $student = $receipt->getSelfApplicant($_REQUEST['purpose'], $_REQUEST['count']);
    
} else {
    $receipt = new Receipt($_REQUEST['dn'], $_REQUEST['date']);
    $student = $receipt->getDefault($_REQUEST['partner_id'], $_REQUEST['region_id'], $_REQUEST['catalog'], $_REQUEST['purpose'], $_REQUEST['count']);
}

$purpose = $student['purpose_text'];
$rval = $student['region'];
$price = $student['price'];

if (sizeof($price) == 1 || $price[1] == 0) {
    $kn = 1;
} else {
    $kn = 2;
}

switch ($_REQUEST['format']) {
case 'pdf':
    // just require TCPDF instead of FPDF
    require_once('../../../modules/tcpdf/tcpdf.php');
    require_once('../../../modules/fpdi/fpdi.php');

    class PDF extends FPDI {
         var $_tplIdx;
   	 function Header() {}
	 function Footer() {}
	 function printInCells($text, $x, $y, $pitch) {
      	     for ($i = 0; $i < strlen($text); $i++) $this->Text($x+$i*$pitch, $y, $text[$i]);
      	 }
    }

    $pdf = new PDF();
    $pdf->SetMargins(PDF_MARGIN_LEFT, 40, 0);
    $pdf->SetAutoPageBreak(true, 0);
    $pdf->setSourceFile('kvit_'.$kn.'.pdf');

    $pdf->AddPage();
    $pdf->useTemplate($pdf->importPage(1));
    $pdf->SetFont("times", "", 10);

    $y = 0;

    for ($i = 0; $i < $kn; $i++) {
        $pdf->Text(68, $y+21.6, $rval[$i]['firm']); 
	$pdf->Text(68, $y+81.0, $rval[$i]['firm']); 

	if ($rval[$i]['inn'] != 0) {
	    $pdf->printInCells($rval[$i]['inn'], 69, $y+27.8, 3.69);
	    $pdf->printInCells($rval[$i]['inn'], 69, $y+89, 3.69);
	}
	if ($rval[$i]['rs'] != 0) {
	    $pdf->printInCells($rval[$i]['rs'], 118.2, $y+27.8, 3.69);
	    $pdf->printInCells($rval[$i]['rs'], 118.2, $y+89, 3.69);
	}

	$pdf->Text(70.2, $y+35.6, $rval[$i]['bank']);
	$pdf->Text(70.2, $y+98.4, $rval[$i]['bank']); //102.8

	if ($rval[$i]['bik'] != 0) {
	    $pdf->printInCells($rval[$i]['bik'], 158.6, $y+35.2, 3.69);
	    $pdf->printInCells($rval[$i]['bik'], 158.6, $y+98, 3.69); // 102.6
	}
	if ($rval[$i]['ks'] != 0) {
	    $pdf->printInCells($rval[$i]['ks'], 118.2, $y+41.8, 3.69);
	    $pdf->printInCells($rval[$i]['ks'], 118.2, $y+106.6, 3.69); // 110.3
	}

	$pdf->Text(68, $y+46.4, $purpose);
	$pdf->Text(68, $y+112.6, $purpose);

	if (is_array($student)) {
    	    $pdf->Text(94, $y+52.4, $student['fio']); 
    	    $pdf->Text(94, $y+56.2, $student['address']);
    	    $pdf->Text(94, $y+120.0, $student['fio']); 
    	    $pdf->Text(94, $y+125.2, $student['address']);    
	}

	 if ($price[$i] > 0) {
   	     $pdf->Text(88, $y+60.2, floor($price[$i])); //61.4
	     $pdf->Text(88, $y+130,  floor($price[$i]));
   	     $pdf->Text(105.4, $y+60.2, sprintf("%02d", $price[$i]-floor($price[$i])));
   	     $pdf->Text(105.4, $y+130, sprintf("%02d", $price[$i]-floor($price[$i])));
	 }

	 $y += 139.7;
    }
    $pdf->Output('kvit.pdf', 'D');
    break;

case 'html':
?>
<html lang="ru">
<!-- SITEMAP_PRIORITY: 0.2 -->

<head>
<title>Печать квитанции</title>
	<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
	<meta http-equiv="Content-Language" content="ru">
	<link rel="icon" type="image/png" href="images/q16x16.png">
<style type="text/css">
code { white-space: pre; }
.nowr { white-space: nowrap; }
td { padding: 0; border: 0;}
table { border: none; }
img { border: none; }
form { margin: 0px; padding: 0px; }
sup { font-size: 66%; line-height: .5; }
li { list-style: square outside; padding: 0px; margin: 0px; }
ul { list-style: square outside; padding: 0em 0em 0em 0em; margin: 0em 0em 0em 1.5em; }
.fakelink { cursor: pointer; }
.centered { margin-left: auto; margin-right: auto; }
.zerosize { font-size: 1px; }
.underlined { text-decoration: underline; }
.bolded { font-weight: bold; }
.vbottom { vertical-align: bottom; }
.vsub { vertical-align: sub; }
.h_left { text-align: left; }
.h_right { text-align: right; }
.h_center { text-align: center; }
.v_top { vertical-align: top; }
.v_bottom { vertical-align: bottom; }
.v_middle { vertical-align: middle; }
.w100, .full_w, .full { width: 100%; }
.h100, .full_h, .full { height: 100%; }
.cramp, .cramp_w { width: 1px; }
.cramp, .cramp_h { height: 1px; }
</style>
<style type="text/css">
body { background-color: white; margin: 0px; text-align: center; }
.ramka { border-top: black 1px dashed; border-bottom: black 1px dashed; border-left: black 1px dashed; border-right: black 1px dashed; margin: 0 auto 12mm auto; height: 145mm; }
.kassir { font-weight: bold; font-size: 10pt; font-family: "Times New Roman", serif; padding: 7mm 0 7mm 0; text-align: center; }
.cell { font-family: Arial, sans-serif; border-left: black 1px solid; border-bottom: black 1px solid; border-top: black 1px solid; font-weight: bold; font-size: 8pt; line-height: 1.1; height: 4mm; vertical-align: bottom; text-align: center; }
.cells { border-right: black 1px solid; width: 100%; }
.subscript { font-size: 6pt; font-family: "Times New Roman", serif; line-height: 1; vertical-align: top; text-align: center; }
.string, .dstring { font-weight: bold; font-size: 8pt; font-family: Arial, sans-serif; border-bottom: black 1px solid; text-align: left; vertical-align: bottom; }
.dstring { font-size: 9pt; letter-spacing: 1pt; }
.floor { vertical-align: bottom; padding-top: 0.5mm; }
.stext { font-size: 8.5pt; font-family: "Times New Roman", serif; vertical-align: bottom; }
.stext7 { font-size: 7.5pt; font-family: "Times New Roman", serif; vertical-align: bottom; }
</style>
<style type="text/css">
input { font-family: Arial, sans-serif; font-size: 9pt; color: black; background-color: white; border: 1px solid #333; margin: 8pt 8pt 8pt 0; }
a { text-decoration: none; color: #555; }
a:hover { text-decoration: underline; }
#toolbox { font-family: Arial, sans-serif; font-size: 9pt; border-bottom: dashed 1pt black; margin-bottom: 0; padding: 2mm 0 0 0; text-align: justify; }
p { margin: 2pt 0 2pt 0; }
</style>
<style type="text/css" media="print">
#toolbox { display: none; }
</style>
<style type="text/css">
#toolbox { width: 180mm; margin-left: auto; margin-right: auto; }
.topmargin { height: 4mm; }
</style>
</head>

<body>
<div id="toolbox"><p>Квитан&shy;ция фор&shy;мы &laquo;&#8470;&nbsp;ПД-4&raquo; сво&shy;бод&shy;но рас&shy;по&shy;ла&shy;га&shy;ет&shy;ся на&nbsp;листе фор&shy;ма&shy;та А4. Никаких осо&shy;бых настро&shy;ек для&nbsp;печа&shy;ти доку&shy;мен&shy;та обыч&shy;но не&nbsp;требуется.</p>
<input type="button" value="Напечатать" onclick="window.print();">
<script language="javascript" type="text/javascript">
<!--
if (document.domain && document.referrer && document.referrer.search(document.domain) > -1)
		document.write('<input type=\"button\" value=\"Закрыть\" onclick=\"window.close();\">');
//-->
</script>
<center><span style="font-size: 80%;">информационный блок от начала страницы до пунктирной линии на печать не выводится</span></center></div>
<div class="topmargin"></div>
<table class="ramka" cellspacing="0" style="width: 180mm;">
<?php
function printincells($text, $n, $width=-1) {
    if ($width == -1) $width = 100/$n;
    print "<table class=\"cells\" cellspacing=\"0\"><tr>";
    for ($i = 0; $i < $n; $i++) {
        print "<td class=\"cell\" style=\"width: ".$width."%;\">".$text[$i]."</td>";
    }
    print "</tr></table>";
}
 
for ($i = 0; $i < 2*$kn; $i++) 
{
$j = floor($i/2);

print "<tr>
	<td style=\"width: 50mm; height: 65mm; border-bottom: black 1.5px solid;\">
      	    <table style=\"width: 50mm; height: 100%;\" cellspacing=\"0\">";
if ($i % 2 == 0) {
    print "<tr><td class=\"kassir\" style=\"vertical-align: top; letter-spacing: 0.2em;\">Извещение</td></tr><tr><td class=\"kassir\" style=\"vertical-align: bottom;\">Кассир</td></tr>";
} else {
    print "<tr><td class=\"kassir\"  style=\"vertical-align: bottom; height: 100%;\">Квитанция<br><br>Кассир</td></tr>";
}
print "</table></td>\n";

print "<td style=\"width: 130mm; height: 65mm; padding: 0mm 4mm 0mm 3mm; border-left: black 1.5px solid; border-bottom: black 1.5px solid;\"> 
<table cellspacing=\"0\" style=\"width: 123mm; height: 100%;\">";

if ($i % 2 == 0) {
print "<tr><td><table width=\"100%\" cellspacing=\"0\">
        <tr><td class=\"stext7\" style=\"text-align: right; vertical-align: middle;\"><i>Форма &#8470; ПД-4</i></td></tr>
       </table></td></tr>";
}

print "<tr><td style=\"vertical-align: bottom;\"><table style=\"width: 100%;\" cellspacing=\"0\"><tr><td class=\"string\"><span class=\"nowr\">".$rval[$j]['firm']."</span></td></tr></table></td></tr>
<tr><td class=\"subscript nowr\">(наименование получателя платежа)</td></tr>
<tr><td><table cellspacing=\"0\" width=\"100%\"><tr><td width=\"30%\" class=\"floor\">";

printincells($rval[$j]['inn'], 10, 10);

print "</td><td width=\"10%\" class=\"stext7\">&nbsp;</td><td width=\"60%\" class=\"floor\">";

printincells($rval[$j]['rs'], 20, 5);

print '</td></tr>
<tr><td class="subscript nowr">(ИНН получателя платежа)</td><td class="subscript">&nbsp;</td><td class="subscript nowr">(номер счета получателя платежа)</td></tr></table></td></tr>

<tr><td><table cellspacing="0" width="100%"><tr><td width="2%" class="stext">в</td><td width="64%" class="string"><span class="nowr">'.$rval[$j]['bank'].'</span></td>
<td width="7%" class="stext" align="right">БИК&nbsp;</td><td width="27%" class="floor">';

printincells($rval[$j]['bik'], 9, 11);

print "</td></tr><tr><td class=\"subscript\">&nbsp;</td><td class=\"subscript nowr\">(наименование банка получателя платежа)</td></tr></table></td></tr><tr><td><table cellspacing=\"0\" width=\"100%\"><tr><td class=\"stext7 nowr\" width=\"40%\">Номер кор./сч. банка получателя платежа</td><td width=\"60%\" class=\"floor\">";

printincells($rval[$j]['ks'], 20, 5);

print '</td></tr></table></td></tr>

<tr><td>
 <table cellspacing="0" width="100%">
  <tr><td class="string"><span class="nowr">'.$purpose.'</span></tr>
  <tr><td class="subscript nowr">(наименование платежа)</td></tr></table></td></tr>
<tr><td>
 <table cellspacing="0" width="100%">
  <tr><td class="stext" width="1%">Ф.И.О&nbsp;плательщика&nbsp;</td><td class="string"><span class="nowr">'.$student['fio'].'</span></td></tr>
 </table>
</td></tr>
<tr><td><table cellspacing="0" width="100%"><tr><td class="stext" width="1%">Адрес&nbsp;плательщика&nbsp;</td><td class="string"><span class="nowr">'.$student['address'].'</span></td></tr></table></td></tr>

<tr><td><table cellspacing="0" width="100%"><tr>
<td class="stext" width="1%">Сумма&nbsp;платежа&nbsp;</td>
<td class="string" width="8%"><span class="nowr">'.floor($price[$j]).'</span></td>
<td class="stext" width="1%">&nbsp;руб.&nbsp;</td>
<td class="string" width="8%"><span class="nowr">'.sprintf("%02d", $price[$j]-floor($price[$j])).'</span></td>
<td class="stext" width="1%">&nbsp;коп.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Сумма&nbsp;платы&nbsp;за&nbsp;услуги&nbsp;</td><td class="string" width="8%">&nbsp;</td><td class="stext" width="1%">&nbsp;руб.&nbsp;</td><td class="string" width="8%">&nbsp;</td><td class="stext" width="1%">&nbsp;коп.</td></tr></table></td></tr>

<tr><td><table cellspacing="0" width="100%"><tr><td class="stext" width="5%">Итого&nbsp;</td><td class="string" width="8%">&nbsp;</td><td class="stext" width="5%">&nbsp;руб.&nbsp;</td><td class="string" width="8%">&nbsp;</td><td class="stext" width="5%">&nbsp;коп.&nbsp;</td><td class="stext" width="20%" align="right">&laquo;&nbsp;</td><td class="string" width="8%">&nbsp;</td><td class="stext" width="1%">&nbsp;&raquo;&nbsp;</td><td class="string" width="20%">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td class="stext" width="3%">&nbsp;20&nbsp;</td><td class="string" width="5%">&nbsp;</td><td class="stext" width="1%">&nbsp;г.</td></tr></table></td></tr><tr><td class="stext7" style="text-align: justify">С условиями приема указанной в платежном документе суммы, в т.ч. с суммой взимаемой платы за&nbsp;услуги банка,&nbsp;ознакомлен&nbsp;и&nbsp;согласен.</td></tr><tr><td style="padding-bottom: 0.5mm;"><table cellspacing="0" width="100%"><tr><td class="stext7" width="50%">&nbsp;</td><td class="stext7" width="1%"><b>Подпись&nbsp;плательщика&nbsp;</b></td><td class="string" width="40%">&nbsp;</td></tr></table></td></tr></table>
    </td>
  </tr>';
}
print "</table></body></html>";
    break;
}
?>
