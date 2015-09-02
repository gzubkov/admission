<?php
require_once '../../conf.php';
require_once '../class/mysql.class.php';

$mslk = new dMysql();

$rus = array('Первый','Второй','Третий','Четвертый');

if (!is_numeric($_POST['catalog'])) {
    echo "error";
    exit(0);
}

$gval = $mslk->getarray("SELECT specialty,internet,basicsemestr,term,termm FROM `catalogs` WHERE `id`='".$_POST['catalog']."'");

echo "<TABLE style=\"display: block;\"><TBODY style=\"border: none;\">";

if ($gval['basicsemestr'] > 1) {
    echo "<TR><TD colspan=\"2\">С 01.01.2011 поступление на данную образовательную программу возможно только на ".$gval['basicsemestr']." и выше семестры. Для зачисления необходимо предоставить академическую справку, диплом о неполном высшем образовании, диплом о среднем профессиональном образовании или диплом о полном высшем образовании.</TD></TR>\n";
    echo "</TBODY></TABLE>"; 
} else {

if ($gval['internet'] > 0) {
    echo "<TR><TD style=\"width: ".$_POST['width']."px;\">Обучение через Интернет<SPAN class=\"form-required\" title=\"Данное поле обязательно для заполнения.\">*</SPAN></TD>\n";
    echo "<TD><LABEL><INPUT type=radio name=\"internet\" value=\"1\" checked>да</LABEL><LABEL><INPUT type=radio name=\"internet\" value=\"0\">нет</LABEL></TD></TR>";    
} else {
    echo "<INPUT type=\"hidden\" name=\"internet\" value=\"0\">";
}

/*    echo "<TR><TD style=\"width: ".$_POST['width']."px;\">Начальный семестр обучения<SPAN class=\"form-required\" title=\"Данное поле обязательно для заполнения.\">*</SPAN></TD>\n";
    echo "<TD><INPUT type=text name=\"semestr\" value=\"";
    if ($_POST['semestr'] != 0) {
        echo "1";
    } else {
        echo $_POST['semestr'];
    }
    echo "\" size=2 maxlength=2> из ".($gval['term']*2+$gval['termm']/6)." (указывать 0, если неизвестно).</TD></TR>"; */

echo "<INPUT type=\"hidden\" name=\"traditional_form\" value=\"1\">\n";

$prval = $mslk->getarray("SELECT b.id,b.name,b.internet FROM `catalogs_profiles` a LEFT JOIN `specialties_profiles` b ON a.profile=b.id WHERE `catalog` ='".$_POST['catalog']."' AND applicable=1;", 1);
if ($prval != 0) {
    echo "<TR><TD style=\"width: ".$_POST['width']."px;\">Профиль</TD>\n";
    if (sizeof($prval) == 1) {
        echo "<TD>".$prval[0]['name'].".<INPUT type=\"hidden\" name=\"profile\" value=\"".$prval[0]['id']."\"></TD></TR>";
    } else {
        echo "<TD><SELECT name=\"profile\">";
	foreach($prval as $v) {
	    echo "<OPTION value=\"".$v['id']."\">".$v['name']."</OPTION>";
	}
	echo "</SELECT></TD></TR>\n";
    }
}

if ($_SESSION['joomlaregion'] == 3) {
    if (isset($_POST['semestr']) === false) {
        $_POST['semestr'] = 1;
    }

    echo "<TR><TD style=\"width: ".$_POST['width']."px;\">Начальный семестр обучения<SPAN class=\"form-required\" title=\"Данное поле обязательно для заполнения.\">*</SPAN></TD>\n";
    echo "<td><select name=\"semestr\">";

    for ($i = 1; $i <= ($gval['term']*2 + $gval['termm']/6); $i++) {
        echo "<option value=\"".$i."\"";
        if ($i == $_POST['semestr']) {
            echo " selected";
        }
        echo ">".$i."</option>";
    }

    echo "</select> (только в порядке восстановления!)</TD></TR>"; 
} else {
    echo "<input type=hidden name=\"semestr\" value=\"1\">"; 
}

echo "<INPUT type=\"hidden\" name=\"spo\" value=\"0\">";
echo "</TBODY></TABLE>";

echo "<h3>Результаты ЕГЭ предыдущих годов (если имеются)</h3>";
echo "<TABLE style=\"display: block;\"><TBODY style=\"border: none;\">"; 
$rval = $mslk->getarray("SELECT b.id,b.name FROM `specialties_subjects` a
                         LEFT JOIN `reg_subjects` b ON b.id = a.subject
                         WHERE a.specialty = '".$gval['specialty']."' LIMIT 3", 1);

$cell = 0;
for ($i = 0; $i < count($rval); $i++) {
    echo "<TR><TD style=\"width: ".$_POST['width']."px;\">".$rus[$i]." предмет</TD><TD>".$rval[$i]['name'].".</TD></TR>";   
    echo "<INPUT type=\"hidden\" name=\"ege[".($i+1)."][subject]\" value=\"".$rval[$i]['id']."\">";

    echo "<TR><TD style=\"width: ".$_POST['width']."px;\">Оценка (в 100-й шкале)</TD>";
    echo "<TD><INPUT type=\"text\" name=\"ege[".($i+1)."][score]\" maxlength=\"3\" style=\"width: 30px;\" id=\"ege[".($i+1)."][score]\" class=\"validate[optional,custom[scores]] text-input\">.</TD></TR>";
    echo "<TR><TD style=\"width: ".$_POST['width']."px;\">Год сдачи</TD>";   
    echo "<td><select name=\"ege[".($i+1)."][year]\" id=\"ege[".($i+1)."][year]\" class=\"validate[optional] text-input\">";
    
    for ($j = date('y'); $j >= 12; $j--) {
        echo "<option value=".$j.">20".$j."</option>";
    }
    echo "</select></td></tr>";
}
echo "</TBODY></TABLE>";

echo "<H3>Сдача вступительных экзаменов</H3>";
echo "<TABLE style=\"display: block;\"><TBODY style=\"border: none;\">";
echo "<TR><LABEL><TD><input type=\"hidden\" name=\"traditional_form\" value=\"0\"> <INPUT type=\"checkbox\" id=\"traditional_form\" name=\"traditional_form\" checked=\"false\" value=\"0\"></TD><TD>Допустить абитуриента до сдачи вступительных экзаменов в традиционно принятой форме.</LABEL></TD></TR>";
echo "</TBODY></TABLE>";
}
?>
