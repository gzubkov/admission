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
}
*/
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

            $(\"input[allowed=onlynumbers]\").keypress(function (e) {
                //if the letter is not digit then don't type anything
                if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
                    return false;
                }
            });
        });
        </script>";
print "<TABLE border=0 style=\"padding: 0px; margin: 0px;\"><TBODY style=\"border: none;\">";
print "<TR><TD><B>Адрес:</B></TD><TD>".$rval['address']."</TD></TR>";
print "<TR><TD><B>Паспорт:</B></TD><TD>".$rval['doc_serie']." №".$rval['doc_number']."</TD></TR>";
print "<TR><TD><B>Учебное заведение:</B></TD><TD>".$spec['abbreviation']."</TD></TR>";
print "<TR><TD><B>Образовательная программа:</B></TD><TD>".$spec['name']."</TD></TR>";
print "<TR><TD><B>Назначенный семестр:</B></TD><TD>".$rval['semestr']."</TD></TR>";

$mhash = md5(md5("moodle.ins-iit.rudddddsdsd".$id));

echo "<tr><td><b>Сводная ведомость успеваемости:</b></td>
      <td><button type=\"button\" class=\"btn btn-default\" onclick=\"javascript: window.open('http://admission.iitedu.ru/students/journal.php?mid=".$id."&mhash=".$mhash."'); return false;\">PDF</button>
      <button type=\"button\" class=\"btn btn-default\" onclick=\"javascript: window.open('http://admission.iitedu.ru/students/journal.php?format=HTML&mid=".$id."&mhash=".$mhash."'); return false;\">HTML</button></TD></TR>\n";

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

$price = new Price($msl, $mssql);
$sessions = $price->getSessions();
unset($price);

print "<TR><TD><B>Сессия:</B></TD><TD><SELECT name=\"date\" id=\"date\">";
foreach ($sessions as $k => $v) print "<OPTION value=\"".$k."\">".$v."</OPTION>";
print "</SELECT></TD></TR>\n";

print "<TR id=\"counttr\" style=\"display: none;\"><TD><B>Количество пересдач:</B></TD><TD><INPUT type=text maxlength=2 name=\"count\" style=\"width: 22px;\" id=\"count\" value=\"1\" style=\"width:15px\" allowed=\"onlynumbers\"></TD></TR>\n";
print "<TR><TD colspan=2 align=center><button type=\"button\" class=\"btn btn-default\" 
onclick=\"javascript: window.open('http://admission.iitedu.ru/receipt/kvit.php?mid=".$id."&mhash=".$mhash."&purpose='+$('#purpose option:selected').val()+'&count='+$('#count').val()+'&date='+$('#date option:selected').val()); return false;\">PDF</button>&nbsp;
<button type=\"button\" class=\"btn btn-default\" onclick=\"javascript: window.open('http://admission.iitedu.ru/receipt/kvit.php?format=html&mid=".$id."&mhash=".$mhash."&purpose='+$('#purpose option:selected').val()+'&count='+$('#count').val()+'&date='+$('#date option:selected').val()); return false;\">HTML</button></TD></TR>";
print "</TBODY></TABLE>";
