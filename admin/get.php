<?php
require_once('../../conf.php');
require_once('../../../modules/mysql.php');
require_once('../class/catalog.class.php');
require_once('../class/moodle.class.php');
$msl = new dMysql();

function deleteApplicant($id) {
    global $msl;
    if ($msl->deleteArray('reg_applicant', array('id'=>$id))) {
        print "ok\n";
    } else {
        print "couldn't use query";
    }
}

function deleteRequest($id) {
    global $msl;
    if ($msl->deleteArray('reg_request', array('id'=>$id))) {
        print "ok\n";
    } else {
        print "couldn't use query";
    }
}

function sendRequestDocs($id) {
    global $msl;
    $rval = $msl->getarray("SELECT surname,name,second_name,`e-mail`,sex FROM reg_applicant WHERE id = ".$id);
   
   $to = $rval['surname']." ".$rval['name']." ".$rval['second_name']."<".$rval['e-mail'].">";
   $subject = "Поступление в МГТУ \"МАМИ\"";

   $message = "
<html>
    <head><title>Поступление в МГТУ \"МАМИ\"</title></head>
    <body>
        <p>Уважаем".(($rval['sex'] == 'M') ? "ый" : "ая").", ".$rval['name']." ".$rval['second_name']."!</p>
        <p>Для поступления в МГТУ МАМИ на заочную форму обучения с использованием дистанционных образовательных технологий, Вам необходимо выслать по электронной почте <A href=\"mailto: iit@ins-iit.ru\">iit@ins-iit.ru</A> комплект документов:
<UL type=\"disc\">
<LI>копию паспорта или другого документа, удостоверяющего личность (в т.ч. с данными о регистрации по месту проживания);</LI>
<LI>копию документа об образовании (с приложением).</LI></UL></P>
        <P>Копии документов можно предствить в виде фотографии, сделанной с использованием сканера, фотоаппарата или мобильного телефона (в приемлемом качестве).</P>
        <P>По всем возникающим вопросам обращайтесь +7 (495) 6631562 доб.20, Ирина Викторовна.</P>
        <P>С уважением, Институт Информационных Технологий</P>
   </body>
</html>";

    $headers  = "Content-type: text/html; charset=utf-8 \r\n";
    $headers .= "From: Электронная приемная комиссия <iit@ins-iit.ru>\r\n";

    return mail($to, $subject, $message, $headers); 
}


