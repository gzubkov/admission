<?php
require_once('../../conf.php');
require_once('../class/mysql.class.php');
require_once('../class/catalog.class.php');
require_once('../class/price.class.php');
$msl = new dMysql();

if ($_SESSION['rights'] != 'admin' || $_SESSION['md_rights'] != md5($CFG_salted.$_SESSION['rights'])) {
    exit(0);
}

if (!is_numeric($_REQUEST['id'])) {
    exit(0);
}

$id = $_REQUEST['id'];

$rval = $msl->getarray("SELECT * FROM `students_base`.`student` WHERE id = '".$id."' LIMIT 1;");
$spec = $msl->getarray("SELECT f.abbreviation, b.name FROM admission.catalogs a 
                  LEFT JOIN admission.specialties b ON a.specialty=b.id 
                  LEFT JOIN admission.`universities_departments` c ON b.department=c.id 
                  LEFT JOIN admission.`universities_faculties` d ON c.faculty=d.id 
		  LEFT JOIN admission.`universities` f ON d.university=f.id 		  
                  WHERE a.base_id='".$rval['catalog']."'");

print "<script type=\"text/javascript\">
	$(function() {
		     $(\"input:submit, input:button\").button();
	});
	</script>";
print "<TABLE border=0><TBODY style=\"border: none;\">";
print "<TR><TD><B>Адрес:</B></TD><TD>".$rval['address']."</TD></TR>";
print "<TR><TD><B>Паспорт:</B></TD><TD>".$rval['doc_serie']." №".$rval['doc_number']."</TD></TR>";
print "<TR><TD><B>Учебное заведение:</B></TD><TD>".$spec['abbreviation']."</TD></TR>";
print "<TR><TD><B>Образовательная программа:</B></TD><TD>".$spec['name']."</TD></TR>";
print "<TR><TD><B>Назначенный семестр:</B></TD><TD>".$rval['semestr']."</TD></TR>";

$mhash = md5(md5("moodle.ins-iit.rudddddsdsd".$id));

print "<TR><TD><B>Сводная ведомость успеваемости:</B></TD><TD><INPUT type=\"button\" value=\"Получить в формате PDF\" onclick=\"javascript: window.open('http://admission.iitedu.ru/students/journal.php?mid=".$id."&mhash=".$mhash."'); return false;\"><INPUT type=\"button\" value=\"Получить в формате HTML\" onclick=\"javascript: window.open('http://admission.iitedu.ru/students/journal_html.php?mid=".$id."&mhash=".$mhash."'); return false;\"></TD></TR>\n";

print "<TR><TD colspan=2 style=\"text-align: center;\"><B>Квитанция на оплату обучения</B></TD></TR>\n";

$cval = $msl->getarray("SELECT id,text,count FROM `receipt_purpose` WHERE student=1");

print "<INPUT type=\"hidden\" name=\"mid\" value=\"".$id."\"><INPUT type=\"hidden\" name=\"mhash\" value=\"".$mhash."\">";
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
print "<TR><TD><B>Назначение платежа:</B></TD><TD><SELECT name=\"purpose\" id=\"purpose\">\n"; 
foreach($cval as $v) {
    $replace = array("Договор №%dn%." => "",
	             "%s%" => $rval['semestr']+1);
    print "<OPTION value=\"".$v['id']."\" countv=\"".$v['count']."\">".strtr($v['text'], $replace)."</OPTION>";
}
print "</SELECT></TD></TR>\n\n";

$price = new Price($msl);
$sessions = $price->getSessions();
unset($price);

print "<TR><TD><B>Сессия:</B></TD><TD><SELECT name=\"date\" id=\"date\">";
foreach ($sessions as $k => $v) print "<OPTION value=\"".$k."\">".$v."</OPTION>";
print "</SELECT></TD></TR>\n";

print "<TR id=\"counttr\" style=\"display: none;\"><TD><B>Количество пересдач:</B></TD><TD><INPUT type=text maxlength=2 name=\"count\" id=\"count\" value=\"1\" style=\"width:15px\"></TD></TR>\n";
print "<TR><TD colspan=2 align=center><INPUT type=\"button\" value=\"Получить квитанцию в формате PDF\" 
onclick=\"javascript: window.open('http://admission.iitedu.ru/receipt/kvit.php?mid=".$id."&mhash=".$mhash."&purpose='+$('#purpose option:selected').val()+'&count='+$('#count').val()+'&date='+$('#date option:selected').val()); return false;\" />&nbsp;<INPUT type=\"button\" value=\"Получить квитанцию в формате HTML\" 
onclick=\"javascript: window.open('http://admission.iitedu.ru/receipt/kvit.php?format=html&mid=".$id."&mhash=".$mhash."&purpose='+$('#purpose option:selected').val()+'&count='+$('#count').val()+'&date='+$('#date option:selected').val()); return false;\" /></TD></TR>";
print "</TBODY></TABLE>";
?>
