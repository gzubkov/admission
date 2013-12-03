<?php
require_once('../../conf.php');
require_once('../class/catalog.class.php');
require_once('../class/mysql.class.php');
require_once('../class/price.class.php');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html dir="ltr" xml:lang="en" xmlns="http://www.w3.org/1999/xhtml" lang="en"><head>

<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css" />
 
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Квитанция</title>

<link type="text/css" rel="stylesheet" media="all" href="../images/defaults.css" />
<link type="text/css" rel="stylesheet" media="all" href="../images/system.css" />
<link type="text/css" rel="stylesheet" media="all" href="../images/style.css" />

<style type="text/css">
   .dataTables_info { padding-top: 0; }
   .dataTables_paginate { padding-top: 1px; }
</style>


<script type="text/javascript" src="../js/jquery-1.4.2.min.js"></script>
<!-- jQuery UI -->
<script type="text/javascript" src="../js/jquery-ui-1.8.custom.min.js"></script>
<link type="text/css" rel="stylesheet" media="all" href="../css/custom-theme/jquery-ui-1.8.custom.css" />	
<script type="text/javascript">
$(document).ready(function() {
   $("input:submit, input:button").button();
});
</script>
</head>
<body class="sidebar-left">


<?php
$msl = new dMysql();

print "<div style=\"border: 1px solid #d3d3d3; background-color: #ffffff; width: 700px; margin:0 auto; text-align: center; margin: 20px auto 0pt;\">\n";
print "<b>Печать квитанции</b>";

print "<div style=\"border: none; width: 98%; margin:0 auto; text-align:left;\">\n";

print "<form action=\"kvit.php\" method=\"post\">"; // target=\"_blank\"
print "<table style=\"border: none; border-spacing:3px;\" id=\"example\" class=\"display\"><tbody style=\"border: none;\">";

if (!isset($_SESSION['joomlaregion'])) {
    $region = 2;
    print "<tr><td>Тарифная зона:</td><td>";
    print "<select name=\"region_id\">
   	   <option value=\"1\">ЦКТ - Россия и Интернет</option>
	   <option value=\"3\">ЦКТ - Москва</option>
	   <option value=\"2\">ИИТ - Россия и Интернет</option>
	   <option value=\"4\">ИИТ - Москва</option>
	   </select>";
} else {
    print "<tr><td>Регион:</td><td><input type=\"hidden\" name=\"region_id\" value=\"".$_SESSION['joomlaregion']."\">";
    $region = $_SESSION['joomlaregion'];
    $regname = $msl->getarray("SELECT name FROM `partner_regions` WHERE `id`='".$_SESSION['joomlaregion']."'");
    print $regname['name'].".";

    if ($region != 3) {
        print "<tr><td>Посредник:</td><td>";
        print "<select name=\"partner_id\">
   	 	  <option value=1>ЦКТ</option>
		  <option value=2>ИИТ</option>
	       </select>";
    }
    $_SESSION['region'] = $_SESSION['joomlaregion'];
}

   print "</td></tr>";
   print "<tr><td>Цель платежа:</td><td>";

print "<script type=\"text/javascript\">
       $(function () {
        $(\"#purpose\").change(function () {
	    if ($('#purpose :selected').attr('value') == 4 || $('#purpose :selected').attr('value') == 3) {
	         $('#counttr').show();
	    } else  {
	         $('#counttr').hide();
	    }
	});
       });
       </script>";
print "<select name=\"purpose\" id=\"purpose\">";
$rval = $msl->getarray("select * FROM `receipt_purpose`");

foreach ($rval as $v) {
    print "<option value=\"".$v['id']."\">".$v['text']."</option>";
}

print "</select>";
print "</td></tr>\n";

print "<tr><td>Специальность:</td><td>";
print "<select name=\"catalog\" id=\"catalog\">";

$catalog = new Catalog(&$msl);
$rval = $catalog->getAvailableByRegion($region, "%name% (%base%) %basicsemestr%", 0, 0, 0);
unset($catalog);

foreach ($rval as $k => $v) {
    print "<option value=\"".$k."\">".$v."</option>";
}
print "</select>";
print "</td></tr>\n";

$prc = new Price($msl);
$sessions = $prc->getSessions();
unset($prc);

