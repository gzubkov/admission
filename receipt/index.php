<?php
require_once('../../conf.php');
require_once('../../../modules/mysql.php');
require_once('../class/catalog.class.php');
require_once('../class/price.class.php');
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html class="js" dir="ltr" xml:lang="en" xmlns="http://www.w3.org/1999/xhtml" lang="en"><head>

<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
 
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Квитанция</title>

<link type="text/css" rel="stylesheet" media="all" href="../images/defaults.css">
<link type="text/css" rel="stylesheet" media="all" href="../images/system.css">
<link type="text/css" rel="stylesheet" media="all" href="../images/style.css">

<style type="text/css">
   .dataTables_info { padding-top: 0; }
   .dataTables_paginate { padding-top: 1px; }
</style>


<SCRIPT type="text/javascript" src="../js/jquery-1.4.2.min.js"></script>
<!-- jQuery UI -->
<SCRIPT type="text/javascript" src="../js/jquery-ui-1.8.custom.min.js"></script>
<link type="text/css" rel="stylesheet" media="all" href="../css/custom-theme/jquery-ui-1.8.custom.css">	
<SCRIPT type="text/javascript" charset="utf-8">
$(document).ready(function() {
   $("input:submit, input:button").button();
});
</SCRIPT>
</HEAD>
<BODY class="sidebar-left">


<?php
print "<BR>\n";
      

   print "<DIV style=\"border: 1px solid #d3d3d3; background-color: #ffffff; width: 700px; margin:0 auto;\">";
   print "<BR><CENTER><B>Печать квитанции</B></CENTER>";

   print "<DIV style=\"border: none; width: 98%; margin:0 auto;\">";

   print "<FORM action=\"kvit.php\" method=\"POST\" target=\"_blank\">";
   print "<TABLE border=0 cellspacing=3 cellpadding=3 id=example class=display><TBODY style=\"border: none;\">";

if (!isset($_SESSION['joomlaregion'])) {
   $region = 2;
   print "<TR><TD>Тарифная зона:</TD><TD>";
   print "<SELECT name=\"region_id\">
   	 	  <OPTION value=1>ЦКТ - Россия и Интернет</OPTION>
		  <OPTION value=3>ЦКТ - Москва</OPTION>
		  <OPTION value=2>ИИТ - Россия и Интернет</OPTION>
		  <OPTION value=4>ИИТ - Москва</OPTION>
	  </SELECT>";
} else {
   print "<TR><TD>Регион:</TD><TD><INPUT type=\"hidden\" name=\"region_id\" value=\"".$_SESSION['joomlaregion']."\">";
   $region = $_SESSION['joomlaregion'];
   $regname = getarray("SELECT name FROM `partner_regions` WHERE `id`='".$_SESSION['joomlaregion']."'");
   print $regname['name'].".";
   print "<TR><TD>Посредник:</TD><TD>";
   print "<SELECT name=\"partner_id\">
   	 	  <OPTION value=1>ЦКТ</OPTION>
		  <OPTION value=2>ИИТ</OPTION>
	  </SELECT>";
   $_SESSION['region'] = $_SESSION['joomlaregion'];
}

   print "</TD></TR>";
   print "<TR><TD>Цель платежа:</TD><TD>";

print "<SCRIPT language=\"javascript\">
       $(function () {
        $(\"#purpose\").change(function () {
	    if ($('#purpose :selected').attr('countv') == 1) {
	         $('#counttr').show();
	    } else  {
	         $('#counttr').hide();
	    }
	});
       });
       </SCRIPT>";
print "<SELECT name=\"purpose\" id=\"purpose\">";
$rval = getarray("SELECT * FROM `receipt_purpose`");
foreach($rval as $v) print "<OPTION value=\"".$v['id']."\" countv=\"".$v['count']."\">".$v['text']."</OPTION>";	      
print "</SELECT>";
print "</TD></TR>\n";

print "<TR><TD>Специальность:</TD><TD>";
print "<SELECT name=\"catalog\" id=\"catalog\">";

$catalog = new Catalog();
$rval = $catalog->getAvailableByRegion($region, "%name% (%base%) %basicsemestr%");
unset($catalog);

foreach($rval as $k => $v) print "<OPTION value=\"".$k."\">".$v."</OPTION>";	      
print "</SELECT>";
print "</TD></TR>\n";

$prc = new Price();
$sessions = $prc->getSessions();
unset($prc);

