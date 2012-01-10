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
<title>Список региональных партнеров</title>

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
print "<BR>\n";
print "<DIV style=\"border: 1px solid #d3d3d3; background-color: #ffffff; width: 900px; margin:0 auto;\">";
print "<BR><CENTER><B>Список региональных партнеров</B></CENTER>";

print "<DIV style=\"border: none; width: 98%; margin:0 auto;\">";
print "<FORM action=\"\" method=\"POST\">";
   
print "<TABLE border=0 cellspacing=3 cellpadding=3 id=example class=display><TBODY style=\"border: none;\">";

if ($_SESSION['rights'] != 'admin' || $_SESSION['md_rights'] != md5($CFG_salted.$_SESSION['rights'])) {
    print "<TR><TD colspan=2>Данная функция Вам недоступна.</TD></TR></TABLE></FORM></DIV></DIV></BODY></HTML>"; 
    exit(0);
}

$msl = new dMysql();
$rpval = $msl->getarray("SELECT id, name, `e-mail`, region, used FROM `partner_regions` a LEFT JOIN `partner_agreement` b ON a.id=b.region WHERE id > 10");

    print "<TR><TD>Название</TD><TD>Электронная почта</TD><TD>Подтверждено</TD>\n";
for ($i=0; $i < count($rpval); $i++) {
    print "<TR><TD><A href=\"card.php?region=".$rpval[$i]['id']."\">".$rpval[$i]['name']."</A></TD>\n";
    print "<TD><A href=\"mailto:".$rpval[$i]['e-mail']."\">".$rpval[$i]['e-mail']."</A></TD>";
    print "<TD>";

    if ($rpval[$i]['region'] > 0) {
        if ($rpval[$i]['used']) {
            print "да";
        } else {
	    print "<FONT color=red>да</FONT>";
	}
    }
    print "</TD></TR>\n";
}

print "</TBODY></TABLE>";
print "</DIV></DIV></BODY></HTML>";
?>
