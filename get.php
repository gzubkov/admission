<?php
require_once('../conf.php');
require_once('class/mysql.class.php');
$mslk = new dMysql();

$rus = array('Первый','Второй','Третий','Четвертый');

if (!is_numeric($_POST['catalog'])) {
   echo "error";
   exit(0);
}

$gval = $mslk->getarray("SELECT specialty,internet,basicsemestr FROM `catalogs` WHERE `id`='".$_POST['catalog']."'");

echo "<table style=\"display: block;\"><tbody style=\"border: none;\">";

// для эксплуатации и электротехники требуются справки из медучреждения
if ($_POST['catalog'] == 53 ||
    $_POST['catalog'] == 54 ||
    $_POST['catalog'] == 61) {
    echo "<tr><TD colspan=\"2\" style=\"color: red;\">При приёме на обучение по данному направлению подготовки, Вам необходимо пройти обязательные предварительные медицинские осмотры согласно <a href=\"http://admission.iitedu.ru/documents/pdf/pr_2_4.pdf\">приложению к приказу</a>.</TD></TR>\n";
}

if ($gval['basicsemestr'] > 1) {
    echo "<tr><TD colspan=\"2\">С 01.01.2011 поступление на данную образовательную программу возможно только на ".$gval['basicsemestr']." и выше семестры. Для зачисления необходимо предоставить академическую справку, диплом о неполном высшем образовании, диплом о среднем профессиональном образовании или диплом о полном высшем образовании.</TD></TR>\n";
    if ($gval['internet'] > 0) {
        echo "<tr><TD style=\"width: ".$_POST['width']."px;\">Обучение через Интернет<SPAN class=\"form-required\" title=\"Данное поле обязательно для заполнения.\">*</SPAN></TD>\n";
        echo "<TD><LABEL><INPUT type=radio name=\"internet\" value=\"1\" checked>да</LABEL><LABEL><INPUT type=radio name=\"internet\" value=\"0\">нет</LABEL></TD></TR>";    
    } else {
        echo "<INPUT type=\"hidden\" name=\"internet\" value=\"0\">";
    }

    echo "<INPUT type=\"hidden\" name=\"traditional_form\" value=\"1\"></TBODY></TABLE>\n";
} else {
    $prval = $mslk->getarray("SELECT * FROM `catalogs_profiles` a LEFT JOIN `specialties_profiles` b ON a.profile=b.id WHERE a.catalog ='".$_POST['catalog']."' AND applicable=1;", 1);
    if ($prval != 0) {
        echo "<tr><TD style=\"width: ".$_POST['width']."px;\">Профиль</TD>\n";
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

    if ($_SESSION['edu_base'] == 2) {
        echo "<tr><TD style=\"width: ".$_POST['width']."px;\">Имею СПО по профилю<SPAN class=\"form-required\" title=\"Данное поле обязательно для заполнения.\">*</SPAN></TD>\n";
        echo "<TD><LABEL><INPUT type=radio name=\"spo\" value=\"1\">да</LABEL><LABEL><INPUT type=radio name=\"spo\" value=\"0\" checked>нет</LABEL></TD></TR>"; 
    } else {
        echo "<INPUT type=\"hidden\" name=\"spo\" value=\"0\">";
    }

    if ($gval['internet'] > 0) {
        echo "<tr><TD style=\"width: ".$_POST['width']."px;\">Обучение через Интернет<SPAN class=\"form-required\" title=\"Данное поле обязательно для заполнения.\">*</SPAN></TD>\n";
        echo "<TD><LABEL><INPUT type=radio name=\"internet\" value=\"1\" checked>да</LABEL><LABEL><INPUT type=radio name=\"internet\" value=\"0\">нет</LABEL></TD></TR>";    
    } else {
        echo "<tr><TD colspan=2 style=\"font-weight: bold; text-align: center; color: black;\">Обучение через Интернет по данному направлению подготовки невозможно!</TD></TR>\n";
        echo "<INPUT type=\"hidden\" name=\"internet\" value=\"0\">";
    }
    echo "</TBODY></TABLE>";

    echo "<h3>Результаты ЕГЭ предыдущих годов (если имеются)</h3>";
    echo "<TABLE style=\"display: block;\"><TBODY style=\"border: none;\">"; 
    $rval = $mslk->getarray("SELECT subject,name FROM `reg_ege_minscores` LEFT JOIN `reg_subjects` ON `reg_subjects`.id = `reg_ege_minscores`.subject 
                             WHERE specialty = '".$gval['specialty']."' LIMIT 0, 10", 1);

    $cell = 0;
    for ($i = 0; $i < count($rval); $i++) {
        echo "<tr><TD style=\"width: ".$_POST['width']."px;\">".$rus[$i]." предмет</TD><TD>".$rval[$i]['name'].".</TD></TR>";   
        echo "<INPUT type=\"hidden\" name=\"ege[".($i+1)."][subject]\" value=\"".$rval[$i]['subject']."\">";

        $kval = $mslk->getarray("SELECT score,document FROM `reg_applicant_scores` 
                                 WHERE `subject` = ".$rval[$i]['subject']." AND `ege`=1 AND applicant_id=".$_SESSION['applicant_id']." LIMIT 1");
        if ($kval == 0) {
            echo "<tr><TD style=\"width: ".$_POST['width']."px;\">Оценка (в 100-й шкале)</TD>"; //<SPAN class=\"form-required\" title=\"Данное поле обязательно для заполнения.\">*</SPAN></TD>";
            echo "<TD><INPUT type=\"text\" name=\"ege[".($i+1)."][scores]\" maxlength=\"3\" style=\"width: 30px;\" id=\"ege[".($i+1)."][scores]\" class=\"validate[optional,custom[scores]] text-input\">.</TD></TR>";
            echo "<tr><TD style=\"width: ".$_POST['width']."px;\">Номер документа</TD>";
            echo "<td><select name=\"ege[".($i+1)."][document]\" id=\"ege[".($i+1)."][document]\" class=\"validate[optional] text-input\">";
        
            for ($j = date('y'); $j >= 12; $j--) {
                echo "<option value=".$j.">20".$j."</option>";
            }
            echo "</select></td></tr>";
        } else {
            $cell++;
            echo "<tr><TD style=\"width: ".$_POST['width']."px;\">Оценка (в 100-й шкале)</TD>";
            echo "<INPUT type=\"hidden\" name=\"ege[".($i+1)."][scores]\" value=\"".$kval['score']."\"><TD>".$kval['score'].".</TD></TR>";
        
            if ($kval['document'] != 0) {
                echo "<tr><TD style=\"width: ".$_POST['width']."px;\">Год сдачи</TD>";
                echo "<INPUT type=\"hidden\" name=\"ege[".($i+1)."][document]\" value=\"".$kval['document']."\"><TD>".$kval['document'].".</TD></TR>";
            }
        }
    }

    echo "</TBODY></TABLE>";

    echo "<H3>Не имею действующих результатов ЕГЭ и прошу</H3>";
    echo "<TABLE style=\"display: block;\"><TBODY style=\"border: none;\">";
    echo "<tr><LABEL><TD><INPUT type=\"checkbox\" id=\"traditional_form\" name=\"traditional_form\" value=\"1\" checked></TD><TD>Допустить меня до сдачи вступительных экзаменов в традиционно принятой форме в соответствии с действующим законодательством РФ.</LABEL></TD></TR>";
    echo "</TBODY></TABLE>";
}