print "<TR><TD>Назначенная сессия:</TD><TD><SELECT name=\"date\">";
foreach ($sessions as $k => $v) {
    print "<OPTION value=\"".$k."\">".$v."</OPTION>\n";
}
print "</SELECT></TD></TR>\n";
print "<TR><TD>Договор:</TD><TD><INPUT name=\"dn\" value=\"\" style=\"width: 50px;\">.</TD></TR>\n";
print "<TR><TD>Семестр:</TD><TD><INPUT name=\"s\" value=\"\" style=\"width: 50px;\">.</TD></TR>\n";
print "<TR><TD>ФИО плательщика:</TD><TD><INPUT name=\"fio\" value=\"\" style=\"width: 400px;\">.</TD></TR>\n";
print "<TR><TD>Адрес плательщика:</TD><TD><INPUT name=\"address\" value=\"\" style=\"width: 400px;\">.</TD></TR>\n";
print "<TR id=\"counttr\" style=\"display: none;\"><TD>Количество пересдач:</TD><TD><INPUT name=\"count\" value=\"1\" style=\"width: 20px;\">.</TD></TR>\n";
print "<TR><TD>Формат вывода:</TD><TD><LABEL><INPUT type=\"radio\" name=\"format\" value=\"html\" checked> HTML</LABEL> <LABEL><INPUT type=\"radio\" name=\"format\" value=\"pdf\"> PDF</LABEL></TD></TR>";
print "<TR><TD colspan=\"2\" style=\"height: 50px; text-align: center; valign: bottom;\"><INPUT type=\"submit\" value=\"Распечатать квитанцию\"></TD></TR>";
print "</TBODY></TABLE></FORM></DIV></DIV>";

if ($_SERVER['REMOTE_ADDR'] == $CFG_trustedip) {
print "<BR>";
    print "<DIV style=\"border: 1px solid #d3d3d3; background-color: #ffffff; width: 700px; margin:0 auto;\">";
    print "<BR><CENTER><B>Печать квитанции с данными из базы</B></CENTER>";

    print "<DIV style=\"border: none; width: 98%; margin:0 auto;\">";

    print "<FORM action=\"kvit.php\" method=\"POST\" target=\"_blank\">";
    print "<TABLE border=0 cellspacing=3 cellpadding=3 id=example class=display><TBODY style=\"border: none;\">";
    print "<TR><TD>Номер договора:</TD><TD><INPUT name=\"mid\" value=\"\" style=\"width: 50px;\">.</TD></TR>\n";
    print "<TR><TD>Цель платежа:</TD><TD>";

    print "<SCRIPT language=\"javascript\">
       $(function () {
        $(\"#purpose2\").change(function () {
	    if ($('#purpose2 :selected').attr('countv') == 1) {
	         $('#counttr2').show();
	    } else  {
	         $('#counttr2').hide();
	    }
	});
       });
       </SCRIPT>";
    print "<SELECT name=\"purpose\" id=\"purpose2\">";
    $rval = getarray("SELECT * FROM `receipt_purpose` WHERE `student`=1");
    foreach($rval as $v) print "<OPTION value=\"".$v['id']."\" countv=\"".$v['count']."\">".$v['text']."</OPTION>";	      
    print "</SELECT>";
    print "</TD></TR>\n";
    print "<TR><TD>Назначенная сессия:</TD><TD><SELECT name=\"date\">";
    foreach ($sessions as $k => $v) {
        print "<OPTION value=\"".$k."\">".$v."</OPTION>\n";
    }
    print "</SELECT></TD></TR>\n";
    print "<TR id=\"counttr2\" style=\"display: none;\"><TD>Количество пересдач:</TD><TD><INPUT name=\"count\" value=\"1\" style=\"width: 20px;\">.</TD></TR>\n";
    print "<TR><TD>Формат вывода:</TD><TD><LABEL><INPUT type=\"radio\" name=\"format\" value=\"html\" checked> HTML</LABEL> <LABEL><INPUT type=\"radio\" name=\"format\" value=\"pdf\"> PDF</LABEL></TD></TR>";
    print "<TR><TD colspan=\"2\" style=\"height: 50px; text-align: center; valign: bottom;\"><INPUT type=\"submit\" value=\"Распечатать квитанцию\"></TD></TR>";
    print "</TBODY></TABLE></FORM></DIV>";
}

?>
</BODY></HTML>

