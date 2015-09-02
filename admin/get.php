<?php
require_once('../../conf.php');
require_once('../class/mysql.class.php');
require_once('../class/catalog.class.php');
require_once('../class/moodle.class.php');
require_once('../class/documents.class.php');
require_once('../class/price.class.php');
$msl = new dMysql();
$mdl = new Moodle($msl);

function sendRequestDocs($id) 
{
    global $msl;
    new FabricApplicant($appl, $msl, $id);
    $rval = $appl->getInfo('email');
     
    $to      = $appl->surname." ".$appl->name." ".$appl->second_name."<".$rval['e-mail'].">";
    $subject = "Поступление в Университет Машиностроения";

    $message = "<html><head><title>Поступление в Университет Машиностроения</title></head><body>
                <p>Уважаем".$appl->inflection().", ".$appl->name." ".$appl->second_name."!</p>
                <p>Для поступления в Московский государственный машиностроительный университет (МАМИ) на заочную форму обучения с использованием дистанционных образовательных технологий, Вам необходимо выслать по электронной почте <A href=\"mailto: internet@ins-iit.ru\">internet@ins-iit.ru</A> комплект документов:
                <ul type=\"disc\">
                <li>копию паспорта или другого документа, удостоверяющего личность (в т.ч. с данными о регистрации по месту проживания);</li>
                <li>копию документа об образовании (с приложением);</li>
                <li>номер телефона для связи.</li>
                </UL></P>
                <P>Копии документов можно представить в виде графического изображения, полученного с использованием сканера, фотоаппарата или мобильного телефона (в приемлемом качестве).</P>
                <p>По всем возникающим вопросам обращайтесь +7 (499) 1277453 доб.20.</p>
                <p>С уважением, Электронная приемная комиссия</p></body></html>";

    $headers  = "Content-type: text/html; charset=utf-8 \r\n";
    $headers .= "From: Электронная приемная комиссия <internet@ins-iit.ru>\r\n";

    $msl->updateArray('reg_applicant', array('request'=>date('Y-m-d')), array('id'=>$id));
    return mail($to, $subject, $message, $headers); 
}


