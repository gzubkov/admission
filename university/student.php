<?php
require_once('../../conf.php');
require_once('../class/mysql.class.php');
require_once('../class/mssql.class.php');
require_once('../class/catalog.class.php');
require_once('../class/price.class.php');
$msl = new dMysql();
$mssql = new dMssql();

/*
if ($_SESSION['rights'] != 'admin' || $_SESSION['md_rights'] != md5($CFG_salted.$_SESSION['rights'])) {
    exit(0);
}*/

if (!is_numeric($_REQUEST['id'])) {
    exit(0);
}

$id = $_REQUEST['id'];

$rval = $mssql->getarray("SELECT * FROM dbo.student WHERE id = '".$id."'");
$cat = new Catalog($msl);
$spec = $cat->getBaseInfo($rval['catalog']);

print "<script type=\"text/javascript\">
	$(function() {
		     $(\"input:submit, input:button\").button();
	});
	</script>";
print "<TABLE border=0><TBODY style=\"border: none;\">";
print "<TR><TD><B>Образовательная программа:</B></TD><TD>".$spec['name']."</TD></TR>";
print "<TR><TD><B>Назначенный семестр:</B></TD><TD>".$rval['semestr']."</TD></TR>";

$mhash = md5(md5("moodle.ins-iit.rudddddsdsd".$id));

print "<TR><TD><B>Сводная ведомость успеваемости:</B></TD><TD><INPUT type=\"button\" value=\"В формате PDF\" onclick=\"javascript: window.open('http://admission.iitedu.ru/students/journal.php?mid=".$id."&mhash=".$mhash."'); return false;\"><INPUT type=\"button\" value=\"В формате HTML\" onclick=\"javascript: window.open('http://admission.iitedu.ru/students/journal.php?mid=".$id."&format=HTML&mhash=".$mhash."'); return false;\"></TD></TR>\n";
print "</TBODY></TABLE>";
?>
