<?php
// just require TCPDF instead of FPDF
require_once('../../../modules/russian_date.php');
require_once('../../../modules/mysql.php');
require_once('../../conf.php');
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html class="js" dir="ltr" xml:lang="en" xmlns="http://www.w3.org/1999/xhtml" lang="en"><head>

<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
 
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Карточка регионального партнера</title>

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

<?

if ($_SESSION['rights'] == 'admin' && $_SESSION['md_rights'] == md5($CFG_salted.$_SESSION['rights'])) {
    if ($_SESSION['joomlaregion'] == 0) $region_id = $_REQUEST['region'];
    else $region_id = $_SESSION['joomlaregion'];
} else {
    $region_id = $_SESSION['joomlaregion'];
}

$msl = new dMysql();

print "<BR>\n";
      

   print "<DIV style=\"border: 1px solid #d3d3d3; background-color: #ffffff; width: 900px; margin:0 auto;\">";
   print "<BR><CENTER><B>Карточка регионального партнера</B></CENTER>";

   print "<DIV style=\"border: none; width: 98%; margin:0 auto;\">";
   print "<FORM action=\"\" method=\"POST\">";
   
   print "<TABLE border=0 cellspacing=3 cellpadding=3 id=example class=display><TBODY style=\"border: none;\">";

if ($region_id == 0) {
   print "<TR><TD colspan=2>Неправильный id регионального партнера.</TD></TR></TABLE></FORM></DIV></DIV></BODY></HTML>"; 
   exit(0);
}

if ($_POST['act'] == 'verified') {
   $msl->insertArray('partner_agreement', array('region'=>$region_id, 'ip'=>sprintf('%u', ip2long($_SERVER['REMOTE_ADDR'])), 'remarks'=>$_POST['remarks']));
}

$rpval = $msl->getarray("SELECT a.*, b.name_rp as pos, c.name_rp as doc FROM `partner_regions` a LEFT JOIN `partner_position` b ON a.gposition=b.id LEFT JOIN `partner_organizational_documents` c ON a.orgdoc=c.id WHERE a.id = '".$region_id."'");

$agreed = $msl->getarray("SELECT date FROM `partner_agreement` WHERE region='".$region_id."'");
if ($agreed == 0) {      
   print "<TR><TD colspan=2>Проверьте указанные ниже сведения:</TD></TR>";
}
      print "<TR><TD style=\"width: 120px;\">Организация:</TD><TD>".$rpval['firm'].".</TD></TR>";
      print "<TR><TD style=\"width: 120px;\">Полное наименование:</TD><TD>".$rpval['longfirm'].".</TD></TR>";
      print "<TR><TD>В лице:</TD><TD>".(($rpval['pos'] == '') ? "<FONT color=red><B>укажите должность в замечаниях</B></FONT>": $rpval['pos'])." ".$rpval['name_rp'].".</TD></TR>";
      print "<TR><TD>На основании:</TD><TD>".$rpval['doc'].".</TD></TR>";
      print "<TR><TD>Юридический адрес:</TD><TD>".$rpval['legaladdress'].".</TD></TR>";
      print "<TR><TD>Фактический адрес:</TD><TD>".$rpval['physicaladdress'].".</TD></TR>";
      print "<TR><TD>БИК:</TD><TD>".$rpval['bik'].".</TD></TR>";
      print "<TR><TD>Кор.счет:</TD><TD>".$rpval['ks'].".</TD></TR>";
      print "<TR><TD>Рассчетный счет:</TD><TD>".$rpval['rs'].".</TD></TR>";
      print "<TR><TD>Банк:</TD><TD>".$rpval['bank'].".</TD></TR>";
      print "<TR><TD>ИНН/КПП:</TD><TD>".$rpval['inn']."/".$rpval['kpp'].".</TD></TR>";
    if ($rpval['dog_num'] != '') {
        print "<TR><TD>Договор с ЦКТ:</TD><TD>".$rpval['dog_num']." от ".mb_strtolower(russian_date( strtotime($rpval['dog_date']), 'j   F  Y' ), 'UTF-8').".</TD></TR>";
    }
    print "<TR><TD>Электронная почта:</TD><TD><A href=\"mailto:".$rpval['e-mail']."\">".$rpval['e-mail']."</A>.</TD></TR>";

if ($agreed == 0) {    
   print "<TR><TD style=\"vertical-align: top;\">Замечания:</TD><TD><TEXTAREA name=\"remarks\" WRAP=\"virtual\" COLS=\"90\" ROWS=\"3\"></TEXTAREA></TD></TR>";
   print "<INPUT type=\"hidden\" name=\"act\" value=\"verified\">";
   print "<TR><TD colspan=2 style=\"text-align: center;\"><INPUT type=submit value=\"Отправить\"></TD></TR>";
} else {
   print "<TR><TD colspan=2>Данные подтверждены ".mb_strtolower(russian_date( strtotime($agreed['date']), 'j F Y в h:m' ), 'UTF-8').".";
   if ($agreed['used'] == 1) {print "Замечания выполнены.";}
   print "</TD></TR>\n";
}

print "</TBODY></TABLE>";
print "</DIV></DIV></BODY></HTML>";
?>
