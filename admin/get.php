<?php
require_once('../../conf.php');
require_once('../class/mysql.class.php');
require_once('../class/catalog.class.php');
require_once('../class/moodle.class.php');
require_once('../class/documents.class.php');
$msl = new dMysql();
$mdl = new Moodle($msl);

function deleteApplicant($id) 
{
    global $msl;
    if ($msl->deleteArray('reg_applicant', array('id'=>$id))) {
        print "ok\n";
    } else {
        print "couldn't use query";
    }
}

function sendRequestDocs($id) 
{
    global $msl;
    $appl = new Applicant(&$msl, $id);
    $rval = $appl->getInfo('email');
   
    $to      = $appl->surname." ".$appl->name." ".$appl->second_name."<".$rval['e-mail'].">";
    $subject = "Поступление в Университет Машиностроения";

    $message = "
<html>
    <head><title>Поступление в Университет Машиностроения</title></head>
    <body>
        <p>Уважаем".$appl->inflection().", ".$appl->name." ".$appl->second_name."!</p>
        <p>Для поступления в Московский государственный машиностроительный университет (МАМИ) на заочную форму обучения с использованием дистанционных образовательных технологий, Вам необходимо выслать по электронной почте <A href=\"mailto: iit@ins-iit.ru\">iit@ins-iit.ru</A> комплект документов:
<UL type=\"disc\">
<LI>копию паспорта или другого документа, удостоверяющего личность (в т.ч. с данными о регистрации по месту проживания);</LI>
<LI>копию документа об образовании (с приложением);</LI>
<LI>номер телефона для связи.</LI>
</UL></P>
        <P>Копии документов можно представить в виде фотографии, сделанной с использованием сканера, фотоаппарата или мобильного телефона (в приемлемом качестве).</P>
        <P>По всем возникающим вопросам обращайтесь +7 (499) 1277453 доб.20, Ирина Викторовна.</P>
        <P>С уважением, Электронная приемная комиссия</P>
   </body>
</html>";

    $headers  = "Content-type: text/html; charset=utf-8 \r\n";
    $headers .= "From: Электронная приемная комиссия <iit@ins-iit.ru>\r\n";

    return mail($to, $subject, $message, $headers); 
}