function changeType($id, $type) 
{
    global $msl;
    $mail = 0;
    if ($type == 1) {
        new FabricApplicant($appl, $msl, $id);

        $rval = $appl->getInfo('email');

        $cat = new Catalog($msl);
        $spc = $cat->getInfo($appl->catalog);
        $uni = $cat->getUniversityInfo($appl->catalog);
        unset($cat);
                 
        $to      = $appl->surname." ".$appl->name." ".$appl->second_name."<".$rval['e-mail'].">";
        $subject = "Поступление в ".$uni['abbreviation']."";

        $message = "<html><head><title>Поступление ".$uni['abbreviation']."</title></head><body>
                    <p>Уважаем".$appl->inflection().", ".$appl->name." ".$appl->second_name."!</p>
                    <p>Рассмотрев присланные Вами копии документов, предварительно сообщаем, что Вы можете быть зачислены в ".$uni['name']." на ".$appl->semestr." семестр ".ceil($appl->semestr/2)." курса на ".$spc['type']." «".$spc['name']."» заочной формы обучения с использованием дистанционных образовательных технологий.</p>
                    <p>Комплект документов абитуриента Вы можете распечатать с личного кабинета, где первоначально регистрировались (<A href=\"http://admission.iitedu.ru/\">http://admission.iitedu.ru/</A>). Для входа в систему используйте адрес электронной почты в качестве логина и в качестве пароля серию и номер паспорта, написанные слитно.</p>
                    <p>Вам необходимо распечатать и подписать комплект документов: <ul type=\"disc\">
                    <li>заявление  о зачислении  (1 экз);</li>
                    <li>договор на оказание платных образовательных услуг (3 экземпляра);</li>
                    <li>договор об организации обучения гражданина на платной основе (2 экземпляра);</li>
                    <li>дополнительное соглашение (при необходимости, 2 экземпляра);</li>
                    <li>экзаменационный лист (при необходимости);</li>
                    <li>опись документов личного дела.</li></UL></P>
                    <p>Кроме этих документов необходимо представить: <ul type=\"disc\">
                    <li>оригинал документа об образовании (с приложением);</li>
                    <li>нотариально заверенную копию свидетельства о результатах ЕГЭ (при наличии);</li>
                    <li>оригинал академической справки или диплома о неполном высшем образовании (при наличии);</li>
                    <li>копию паспорта или другого документа, удостоверяющего личность (в т.ч. с данными о регистрации по месту проживания);</li>
                    <li>при несоответствии фамилии, и (или) имени, и (или) отчества, указанных в документе об образовании и документе, удостоверяющем личность, нотариально заверенную копию документа, подтверждающего изменение фамилии, и (или) имени, и (или) отчества обладателя документа;</li>
                    <li>6 фотографий 3×4 см (одинаковых);</li>
                    <li>копию квитанции на оплату обучения.</li></UL></P>
                    <p>Все вышеперечисленные документы Вам нужно привезти нарочным или отправить по почте на адрес: 117152, г. Москва, Загородное шоссе, д. 7, корп. 5, строение 1, \"Институт информационных технологий\".</P>
                    <p>После зачисления в ВУЗ, на Ваш электронный адрес будут высланы идентификационные данные для доступа в систему интернет-обучения, а также по почте будут отправлены студенческий билет и Ваши экземпляры договоров.</P>
                    <p>По всем возникающим вопросам обращайтесь +7 (499) 127-7453 доб.20.</p>
                    <p>С уважением, Электронная приемная комиссия</p></body></html>";

        $headers  = "Content-type: text/html; charset=utf-8 \r\n";
        $headers .= "From: Электронная приемная комиссия <internet@ins-iit.ru>\r\n";
        
        if (mail($to, $subject, $message, $headers) === true &&
            $msl->updateArray('reg_applicant', array('type' => $type, 'step' => 4), array('id' => $id)) === true) {
            echo "1";
            return true;
        }
    } else {
        if ($msl->updateArray('reg_applicant', array('type' => $type), array('id' => $id)) === true) {
            echo "1";
            return true;
        }
    }

    echo "Ошибка";
}

function printSelect($name, $array, $cond = -1) 
{
    echo "<select id=\"".$name."\">";

    foreach ($array as $k => $v) {
        echo "<option value=".$k;
                        
        if ($cond != -1 && 
            $k == $cond) {
            echo " selected";
        }
        echo ">".$v."</option>";
    }
        
    echo "</select>\n";
    return true; 
}

function getSpecialties($id) 
{
    global $msl;
    global $mdl;
    global $CFG_uploaddir;

    new FabricApplicant($appl, $msl, $id);

    $reg  = $appl->getInfo();

    echo "<script language=\"javascript\">
        $.ajaxSetup({url: 'get.php', type: 'POST', 
            beforeSend: function() {\$.blockUI({ centerY: 0, css: { top: '10px', left: '', right: '10px' }, message: 'Ваш запрос обрабатывается...' })}, 
            success: function(msg) {\$.unblockUI(); if (msg != 1){alert(msg)}}});
        </script>";  

    if ($reg['step'] > 2) {
        $addr = $appl->getAddress();
        $addr = end($addr);
        if ($reg['homephone_code'] > 0) {
            $code = $appl->getPhoneCode();
            echo "Дом: +".$code['phone_code']." (".$reg['homephone_code'].") ".$reg['homephone'].", моб: +7 (".$reg['mobile_code'].") ".$reg['mobile']."<br>";
        } else {
            echo "Моб: +7 (".$reg['mobile_code'].") ".$reg['mobile']."<br>";
        } 
        echo "".$addr['city']." (";
        if ($addr['country'] == 1) {
            echo $addr['regionname'];
        } else {
            echo $addr['countryname'];
        }
        echo ")<br>";
    
        if ($reg['birthday'] == 0 || ($addr['country'] == 1 && strlen($reg['doc_code']) == 0)) {
            echo "<span style=\"color: #ff0000; font-weight: bold;\">Возможны ошибки в документах!</span>";
        }
        $cat = new Catalog($msl);
        $spc = $cat->getInfo($appl->catalog, $appl->profile);
            
        echo "<h3>".$spc['code']." \"".$spc['name']."\":</h3>";
        if (isset($spc['profile']) === true) {
            echo "(".$spc['profile'].")";
        }
        
        $price = new Price($msl);
        $allPrice = $price->newgetPrice($appl->catalog, $reg['region']);

        echo "<br><span style=\"font-size: 8pt;\">Стоимость семестра обучения ".intval($allPrice['price'])." рублей.</span>";
        
        if ($appl->internet == 1) {
            echo "<br><span style=\"font-size: 8pt; color: darkblue;\">Обучение через Интернет.</span>";
        }

        echo "<div><table style=\"display: block;\"><tbody style=\"border: none;\">"; 
        echo "<tr><td><a href=\"../documents/des.php?applicant=".$id."\"><b>Решение о возможности зачисления абитуриента</b></a></td></tr>\n";

        $appl->printDocs('../');

        echo "</td></tr><tr><td>";
        printSelect('catalog'.$id, $cat->getAvailableSpecialtiesByPgid(0, "%name%"), $appl->catalog);

        $bval    = $cat->getAllProfiles(0);
        $bval[0] = "---";

        printSelect('profile'.$id, $bval, $appl->profile);

        $maxSemestr = $spc['indterm'][0]*2;
        if ($spc['indterm'][1] == 6) {
            $maxSemestr++;
        }
        $semArray = array(1 => 1);

        for ($i = 2; $i <= $maxSemestr; $i++) {
            $semArray[$i] = $i;
        }

        echo "</td></tr><tr><td>";
        printSelect('semestr'.$id, $semArray, $appl->semestr);

        $earr = $cat->getSubCatalogsByRegion($reg['region'], $appl->catalog, 0, 1);
        if (count($earr) > 1) {
            printSelect('baseedu'.$id, $earr, $appl->catalog);
        } else {
            echo "<input type=\"hidden\" id=\"baseedu".$id."\" value=\"".key($earr)."\"> ".current($earr)." ";
        } 

        $tarr = $msl->getarrayById("SELECT id, name FROM `partner_regions` WHERE `id` = 1 or `id` = 3 or `id` = 5",'id','name');
        printSelect('region'.$id, $tarr, $reg['region']);
        
        print "<a onclick=\"$.ajax({url: 'get.php', type: 'POST', data:'act=setsemestr&id=".$id."&s='+$('#semestr".$id."').val()+'&catalog='+$('#catalog".$id." option:selected').val()+'&profile='+$('#profile".$id." option:selected').val()+'&region='+$('#region".$id." option:selected').val(), beforeSend: function() {\$.blockUI({ centerY: 0, css: { top: '10px', left: '', right: '10px' }, message: 'Ваш запрос обрабатывается...' })}, success: function(msg) {\$.unblockUI(); if (msg != 1){alert(msg)}}})\">Проставить семестр и регион</a>\n";
        print "</td></tr>";
            
        if ($appl->semestr > 1) {
            $dval = $appl->getRups();
            echo "<TR><TD>Досдач <INPUT type=\"text\" id=\"dosdachi".$id."\" value=\"".$dval['rups']."\" maxlength=2 style=\"width: 20px;\">";
            echo " платных <INPUT type=\"text\" maxlength=2 value=\"".$dval['pay']."\" id=\"pay".$id."\" style=\"width: 20px;\"> ";
            echo "<A onclick=\"$.ajax({url: 'get.php', type: 'POST', data:'act=savedosdachi&dosdachi='+$('#dosdachi".$id."').val()+'&pay='+$('#pay".$id."').val()+'&date='+$('#date".$id."').val()+'&aid=".$id."', beforeSend: function() {\$.blockUI({ centerY: 0, css: { top: '10px', left: '', right: '10px' }, message: 'Ваш запрос обрабатывается...' })}, success: function(msg) {\$.unblockUI(); if (msg != 1){alert(msg)}}})\">Сохранить</A></TD></TR>";   
        }
   
        echo "<tr><td>";
        switch($appl->type) {
        case 1:
            echo "<a href=\"#\" onclick=\"$.ajax({data:'act=changetype&type=0&id=".$id."'})\">Запретить заявление</a>\n";
            break;
        default:
            echo "<a href=\"#\" onclick=\"$.ajax({data:'act=changetype&type=1&id=".$id."'})\">Одобрить заявление</a>\n";
            echo "/ <a href=\"#\" onclick=\"$.ajax({data:'act=revokeapplicant&id=".$id."'})\">Отказать абитуриенту</a>\n"; 
        }
        echo "</td></tr></TBODY></TABLE></DIV>\n\n";      
    }
    
    $ege = $appl->getEge();
    if ($ege != 0) {
        $year = 0;
        $string = '';
        echo "<span style=\"font-size: 8pt;\"><b>ЕГЭ</b> (";

        foreach ($ege as $key => $value) {
            $year = $value['year'];
            $string .= $value['name']." - ";

            if ($value['score'] < $value['min']) {
                $string .= "<span style=\"color: #ff0000; font-weight: bold;\">".$value['score']."</span>";
            } else {
                $string .= $value['score'];
            }

            if ($key < sizeof($ege)-1) {
                $string .= ", ";
            }
        }

        if ($year == 0) {
            echo "<span style=\"color: #ff0000; font-weight: bold;\">Год сдачи не указан!</span>";
        } else {
            echo "20".$year;
        }
        echo "): ".$string.".</span>";
        
        if (sizeof($ege) < 3) {
            echo " <span style=\"color: #ff0000; font-size: 8pt;\">Сдано ".sizeof($ege)." предмета из 3!</span>";
        }
        echo "<br>";
    }

    $fval = $msl->getarray("SELECT a.id, a.primary, a.filename, b.name FROM reg_applicant_edu_doc a LEFT JOIN reg_edu_doc b ON a.edu_doc=b.id WHERE a.applicant=".$id, 1);
    echo "<script language=\"javascript\">
          function openDocAttach() {
             $('#dialog-message2').dialog('option','title', 'Добавление документа').dialog('open');
             $('#hiddenaid').val(".$id.");
          }</script>";

    if ($fval == 0) {            
        echo "<font color=red><b>Не загружено ни одного файла.</b></font><br>";
    } else {
        echo "Загруженые файлы: <ul>";
        
        foreach ($fval as $valf) {
            echo "<li><a";
            if (isset($valf['filename']) === true && 
                file_exists($CFG_uploaddir.$id."/".$valf['filename']) === true) {
                echo " href=\"view.php?id=".$valf['id']."\" target=\"_blank\"";
            }

            echo ">";
            if ($valf['primary']) {
                print "<i>".$valf['name']."</i></a>";
            } else {
                print $valf['name']."</a> (<a href=\"#\" onclick=\"$.ajax({data:'act=makeprimary&fid=".$valf['id']."&aid=".$id."'})\" title=\"Сделать документом по умолчанию\">M</a>)";
            }
            
            echo ".</li>";
        }
        echo "</ul>\n";
    }
    
    echo "<a onclick=\"openDocAttach();\">Добавить документ вручную</a><br>";

    if ($reg['request'] == 0) {
        echo "<a href=\"\" onclick=\"$.ajax({url: 'get.php', type: 'POST', data:'act=sendrequestdocs&id=".$id."'})\">Запросить копию паспорта и документов об образовании</a><br>\n"; 
    } else {
        echo "Документы были запрошены <b>".date('d.m.Y', strtotime($reg['request']))."</b>. <A href=\"\" onclick=\"$.ajax({url: 'get.php', type: 'POST', data:'act=sendrequestdocs&id=".$id."'})\">Запросить повторно</A><BR>\n"; 
    }

    if ($reg['step'] > 2) {
        $moduser = $mdl->searchUser($reg['e-mail']);
        
        if ($moduser > 0) {
            echo "<b>Moodle: пользователь уже создан.</b><br>";

            if ($mdl->isAssigned($moduser) === true) {
                $subjects = $cat->getSubjects($appl->catalog);

                echo "<span style=\"font-size: 8pt;\">";
                $k = 0;
            
                foreach ($subjects as $value) {
                    if ($k > 0) {
                        echo ", ";
                    }
                    echo $value['subject']." - ";

                    $grade = $mdl->getGrades($moduser, $value['mid'], $value['mid_old']);

                    if ($grade < 0) {
                        echo "<span style=\"color: darkblue; font-weight: bold;\">не сдавал</span>";
                    } elseif ($grade < $value['min']) {
                        echo "<span style=\"color: #ff0000; font-weight: bold;\">".$grade."</span> (".$value['min'].")";
                    } else {
                        echo $grade;
                    }
                    $k = 1;
                }
                echo ".</span><br>";
            }
        } else {
            if ($reg['num'] == 0) {
                print "<a onclick=\"$.ajax({url: 'get.php', type: 'POST', data:'act=createmoodleusertest&id=".$id."'})\">Создать пользователя в Moodle и назначить на тест</A><BR>\n"; 
            } else if ($reg['num'] > 0) {
                print "<a onclick=\"$.ajax({url: 'get.php', type: 'POST', data:'act=createmoodleuser&id=".$id."'})\">Создать пользователя в Moodle</A><BR>\n";  
            }
        }

        if ($reg['type'] == 1) {
            echo "Номер личного дела в БД <INPUT type=\"text\" maxlength=5 value=\"".$reg['num']."\" id=\"num".$id."\" style=\"width: 40px;\"> <A href=\"\" onclick=\"$.ajax({url: 'get.php', type: 'POST', data:'act=savebdindex&index='+$('#num".$id."').val()+'&id=".$id."'})\">Сохранить</A><BR>\n";
        }
    }
        
    print "<a href=\"\" onclick=\"$.ajax({url: 'get.php', type: 'POST', data:'act=deleteapplicant&id=".$id."'})\">Удалить абитуриента</a><br>\n"; 
}

if ($_POST['act'] == 'login') {
    $r = $msl->getarray("SELECT * FROM users WHERE `e-mail`='".$_POST['login']."'");

    if (md5($CFG_salted.$_POST['password']) == $r['passwd']) {
        $_SESSION['user_id'] = $r['id'];
        
        if ($r['rights'] == 1) {
            $_SESSION['rights'] = 'admin';
            $_SESSION['md_rights'] = md5($CFG_salted.$_SESSION['rights']);
            echo "ok";
        } else if ($r['university'] > 0) {
            $_SESSION['university'] = $r['university'];
            echo "university";
        } else {
            echo "notadmin";
        }
    } else {
        print "wrongpwd";
    }
    exit(0);
}

if ($_POST['act'] == 'unlogin') {
    $_SESSION = array();
    echo "ok";
    exit(0);
}

if ($_SESSION['rights'] == 'admin' && $_SESSION['md_rights'] == md5($CFG_salted.$_SESSION['rights'])) {
    switch ($_POST['act']) {
    case 'sendrequestdocs':
        if (sendRequestDocs($_POST['id'])) {
            print "ok";
        } else {
            print "error";
        }
        break;
     
    case 'makeprimary':
        if ($msl->updateArray("reg_applicant_edu_doc", array('primary' => 0), array('applicant' => $_POST['aid'])) && 
            $msl->updateArray("reg_applicant_edu_doc", array('primary' => 1), array('id' => $_POST['fid']))) {
            echo "1";
        } else {
            echo "error";
        }
        break;

    case 'savebdindex':
        if ($msl->updateArray("reg_applicant", array('num'=>$_POST['index']), array('id'=>$_POST['id']))) {
            echo "ok";
        } else {
            echo "error";
        }
        break;

    case 'savedosdachi':
        $date = implode("-", array_reverse(explode(".",$_POST['date'])));

        if ($msl->insertArray("reg_institution_additional", array('rups'=>$_POST['dosdachi'], 'pay'=>$_POST['pay'], 'date'=>$date, 'applicant_id'=>$_POST['aid']))) {
            print "1";
        } else {
            print "Произошла ошибка.";
        }
        break;
            
    case 'attachdoc':
        $array = array('applicant' => $_POST['aid'],
                       'edu_doc' => $_POST['doctype'],
                       'serie' => $_POST['docserie'],
                       'number' => $_POST['docnumber'],
                       'institution' => $_POST['docinstitution'],
                       'date' => implode("-", array_reverse(explode(".",$_POST['docdate']))),
                       'specialty' => $_POST['docspecialty'],
                       'primary' => '1');
        if ($msl->updateArray('reg_applicant_edu_doc', array('primary' => 0), array('applicant' => $_POST['aid'])) && 
            $msl->insertArray('reg_applicant_edu_doc', $array)) {
            print "1";
        } else {
            print "Ошибка при добавлении документов";
        }
        break;

    case 'createmoodleuser':
        new FabricApplicant($appl, $msl, $_POST['id']);

        $addr = end($appl->getAddress());   
        $rval = $appl->getInfo('email','num');

        $id = $mdl->createUser($appl->name, $appl->surname, $rval['e-mail'], '7428bd7aa76b3ae591ada0f46a2b22e8', $addr['city'], $rval['num']);
                
        $cat = new Catalog($msl);
        $spc = $cat->getInfo($appl->catalog);
        unset($cat);

        $to = $appl->surname." ".$appl->name." ".$appl->second_name."<".$rval['e-mail'].">";
        
        $message = "<html><body>
                    <p>Уважаем".$appl->inflection().", ".$appl->name." ".$appl->second_name."!</p>
                    <p>Вы зачислены на ".$spc['type']." «".$spc['name']."» системы электронного обучения (<a href=\"http://moodle.ins-iit.ru/\">http://moodle.ins-iit.ru/</a>)</p>
                    <p>Для входа в систему используйте адрес электронной почты как логин и временный пароль \"123456\".</p>
                    <p>По всем возникающим вопросам обращайтесь +7 (499) 1277453 доб.20.</p>
                    <p>С уважением, Электронная приемная комиссия</p></body></html>";

        $headers  = "Content-type: text/html; charset=utf-8 \r\n";
        $headers .= "From: Интернет-обучение <internet@ins-iit.ru>\r\n";
                
        if (mail($to, "Интернет-обучение", $message, $headers) && $id > 0) {
            print "ok";
        } else {
            print "error";
        }
        break;

    case 'createmoodleusertest':
        new FabricApplicant($appl, $msl, $_POST['id']);

        $addr = end($appl->getAddress());   
        $rval = $appl->getInfo('email');

        $id = $mdl->createUser($appl->name, $appl->surname, $rval['e-mail'], '7428bd7aa76b3ae591ada0f46a2b22e8', $addr['city']);
        $mdl->assignTest($id);
             
        $cat = new Catalog($msl);
        $spc = $cat->getInfo($appl->catalog);
        unset($cat);

        $subjects = implode(", ", $msl->getarrayById("SELECT b.id, b.name FROM `specialties_subjects` a
                                                      LEFT JOIN `reg_subjects` b ON b.id = a.subject
                                                      WHERE specialty = '".$spc['id']."' LIMIT 0, 10", "id", "name"));
        $to = $appl->surname." ".$appl->name." ".$appl->second_name."<".$rval['e-mail'].">";
        
        $message = "<html><body><p>Уважаем".$appl->inflection().", ".$appl->name." ".$appl->second_name."!</p>
                    <p>Для поступления на ".$spc['type']." «".$spc['name']."» Вам необходимо пройти вступительные испытания по следующим дисциплинам: ".$subjects.". 
                    Вы можете пройти их в любое удобное для Вас время в разделе «Вступительные испытания» системы электронного обучения (
                    <a href=\"http://moodle.ins-iit.ru/course/view.php?id=71\">http://moodle.ins-iit.ru/</a>)</p>
                    <p>Для входа в систему используйте адрес электронной почты как логин и временный пароль \"123456\".</p>
                    <p>В случае неуспешной сдачи вступительных испытаний, Вам будет предложено пройти их еще раз.</p>
                    <p>По всем возникающим вопросам обращайтесь +7 (499) 127-7453 доб.20.</p>
                    <p>С уважением, Электронная приемная комиссия</p></body></html>";

        $headers  = "Content-type: text/html; charset=utf-8 \r\n";
        $headers .= "From: Электронная приемная комиссия <internet@ins-iit.ru>\r\n";
                
        if (mail($to, "Вступительные испытания", $message, $headers) && 
            $id > 0) {
            print "ok";
        } else {
            print "error";
        }
        break;

    case 'deleteapplicant':
        if ($msl->deleteArray('reg_applicant', array('id' => $_POST['id'])) === true) {
            print "ok\n";
        } else {
            print "Ошибка при удалении поступающего ".$_POST['id']."!";
        }
        break;

    case 'revokeapplicant':
        if ($msl->updateArray('reg_applicant', array('type' => '2'), array('id' => $_POST['id']))) {
            print "1";
        } else {
            print "Ошибка при сохранении отказа.";
        }
        break;

    case 'changetype':
        changeType($_POST['id'], $_POST['type']);
        break;

    case 'setsemestr':
        if ($msl->updateArray('reg_applicant', array('semestr' => $_POST['s'], 'catalog' => $_POST['catalog'], 'profile' => $_POST['profile'], 'region' => $_POST['region']), array('id' => $_POST['id']))) {
            print "1";
        } else {
            print "Ошибка сохранения семестра";
        }
        break;

    case 'getspecialties':
        getSpecialties($_POST['id']);
        break;

    default:
        echo 'Error';
    }
}