function changeType($id,$type) {
    global $msl;
    $mail = 0;
   if ($type == 1) {
      $rval = $msl->getarray("SELECT surname,name,second_name,`e-mail`,sex,b.semestr,b.catalog FROM reg_applicant a LEFT JOIN reg_request b ON a.id = b.applicant_id WHERE b.id = ".$id);

      $cat = new Catalog();
      $spc = $cat->getInfo($rval['catalog']);
      $uni = $cat->getUniversityInfo($rval['catalog']);
      unset($cat);
      	 
      $to = $rval['surname']." ".$rval['name']." ".$rval['second_name']."<".$rval['e-mail'].">";
      $subject = "Поступление в ".$uni['abbreviation']."";

      $message = "
<html>
    <head><title>Поступление ".$uni['abbreviation']."</title></head>
    <body>
        <p>Уважаем".(($rval['sex'] == 'M') ? "ый" : "ая").", ".$rval['name']." ".$rval['second_name']."!</p>
        <p>Рассмотрев присланные Вами копии документов, предварительно сообщаем, что Вы можете быть зачислены в ".$uni['name']." на ".$rval['semestr']." семестр ".ceil($rval['semestr']/2)." курса на ".$spc['type']." «".$spc['name']."» заочной формы обучения с использованием дистанционных образовательных технологий.</p>
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
<LI>нотариально заверенную копию документа об образовании (с приложением);</LI>
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
<P>По всем возникающим вопросам обращайтесь +7 (495) 6631562 доб.20, Ирина Викторовна.</P>
<P>С уважением, Институт Информационных Технологий</P>
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

    if ($mail == 1 && $msl->updateArray('reg_request', array('type'=>$type), array('id'=>$id))) {
        print "1";
    } else print "Ошибка";
}

function getSpecialties($id) {
   $rval = getarray("SELECT a.id, a.semestr, a.type, a.catalog, a.profile 
                     FROM reg_request a
                     WHERE a.applicant_id='".$id."'", 1);

   $reg = getarray("SELECT step, `homeaddress-city` , b.name, homephone_code, homephone, mobile_code, mobile, region, num
FROM reg_applicant a JOIN reg_rf_subject b ON a.`homeaddress-region`=b.id 
WHERE a.id ='".$id."'");
   
   if ($reg['step'] > 1) {
      print "Дом: +7 (".$reg['homephone_code'].") ".$reg['homephone'].", моб: +7 (".$reg['mobile_code'].") ".$reg['mobile']."<BR>";
      print "".$reg['homeaddress-city']." (".$reg['name'].")<BR>";
   }   

   $cat = new Catalog();

   if (is_array($rval)) {
   foreach($rval as $key => $val) {
      print "<h3>";
      $spc = $cat->getInfo($val['catalog'], $val['profile']);
      
      print mb_strtoupper(mb_substr($spc['type'], 0, 1)) . mb_strtolower(mb_substr($spc['type'], 1, mb_strlen($spc['type'])))." ".$spc['spec_code']." \"".$spc['name']."\":</h3>";
      if (isset($spc['profile'])) {
          print "(профиль - ".$spc['profile'].")";
      }
      print "<DIV><TABLE style=\"display: block;\"><TBODY style=\"border: none;\">"; 
   
      print "<TR><TD>";
      print "<TR><TD><A href=\"../documents/des.php?request=".$val['id']."\"><B>Решение о возможности зачисления абитуриента</B></A></TD></TR>\n";
      switch($val['semestr']) {
         case 1: 
	    print "<TR><TD><A href=\"../documents/anketa.php?request=".$val['id']."\">Заявление абитуриента</A></TD></TR>\n";
  	    break;
         default:
	    print "<TR><TD><A href=\"../documents/anketa2.php?request=".$val['id']."\">Заявление абитуриента</A></TD></TR>\n";
	    print "<TR><TD><A href=\"../documents/perez.php?applicant_id=".$id."\">Заявление о перезачете дисциплин</A></TD></TR>\n";
	    $ival = getarray("SELECT pay FROM reg_institution_additional WHERE request_id='".$val['id']."'");
            if ($ival['pay'] > 0) {
		     print "<TR><TD><A href=\"../documents/ds_ckt.php?request_id=".$val['id']."\">Дополнительное соглашение</A></TD></TR>\n";
		     print "<TR><TD><A href=\"../receipt/kvit.php?purpose=3&request_id=".$val['id']."\">Квитанция для оплаты досдач</A></TD></TR>\n";
	    }
      }
	    print "<TR><TD><A href=\"../documents/opd.php?applicant_id=".$id."\">Анкета-согласие на обработку персональных данных</A></TD></TR>\n";
	    print "<TR><TD><A href=\"../documents/dog_ckt.php?request_id=".$val['id']."\">Договор на оказание платных образовательных услуг</A></TD></TR>\n";
	    print "<TR><TD><A href=\"../documents/dog_ckt_s.php?request_id=".$val['id']."\">Договор об организации обучения гражданина на платной основе</A></TD></TR>\n";
	    print "<TR><TD><A href=\"../documents/diplom.php?applicant_id=".$id."\">Заявление на возврат оригинала документа об образовании</A></TD></TR>\n";
	    print "<TR><TD><A href=\"../documents/opis.php?applicant_id=".$id."\">Опись документов личного дела</A></TD></TR>\n";
	    print "<TR><TD><A href=\"../documents/ekz_list.php?request_id=".$val['id']."\">Экзаменационный лист</A></TD></TR>\n";
	    print "<TR><TD><A href=\"../receipt/kvit.php?request_id=".$val['id']."\">Квитанция на оплату обучения</A></TD></TR>\n";

      print "</TD></TR>";
      print "<TR><TD>";
      print "<A href=\"\" onclick=\"$.ajax({url: 'get.php', type: 'POST', data:'act=deleterequest&rid=".$val['id']."'})\">Удалить заявление</A>\n";
      print "</TD></TR>";
   
      print "<TR><TD>";
      print "<SELECT id=\"catalog".$val['id']."\">";
      $bval = $cat->getAvailableSpecialtiesByPgid(1, "%shortname% - %qualify%");
      foreach($bval as $k => $v) {
         print "<OPTION value=".$k;
	 if ($k == $val['catalog']) print " selected";
	 print ">".$v."</OPTION>";
      }
      print "</SELECT> <A onclick=\"$.ajax({url: 'get.php', type: 'POST', data:'act=setsemestr&rid=".$val['id']."&s='+$('#semestr".$val['id']."').val()+'&catalog='+$('#catalog".$val['id']." option:selected').val(), beforeSend: function() {\$.blockUI({ centerY: 0, css: { top: '10px', left: '', right: '10px' }, message: 'Ваш запрос обрабатывается...' })}, success: function(msg) {\$.unblockUI(); $('#dialog-message').dialog('close'); if (msg != 1){alert(msg)}}})\">Сменить</A>"; 
      print "</TD></TR>";
   
      print "<TR><TD>";
      print "<INPUT type=text id=\"semestr".$val['id']."\" value=\"".$val['semestr']."\" maxlength=2 style=\"width: 15px;\"> ";

      print "<SELECT id=\"baseedu".$val['id']."\">";
      $earr = $cat->getSubCatalogsByRegion($reg['region'], $val['catalog']);
      
      foreach($earr as $k => $v) {
         print "<OPTION value=".$k;
	 if ($k == $val['catalog']) print " selected";
	 print ">".$v."</OPTION>";
      }
      print "</SELECT> "; 

      print "<SELECT id=\"region".$val['id']."\">";
      $tarr = getarray("SELECT id, name FROM `partner_regions` WHERE `id` = 1 or `id` = 3",1);

      foreach($tarr as $v) {
         print "<OPTION value=".$v['id'];
	 if ($v['id'] == $reg['region']) print " selected";
	 print ">".$v['name']."</OPTION>";
      }
      print "</SELECT> ";
    

      print "<A onclick=\"$.ajax({url: 'get.php', type: 'POST', data:'act=setsemestr&aid=".$id."&rid=".$val['id']."&s='+$('#semestr".$val['id']."').val()+'&catalog='+$('#baseedu".$val['id']." option:selected').val()+'&region='+$('#region".$val['id']." option:selected').val(), beforeSend: function() {\$.blockUI({ centerY: 0, css: { top: '10px', left: '', right: '10px' }, message: 'Ваш запрос обрабатывается...' })}, success: function(msg) {\$.unblockUI(); if (msg != 1){alert(msg)}}})\">Проставить семестр</A>\n";

      print "</TD></TR>";
      
      if ($val['semestr'] > 1) {
         $dval = getarray("SELECT rups,pay,date FROM `reg_institution_additional` WHERE request_id=".$val['id']);
      	 print "<TR><TD>Досдач <INPUT type=\"text\" id=\"dosdachi".$val['id']."\" value=\"".$dval['rups']."\" maxlength=2 style=\"width: 20px;\">";
	 print " платных <INPUT type=\"text\" maxlength=2 value=\"".$dval['pay']."\" id=\"pay".$val['id']."\" style=\"width: 20px;\"> ";

	 print "<SCRIPT type=\"text/javascript\">
		$(function(){	$('#date".$val['id']."').datepicker({minDate: +7});
		});
	      </SCRIPT>";
	 print " дата <INPUT type=\"text\" maxlength=10 value=\"".date('d.m.Y', strtotime($dval['date']))."\" id=\"date".$val['id']."\" style=\"width: 82px;\"> ";
	 print "<A onclick=\"$.ajax({url: 'get.php', type: 'POST', data:'act=savedosdachi&dosdachi='+$('#dosdachi".$val['id']."').val()+'&pay='+$('#pay".$val['id']."').val()+'&date='+$('#date".$val['id']."').val()+'&rid=".$val['id']."', beforeSend: function() {\$.blockUI({ centerY: 0, css: { top: '10px', left: '', right: '10px' }, message: 'Ваш запрос обрабатывается...' })}, success: function(msg) {\$.unblockUI(); if (msg != 1){alert(msg)}}})\">Сохранить</A></TD></TR>";   
      }
      
      switch($val['type']) {
         case 1:
	    print "<TR><TD>";
      	    print "<A onclick=\"$.ajax({url: 'get.php', type: 'POST', data:'act=changetype&type=0&rid=".$val['id']."', beforeSend: function() {\$.blockUI({ centerY: 0, css: { top: '10px', left: '', right: '10px' }, message: 'Ваш запрос обрабатывается...' })}, success: function(msg) {\$.unblockUI(); if (msg != 1){alert(msg)}}})\">Запретить заявление</A>\n";
      	    print "</TD></TR>";
	    break;
	 default:
            print "<TR><TD>";
      	    print "<A onclick=\"$.ajax({url: 'get.php', type: 'POST', data:'act=changetype&type=1&rid=".$val['id']."', beforeSend: function() {\$.blockUI({ centerY: 0, css: { top: '10px', left: '', right: '10px' }, message: 'Ваш запрос обрабатывается...' })}, success: function(msg) {\$.unblockUI(); if (msg != 1){alert(msg)}}})\">Одобрить заявление</A>\n";
      	    print "</TD></TR>";
      }
      print "</TBODY></TABLE></DIV>\n\n";      
   }
  }
   $fval = getarray("SELECT a.id, a.primary, b.name FROM reg_applicant_edu_doc a LEFT JOIN reg_edu_doc b ON a.edu_doc=b.id WHERE a.applicant=".$id, 1);
   if ($fval == 0) {
       print "<SCRIPT language=\"jajascript\">
       	      function openDocAttach() {
	          $('#dialog-message2').dialog('option','title', 'Добавление документа').dialog('open');
		  $('#hiddenaid').val(".$id.");
	      }
	      </SCRIPT>";
      print "<FONT color=red><B>Не загружено ни одного файла.</B></FONT> <A onclick=\"openDocAttach();\">Добавить документ вручную</A><BR><BR>";
   } else {
      print "Загруженые файлы: <UL>";
      foreach($fval as $valf) {
         print "<LI><A href=\"view.php?id=".$valf['id']."\" target=\"_blank\">";
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
    print "<A href=\"\" onclick=\"$.ajax({url: 'get.php', type: 'POST', data:'act=sendrequestdocs&id=".$id."'})\">Запросить копию паспорта и документов об образовании</A><BR>\n"; 
    if (isset($val)) {
        print "<A onclick=\"$.ajax({url: 'get.php', type: 'POST', data:'act=createmoodleuser&rid=".$val['id']."'})\">Создать пользователя в Moodle и назначить на тест</A><BR>\n"; 
    	print "Номер личного дела в БД <INPUT type=\"text\" maxlength=5 value=\"".$reg['num']."\" id=\"num".$val['id']."\" style=\"width: 40px;\"> <A href=\"\" onclick=\"$.ajax({url: 'get.php', type: 'POST', data:'act=savebdindex&index='+$('#num".$val['id']."').val()+'&id=".$id."'})\">Сохранить</A><BR>\n"; 
    }
    print "<A href=\"\" onclick=\"$.ajax({url: 'get.php', type: 'POST', data:'act=deleteapplicant&id=".$id."'})\">Удалить абитуриента</A><BR>\n"; 
}

if ($_POST['act'] == 'login') {
    $r = getarray("SELECT * FROM users WHERE `e-mail`='".$_POST['login']."'");

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

        if ($msl->insertArray("reg_institution_additional", array('rups'=>$_POST['dosdachi'], 'pay'=>$_POST['pay'], 'date'=>$date, 'request_id'=>$_POST['rid']))) {
	    print "1";
	} else print "Произошла ошибка.";
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
	} else print "Ошибка при добавлении документов";
        break;

    case 'createmoodleuser':
        $mdl = new Moodle();
	$req = $msl->getarray("SELECT * FROM `admission`.`reg_request` WHERE id = ".$_POST['rid']);
	if (is_array($req)) {
	    $rval = $msl->getarray("SELECT surname,name,second_name,`e-mail`,sex,`homeaddress-city` FROM reg_applicant WHERE id = ".$req['applicant_id']);
	
	    $id = $mdl->createUser($rval['name'], $rval['surname'], $rval['e-mail'], '7428bd7aa76b3ae591ada0f46a2b22e8', $rval['homeaddress-city']);
	    $mdl->assignTest($id);
        }

	$cat = new Catalog();
      	$spc = $cat->getInfo($req['catalog']);
      	unset($cat);

	$subjects = implode(", ", $msl->getarrayById("SELECT id, name FROM `reg_ege_minscores` LEFT JOIN `reg_subjects` ON `reg_subjects`.id = `reg_ege_minscores`.subject 
                  WHERE specialty = '".$spc['id']."' LIMIT 0, 10", "id", "name"));
      	$to = $rval['surname']." ".$rval['name']." ".$rval['second_name']."<".$rval['e-mail'].">";
      	$subject = "Вступительные испытания";

      	$message = "
<html>
    <body>
        <p>Уважаем".(($rval['sex'] == 'M') ? "ый" : "ая").", ".$rval['name']." ".$rval['second_name']."!</p>
        <p>Для поступления на ".$spc['type']." «".$spc['name']."» Вам необходимо пройти вступительные испытания по следующим дисциплинам: ".$subjects.". Вы можете пройти их в любое удобное для Вас время в разделе «Вступительные испытания» системы электронного обучения (<A href=\"http://moodle.ins-iit.ru/course/view.php?id=71\">http://moodle.ins-iit.ru/</A>)</p>
        <p>Для входа в систему используйте адрес электронной почты как логин и временный пароль \"123456\".</p>
        <p>В случае неуспешной сдачи вступительных испытаний, Вам будет предложено пройти их еще раз.</P>
 	<P>По всем возникающим вопросам обращайтесь +7 (495) 6631562 доб.20, Ирина Викторовна.</P>
	<P>С уважением, Институт Информационных Технологий</P>
   </body>
</html>";

	$headers  = "Content-type: text/html; charset=utf-8 \r\n";
      	$headers .= "From: Институт Информационных Технологий - Электронная приемная комиссия <iit@ins-iit.ru>\r\n";
      	if (mail($to, $subject, $message, $headers) && $id > 0) {
	    print "ok";
	} else print "error";
        break;

    case 'deleteapplicant':
        deleteApplicant($_POST['id']);
	break;

    case 'deleterequest':
        deleteRequest($_POST['rid']);
	break;

    case 'changetype':
        changeType($_POST['rid'],$_POST['type']);
	break;

    case 'setsemestr':
        if ($msl->updateArray('reg_request', array('semestr'=>$_POST['s'],'catalog'=>$_POST['catalog']), array('id'=>$_POST['rid'])) &&
    	    $msl->updateArray('reg_applicant', array('region'=>$_POST['region']), array('id'=>$_POST['aid']))) {
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