function changeType($id, $type) 
{
    global $msl;
    $mail = 0;
    if ($type == 1) {
        $appl = new Applicant(&$msl, $id);

        $rval = $appl->getInfo('email');

        $cat = new Catalog(&$msl);
      	$spc = $cat->getInfo($appl->catalog);
      	$uni = $cat->getUniversityInfo($appl->catalog);
      	unset($cat);
      	 
      	$to      = $appl->surname." ".$appl->name." ".$appl->second_name."<".$rval['e-mail'].">";
        $subject = "Поступление в ".$uni['abbreviation']."";

      	$message = "
<html>
    <head><title>Поступление ".$uni['abbreviation']."</title></head>
    <body>
        <p>Уважаем".$appl->inflection().", ".$appl->name." ".$appl->second_name."!</p>
        <p>Рассмотрев присланные Вами копии документов, предварительно сообщаем, что Вы можете быть зачислены в ".$uni['name']." на ".$appl->semestr." семестр ".ceil($appl->semestr/2)." курса на ".$spc['type']." «".$spc['name']."» заочной формы обучения с использованием дистанционных образовательных технологий.</p>
        <p>Комплект документов абитуриента Вы можете распечатать с личного кабинета, где первоначально регистрировались (<A href=\"http://admission.iitedu.ru/\">http://admission.iitedu.ru/</A>). Для входа в систему используйте адрес электронной почты в качестве логина и в качестве пароля серию и номер паспорта, написанные слитно.</p>
        <p>Вам необходимо распечатать и подписать комплект документов:
<UL type=\"disc\">
<LI>заявление  о зачислении  (1 экз);</LI>
<LI>договор на оказание платных образовательных услуг (3 экземпляра);</LI>
<LI>договор об организации обучения гражданина на платной основе (2 экземпляра);</LI>
<LI>дополнительное соглашение (при необходимости, 2 экземпляра).</LI>
<LI>заявление на выдачу оригинала документа об образовании (даты не ставить);</LI>
<LI>опись документов личного дела.</LI>
</UL></P>
        <P>Кроме этих документов необходимо представить:
<UL type=\"disc\">
<LI>оригинал документа об образовании (с приложением);</LI>
<LI>нотариально заверенную копию свидетельства о результатах ЕГЭ (при наличии);</LI>
<LI>оригинал академической справки или диплома о неполном высшем образовании (при наличии);</LI>
<LI>копию паспорта или другого документа, удостоверяющего личность (в т.ч. с данными о регистрации по месту проживания);</LI>
<LI>при несоответствии фамилии, и (или) имени, и (или) отчества, указанных в документе об образовании и документе, удостоверяющем личность, нотариально заверенную копию документа, подтверждающего изменение фамилии, и (или) имени, и (или) отчества обладателя документа;</LI>
<LI>6 фотографий 3×4 см (одинаковых);</LI>
<LI>копию квитанции на оплату обучения.</LI></UL></P>
        <P>Все вышеперечисленные документы Вам нужно привезти нарочным или отправить по почте на адрес: 
117152, г. Москва, Загородное шоссе, д. 7, корп. 5, строение 1,
\"Институт информационных технологий\".</P>
        <P>После зачисления в ВУЗ, на Ваш электронный адрес будут высланы идентификационные данные для доступа в систему интернет-обучения, а также по почте будут отправлены студенческий билет, учебный план и Ваши экземпляры договоров.</P>
<P>По всем возникающим вопросам обращайтесь +7 (499) 1277453 доб.20, Ирина Викторовна.</P>
<P>С уважением, Электронная приемная комиссия</P>
   </body>
</html>";

        $headers  = "Content-type: text/html; charset=utf-8 \r\n";
      	$headers .= "From: Электронная приемная комиссия <iit@ins-iit.ru>\r\n";
      	if (mail($to, $subject, $message, $headers)) {
	    $mail = 1;
        } 
    } else {
        $mail = 1;
    }

    if ($mail == 1 && $msl->updateArray('reg_applicant', array('type'=>$type), array('id'=>$id))) {
        print "1";
    } else print "Ошибка";
}

function getSpecialties($id) {
    global $msl;
    global $mdl;
    global $CFG_uploaddir;

    $appl = new Applicant($msl, $id);
    $reg  = $appl->getInfo('step', 'homephone_code', 'homephone', 'mobile_code', 'mobile', 'region', 'num', 'email');

    if ($reg['step'] > 2) {
        $addr = end($appl->getAddress());
        print "Дом: +7 (".$reg['homephone_code'].") ".$reg['homephone'].", моб: +7 (".$reg['mobile_code'].") ".$reg['mobile']."<BR>";
      	print "".$addr['city']." (".$addr['regionname'].")<BR>";
    }   

    $cat = new Catalog(&$msl);

    if ($reg['step'] > 2) {
        print "<h3>";
        $spc = $cat->getInfo($appl->catalog, $appl->profile);
      
        print mb_strtoupper(mb_substr($spc['type'], 0, 1)) . mb_strtolower(mb_substr($spc['type'], 1, mb_strlen($spc['type'])))." ".$spc['spec_code']." \"".$spc['name']."\":</h3>";
        if (isset($spc['profile'])) {
            print "(профиль - ".$spc['profile'].")";
        }
        print "<DIV><TABLE style=\"display: block;\"><TBODY style=\"border: none;\">"; 
   
        print "<TR><TD>";
        print "<TR><TD><A href=\"../documents/des.php?applicant=".$id."\"><B>Решение о возможности зачисления абитуриента</B></A></TD></TR>\n";

	$appl->printDocs('../');

        print "</TD></TR>";

      	print "<TR><TD>";
      	print "<SELECT id=\"catalog".$id."\">";
      	$bval = $cat->getAvailableSpecialtiesByPgid(1, "%shortname% - %qualify%");
      	foreach($bval as $k => $v) {
            print "<OPTION value=".$k;
	    if ($k == $appl->catalog) print " selected";
	    print ">".$v."</OPTION>";
        }
      	print "</SELECT> <SELECT id=\"profile".$id."\">";
      	$bval    = $cat->getAllProfiles();
      	$bval[0] = "---";

	foreach($bval as $k=>$v) {
            print "<OPTION value=".$k;
	    if ($k == $appl->profile) print " selected";
	    print ">".$v."</OPTION>";
        }
        print "</SELECT> <A onclick=\"$.ajax({url: 'get.php', type: 'POST', data:'act=setsemestr&id=".$id."&s='+$('#semestr".$id."').val()+'&catalog='+$('#catalog".$id." option:selected').val()+'&profile='+$('#profile".$id." option:selected').val(), beforeSend: function() {\$.blockUI({ centerY: 0, css: { top: '10px', left: '', right: '10px' }, message: 'Ваш запрос обрабатывается...' })}, success: function(msg) {\$.unblockUI(); $('#dialog-message').dialog('close'); if (msg != 1){alert(msg)}}})\">Сменить</A>"; 
        print "</TD></TR>";
   
        print "<TR><TD>";
        print "<INPUT type=text id=\"semestr".$id."\" value=\"".$appl->semestr."\" maxlength=2 style=\"width: 15px;\"> ";

        print "<SELECT id=\"baseedu".$id."\">";
        $earr = $cat->getSubCatalogsByRegion($reg['region'], $appl->catalog);
      
        foreach($earr as $k => $v) {
            print "<OPTION value=".$k;
	    if ($k == $appl->catalog) print " selected";
	    print ">".$v."</OPTION>";
        }
        print "</SELECT> "; 

        print "<SELECT id=\"region".$id."\">";
        $tarr = $msl->getarray("SELECT id, name FROM `partner_regions` WHERE `id` = 1 or `id` = 3 or `id` = 5",1);

        foreach($tarr as $v) {
            print "<OPTION value=".$v['id'];
	    if ($v['id'] == $reg['region']) print " selected";
	    print ">".$v['name']."</OPTION>";
        }
      	print "</SELECT> ";
    

      print "<A onclick=\"$.ajax({url: 'get.php', type: 'POST', data:'act=setsemestr&id=".$id."&s='+$('#semestr".$id."').val()+'&catalog='+$('#baseedu".$id." option:selected').val()+'&region='+$('#region".$id." option:selected').val(), beforeSend: function() {\$.blockUI({ centerY: 0, css: { top: '10px', left: '', right: '10px' }, message: 'Ваш запрос обрабатывается...' })}, success: function(msg) {\$.unblockUI(); if (msg != 1){alert(msg)}}})\">Проставить семестр</A>\n";

      print "</TD></TR>";
      
      if ($appl->semestr > 1) {
         $dval = $appl->getRups();
      	 print "<TR><TD>Досдач <INPUT type=\"text\" id=\"dosdachi".$id."\" value=\"".$dval['rups']."\" maxlength=2 style=\"width: 20px;\">";
	 print " платных <INPUT type=\"text\" maxlength=2 value=\"".$dval['pay']."\" id=\"pay".$id."\" style=\"width: 20px;\"> ";

/*	 print "<SCRIPT type=\"text/javascript\">
		$(function(){	$('#date".$id."').datepicker({minDate: +7});
		});
	      </SCRIPT>";
	 print " дата <INPUT type=\"text\" maxlength=10 value=\"".date('d.m.Y', strtotime($dval['date']))."\" id=\"date".$id."\" style=\"width: 82px;\"> ";
*/
	 print "<A onclick=\"$.ajax({url: 'get.php', type: 'POST', data:'act=savedosdachi&dosdachi='+$('#dosdachi".$id."').val()+'&pay='+$('#pay".$id."').val()+'&date='+$('#date".$id."').val()+'&aid=".$id."', beforeSend: function() {\$.blockUI({ centerY: 0, css: { top: '10px', left: '', right: '10px' }, message: 'Ваш запрос обрабатывается...' })}, success: function(msg) {\$.unblockUI(); if (msg != 1){alert(msg)}}})\">Сохранить</A></TD></TR>";   
      }
      
      switch($appl->type) {
      case 1:
	  print "<TR><TD>";
      	  print "<A onclick=\"$.ajax({url: 'get.php', type: 'POST', data:'act=changetype&type=0&id=".$id."', beforeSend: function() {\$.blockUI({ centerY: 0, css: { top: '10px', left: '', right: '10px' }, message: 'Ваш запрос обрабатывается...' })}, success: function(msg) {\$.unblockUI(); if (msg != 1){alert(msg)}}})\">Запретить заявление</A>\n";
      	  print "</TD></TR>";
	  break;
      default:
          print "<TR><TD>";
      	  print "<A onclick=\"$.ajax({url: 'get.php', type: 'POST', data:'act=changetype&type=1&id=".$id."', beforeSend: function() {\$.blockUI({ centerY: 0, css: { top: '10px', left: '', right: '10px' }, message: 'Ваш запрос обрабатывается...' })}, success: function(msg) {\$.unblockUI(); if (msg != 1){alert(msg)}}})\">Одобрить заявление</A>\n";
      	  print "</TD></TR>";
      }
      print "</TBODY></TABLE></DIV>\n\n";      
   }
  

    $fval = $msl->getarray("SELECT a.id, a.primary, a.filename, b.name FROM reg_applicant_edu_doc a LEFT JOIN reg_edu_doc b ON a.edu_doc=b.id WHERE a.applicant=".$id, 1);
    print "<SCRIPT language=\"jajascript\">
       	       function openDocAttach() {
	          $('#dialog-message2').dialog('option','title', 'Добавление документа').dialog('open');
		  $('#hiddenaid').val(".$id.");
	       }
	       </SCRIPT>";
    if ($fval == 0) {
        
        print "<FONT color=red><B>Не загружено ни одного файла.</B></FONT> <BR><BR>";
    } else {
        print "Загруженые файлы: <UL>";
        foreach($fval as $valf) {
	    print "<LI><A ";
	    if (isset($valf['filename']) && file_exists($CFG_uploaddir.$id."/".$valf['filename'])) print "href=\"view.php?id=".$valf['id']."\" target=\"_blank\"";
	    print ">";
	    if ($valf['primary']) print "<I>";
	    print $valf['name'];
	    if ($valf['primary']) {
	        print "</I></A>";
	    } else {
	        print "</A> (<A href=\"\" onclick=\"$.ajax({url: 'get.php', type: 'POST', data:'act=makeprimary&fid=".$valf['id']."&aid=".$id."'})\" title=\"Сделать первичным\">M</A>)";
	    }
	    print ".</LI>";
      	}
      	print "</UL>\n";
    }
    print "<A onclick=\"openDocAttach();\">Добавить документ вручную</A><BR>";
    print "<A href=\"\" onclick=\"$.ajax({url: 'get.php', type: 'POST', data:'act=sendrequestdocs&id=".$id."'})\">Запросить копию паспорта и документов об образовании</A><BR>\n"; 
    if ($reg['step'] > 2) {
        $moduser = $mdl->searchUser($reg['e-mail']);
	if ($moduser > 0) {
	    print "<b>Moodle: пользователь уже создан.</b><br>";
	} else {
            if ($reg['num'] == 0) {
	        print "<A onclick=\"$.ajax({url: 'get.php', type: 'POST', data:'act=createmoodleusertest&id=".$id."'})\">Создать пользователя в Moodle и назначить на тест</A><BR>\n"; 
	    } else if ($reg['num'] > 0) {
	        print "<A onclick=\"$.ajax({url: 'get.php', type: 'POST', data:'act=createmoodleuser&id=".$id."'})\">Создать пользователя в Moodle</A><BR>\n";  
	    }
	}
	print "Номер личного дела в БД <INPUT type=\"text\" maxlength=5 value=\"".$reg['num']."\" id=\"num".$id."\" style=\"width: 40px;\"> <A href=\"\" onclick=\"$.ajax({url: 'get.php', type: 'POST', data:'act=savebdindex&index='+$('#num".$id."').val()+'&id=".$id."'})\">Сохранить</A><BR>\n";
    }
    print "<A href=\"\" onclick=\"$.ajax({url: 'get.php', type: 'POST', data:'act=deleteapplicant&id=".$id."'})\">Удалить абитуриента</A><BR>\n"; 
}

if ($_POST['act'] == 'login') {
    $r = $msl->getarray("SELECT * FROM users WHERE `e-mail`='".$_POST['login']."'");

    if (md5($CFG_salted.$_POST['password']) == $r['passwd']) {
        $_SESSION['user_id'] = $r['id'];
        if ($r['rights'] == 1) {
            $_SESSION['rights'] = 'admin';
	    $_SESSION['md_rights'] = md5($CFG_salted.$_SESSION['rights']);
	    print "ok";
        } else if ($r['university'] > 0) {
            $_SESSION['university'] = $r['university'];
	    print "university";
        } else {
            print "notadmin";
        }
    } else {
        print "wrongpwd";
    }
    exit(0);
}

if ($_POST['act'] == 'unlogin') {
    $_SESSION = array();
    print "ok";
    exit(0);
}

if ($_SESSION['rights'] == 'admin' && $_SESSION['md_rights'] == md5($CFG_salted.$_SESSION['rights'])) {
        
switch($_POST['act']) 
{
    case 'sendrequestdocs':
        if (sendRequestDocs($_POST['id'])) {
	    print "ok";
	} else {
	    print "error";
        }
	break;
	 
    case 'makeprimary':
         if ($msl->updateArray("reg_applicant_edu_doc", array('primary'=>0), array('applicant'=>$_POST['aid'])) && 
	     $msl->updateArray("reg_applicant_edu_doc", array('primary'=>1), array('id'=>$_POST['fid']))) {
             print "ok";
         } else print "error";
	 break;

    case 'savebdindex':
         if ($msl->updateArray("reg_applicant", array('num'=>$_POST['index']), array('id'=>$_POST['id']))) {
             print "ok";
         } else print "error";
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
	if ($msl->insertArray('reg_applicant_edu_doc', $array)) {
	    print "1";
	} else {
	    print "Ошибка при добавлении документов";
        }
        break;

    case 'createmoodleuser':
        $appl = new Applicant(&$msl, $_POST['id']);
	$addr = end($appl->getAddress());	
	$rval = $appl->getInfo('email','num');

	$id   = $mdl->createUser($appl->name, $appl->surname, $rval['e-mail'], '7428bd7aa76b3ae591ada0f46a2b22e8', $addr['city'], $rval['num']);
        
	$cat = new Catalog(&$msl);
      	$spc = $cat->getInfo($appl->catalog);
      	unset($cat);

	$to = $appl->surname." ".$appl->name." ".$appl->second_name."<".$rval['e-mail'].">";
      	$subject = "Интернет-обучение";

      	$message = "
<html>
    <body>
        <p>Уважаем".$appl->inflection().", ".$appl->name." ".$appl->second_name."!</p>
        <p>Вы зачислены на ".$spc['type']." «".$spc['name']."» системы электронного обучения (<A href=\"http://moodle.ins-iit.ru/\">http://moodle.ins-iit.ru/</A>)</p>
        <p>Для входа в систему используйте адрес электронной почты как логин и временный пароль \"123456\".</p>
 	<P>По всем возникающим вопросам обращайтесь +7 (499) 1277453 доб.20, Ирина Викторовна.</P>
	<P>С уважением, Электронная приемная комиссия</P>
   </body>
</html>";

	$headers  = "Content-type: text/html; charset=utf-8 \r\n";
      	$headers .= "From: Интернет-обучение <iit@ins-iit.ru>\r\n";
      	if (mail($to, $subject, $message, $headers) && $id > 0) {
	    print "ok";
	} else print "error";
        break;

    case 'createmoodleusertest':
        $appl = new Applicant(&$msl, $_POST['id']);
	$addr = end($appl->getAddress());	
	$rval = $appl->getInfo('email');

	$id   = $mdl->createUser($appl->name, $appl->surname, $rval['e-mail'], '7428bd7aa76b3ae591ada0f46a2b22e8', $addr['city']);
	$mdl->assignTest($id);
       
	$cat = new Catalog(&$msl);
      	$spc = $cat->getInfo($appl->catalog);
      	unset($cat);

	$subjects = implode(", ", $msl->getarrayById("SELECT id, name FROM `reg_ege_minscores` LEFT JOIN `reg_subjects` ON `reg_subjects`.id = `reg_ege_minscores`.subject 
                  WHERE specialty = '".$spc['id']."' LIMIT 0, 10", "id", "name"));
      	$to = $appl->surname." ".$appl->name." ".$appl->second_name."<".$rval['e-mail'].">";
      	$subject = "Вступительные испытания";

      	$message = "
<html>
    <body>
        <p>Уважаем".$appl->inflection().", ".$appl->name." ".$appl->second_name."!</p>
        <p>Для поступления на ".$spc['type']." «".$spc['name']."» Вам необходимо пройти вступительные испытания по следующим дисциплинам: ".$subjects.". Вы можете пройти их в любое удобное для Вас время в разделе «Вступительные испытания» системы электронного обучения (<A href=\"http://moodle.ins-iit.ru/course/view.php?id=71\">http://moodle.ins-iit.ru/</A>)</p>
        <p>Для входа в систему используйте адрес электронной почты как логин и временный пароль \"123456\".</p>
        <p>В случае неуспешной сдачи вступительных испытаний, Вам будет предложено пройти их еще раз.</P>
 	<P>По всем возникающим вопросам обращайтесь +7 (499) 1277453 доб.20, Ирина Викторовна.</P>
	<P>С уважением, Электронная приемная комиссия</P>
   </body>
</html>";

	$headers  = "Content-type: text/html; charset=utf-8 \r\n";
      	$headers .= "From: Электронная приемная комиссия <iit@ins-iit.ru>\r\n";
      	if (mail($to, $subject, $message, $headers) && $id > 0) {
	    print "ok";
	} else print "error";
        break;

    case 'deleteapplicant':
        deleteApplicant($_POST['id']);
	break;

    case 'changetype':
        changeType($_POST['id'],$_POST['type']);
	break;

    case 'setsemestr':
        if ($msl->updateArray('reg_applicant', array('semestr'=>$_POST['s'],'catalog'=>$_POST['catalog'],'profile'=>$_POST['profile'],'region'=>$_POST['region']), array('id'=>$_POST['id']))) {
    	    print "1";
	} else print "Ошибка сохранения семестра";
	break;

    case 'getspecialties':
        getSpecialties($_POST['id']);
      	break;

    default:
        print 'Error';
   }
}
?>