print "<tr><td>Назначенная сессия:</td><td><select name=\"date\">";
foreach ($sessions as $k => $v) {
    print "<option value=\"".$k."\">".$v."</option>\n";
}
print "</select></td></tr>\n";
print "<tr><td>Договор:</td><td><input name=\"dn\" value=\"\" style=\"width: 50px;\" />.</td></tr>\n";
print "<tr><td>Семестр:</td><td><input name=\"s\" value=\"\" style=\"width: 50px;\" />.</td></tr>\n";
print "<tr><td>ФИО плательщика:</td><td><input name=\"fio\" value=\"\" style=\"width: 400px;\" />.</td></tr>\n";
print "<tr><td>Адрес плательщика:</td><td><input name=\"address\" value=\"\" style=\"width: 400px;\" />.</td></tr>\n";
print "<tr id=\"counttr\" style=\"display: none;\"><td>Количество пересдач/досдач:</td><td><input name=\"count\" value=\"1\" style=\"width: 20px;\" />.</td></tr>\n";
print "<tr><td>ТП \"Абитуриент\":</td><td><label><input type=\"radio\" name=\"tpapplicant\" value=\"0\" checked=\"checked\" /> нет</label> <label><input type=\"radio\" name=\"tpapplicant\" value=\"1\" /> да</label></td></tr>";
print "<tr><td>Формат вывода:</td><td><label><input type=\"radio\" name=\"format\" value=\"html\" checked=\"checked\" /> HTML</label> <label><input type=\"radio\" name=\"format\" value=\"pdf\" /> PDF</label></td></tr>";
print "<tr><td colspan=\"2\" style=\"height: 50px; text-align: center; vertical-align: bottom;\"><input type=\"submit\" value=\"Распечатать квитанцию\" /></td></tr>";
print "</tbody></table></form></div></div>";

if ($_SERVER['REMOTE_ADDR'] == $CFG_trustedip) {
    print "<br><div style=\"border: 1px solid #d3d3d3; background-color: #ffffff; width: 700px; margin:0 auto;\">";
    print "<BR><CENTER><B>Печать квитанции с данными из базы</B></CENTER>";

    print "<div style=\"border: none; width: 98%; margin:0 auto;\">";

    print "<form action=\"kvit.php\" method=\"POST\" target=\"_blank\">";
    print "<table border=0 cellspacing=3 cellpadding=3 id=example class=display><tbody style=\"border: none;\">";
    print "<tr><td>Номер договора:</td><td><input name=\"mid\" value=\"\" style=\"width: 50px;\" />.</td></tr>\n";
    print "<tr><td>Цель платежа:</td><td>";

    print "<script type=\"text/javascript\">
       $(function () {
        $(\"#purpose2\").change(function () {
	    if ($('#purpose2 :selected').attr('countv') == 1) {
	         $('#counttr2').show();
	    } else  {
	         $('#counttr2').hide();
	    }
	});
       });
       </script>";
    print "<select name=\"purpose\" id=\"purpose2\">";
    $rval = $msl->getarray("select * FROM `receipt_purpose` WHERE `student`=1");

    foreach ($rval as $v) {
        print "<option value=\"".$v['id']."\" countv=\"".$v['count']."\">".$v['text']."</option>";
    }

    print "</select>";
    print "</td></tr>\n";
    print "<tr><td>Назначенная сессия:</td><td><select name=\"date\">";
    foreach ($sessions as $k => $v) {
        print "<option value=\"".$k."\">".$v."</option>\n";
    }
    print "</select></td></tr>\n";
    print "<tr id=\"counttr2\" style=\"display: none;\"><td>Количество пересдач:</td><td><input name=\"count\" value=\"1\" style=\"width: 20px;\" />.</td></tr>\n";
    print "<tr><td>Формат вывода:</td><td><label><input type=\"radio\" name=\"format\" value=\"html\" checked /> HTML</label> <label><input type=\"radio\" name=\"format\" value=\"pdf\" /> PDF</label></td></tr>";
    print "<tr><td colspan=\"2\" style=\"height: 50px; text-align: center; valign: bottom;\"><input type=\"submit\" value=\"Распечатать квитанцию\" /></td></tr>";
    print "</tbody></table></form></div>";
}

?>
</body></html>

