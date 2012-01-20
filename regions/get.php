<?php
require_once('../../conf.php');
require_once('../class/mysql.class.php');

$rus = array('Первый','Второй','Третий','Четвертый');

if (!is_numeric($_POST['catalog'])) {
    print "error";
    exit(0);
}

$mslk = new dMysql();
$gval = $mslk->getarray("SELECT specialty, internet, baseedu, term FROM `catalogs` WHERE `id`='".$_POST['catalog']."'");

if (0) {
if ($gval['internet'] > 0) {
print "<TR><TD style=\"width: ".$_POST['width']."px;\">Обучение через Интернет<SPAN class=\"form-required\" title=\"Данное поле обязательно для заполнения.\">*</SPAN></TD>\n";
print "<TD><INPUT type=radio name=\"internet\" id=\"internet-1\" value=\"1\" checked>да<INPUT type=radio name=\"internet\" id=\"internet-0\" value=\"0\">нет</TD></TR>";  
//    print "<TR><TD style=\"width: ".$_POST['width']."px;\">Обучение через Интернет<SPAN class=\"form-required\" title=\"Данное поле обязательно для заполнения.\">*</SPAN></TD>\n";
//    print "<TD><LABEL for=\"internet-1\"><INPUT type=radio name=\"internet\" id=\"internet-1\" value=\"1\" checked>да</LABEL><LABEL for=\"internet-0\"><INPUT type=radio name=\"internet\" id=\"internet-0\" value=\"0\">нет</LABEL></TD></TR>";    
} else {
    print "<INPUT type=\"hidden\" name=\"internet\" value=\"0\">";
}
}

print "<TABLE border=0 cellspacing=0 cellpadding=0><TBODY style=\"border: none;\">";
print "<TR><TD style=\"width: ".$_POST['width']."px;\">Начальный семестр обучения<SPAN class=\"form-required\" title=\"Данное поле обязательно для заполнения.\">*</SPAN></TD>\n";
print "<TD><INPUT type=text name=\"semestr\" value=\"";
if ($_POST['semestr'] != 0) {print "1";} else {print $_POST['semestr'];}
print "\" size=2 maxlength=2> из ".($gval['term']*2)." (указывать 0, если неизвестно).</TD></TR>";  
print "<TR><TD colspan=\"2\"><LABEL><INPUT type=\"checkbox\" id=\"traditional_form\" name=\"traditional_form\" value=\"1\" checked> Результаты ЕГЭ отсутствуют, сдача вступительных экзаменов в традиционно принятой в МГТУ \"МАМИ\" форме.</LABEL></TD></TR>";

$rval = $mslk->getarray("SELECT subject,name FROM `reg_ege_minscores` LEFT JOIN `reg_subjects` ON `reg_subjects`.id = `reg_ege_minscores`.subject 
                  WHERE specialty = '".$gval['specialty']."' LIMIT 0, 10", 1);

print "<TR><TD colspan=2>Результаты ЕГЭ предыдущих годов (если имеются):</TD></TR>";
for ($i = 0; $i < count($rval); $i++) {
    if ($_POST['act'] == 'editapplicant') {
    $kval = $mslk->getarray("SELECT score,document FROM `partner_applicant_scores` 
                     	     WHERE `subject` = ".$rval[$i]['subject']." AND `ege`=1 AND applicant_id=".$_POST['applicant_id']." LIMIT 0,1");
    }

    print "<TR><TD style=\"width: ".$_POST['width']."px;\">".$rus[$i]." предмет</TD><TD>".$rval[$i]['name'].".</TD></TR>";   
    print "<INPUT type=\"hidden\" name=\"ege[".($i+1)."][subject]\" value=\"".$rval[$i]['subject']."\">";

    print "<TR><TD style=\"width: ".$_POST['width']."px;\">Оценка</TD>";
    print "<TD><INPUT type=\"text\" name=\"ege[".($i+1)."][score]\" maxlength=\"3\" style=\"width: 30px;\" id=\"ege[".($i+1)."][scores]\" class=\"validate[optional,custom[scores]] text-input\""; 
    if ($kval != 0) print " value=\"".$kval['score']."\"";
    print ">/100.</TD></TR>";
    print "<TR><TD style=\"width: ".$_POST['width']."px;\">Номер документа</TD>";
    print "<TD><INPUT type=\"text\" name=\"ege[".($i+1)."][document]\" maxlength=\"15\" style=\"width: 120px;\" id=\"ege[".($i+1)."][document]\" class=\"validate[optional,custom[ege]] text-input\""; 
    if ($kval != 0) print " value=\"".$kval['document']."\"";
    print ">.</TD></TR>";
}

print "</TBODY></TABLE>";
unset($mslk);
?>
