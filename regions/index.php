<?php
require_once('../../conf.php');
require_once('../class/mysql.class.php');
require_once('../class/forms.class.php');
require_once('../class/catalog.class.php');
require_once('../class/documents.class.php');
require_once('../class/moodle.class.php');

error_reporting(E_ALL);
ini_set("display_errors", 1); 

$msl = new dMysql();

if (isset($_SESSION['rights'])) {
    if ($_SESSION['rights'] == 'admin' && $_SESSION['md_rights'] == md5($CFG_salted.$_SESSION['rights'])) {
        if (isset($_REQUEST['region'])) {
            $_SESSION['joomlaregion'] = $_REQUEST['region'];
        } 
    }
}

if (isset($_REQUEST['act'])) {
    if ($_REQUEST['act'] == 'createmoodleuser') {
        new FabricApplicant($appl, $msl, $_REQUEST['id']);
        $mdl = new Moodle($msl);
    
        $addr = end($appl->getAddress());   
        $rval = $appl->getInfo('email');
        $num  = $_REQUEST['num'];

        $id = $mdl->createUser($appl->name, $appl->surname, $rval['e-mail'], '7428bd7aa76b3ae591ada0f46a2b22e8', $addr['city'], $num);
        if ($id == 0) {
            echo "Ошибка создания";
            exit(0);
        }
 
        $appl->setNum($num);    
    
        $cat = new Catalog($msl);
        $spc = $cat->getInfo($appl->catalog);
        unset($cat);

        $to      = $appl->surname." ".$appl->name." ".$appl->second_name."<".$rval['e-mail'].">, <gzubkov@gmail.com>";
        $subject = "Интернет-обучение";
        $message = "<html><body>
            <p>Уважаем".$appl->inflection().", ".$appl->name." ".$appl->second_name."!</p>
            <p>Вы зачислены на ".$spc['type']." «".$spc['name']."» системы электронного обучения (<A href=\"http://moodle.ins-iit.ru/\">http://moodle.ins-iit.ru/</A>)</p>
            <p>Для входа в систему используйте адрес электронной почты как логин и временный пароль \"123456\".</p>
            <p>По всем возникающим вопросам обращайтесь +7 (499) 1277453 доб.20.</P>
            <p>С уважением, Электронная приемная комиссия</P></body></html>";

        $headers  = "Content-type: text/html; charset=utf-8 \r\n From: Система интернет-обучения <iit@ins-iit.ru>\r\n";
   
        if (mail($to, $subject, $message, $headers) && $id > 0) {
            echo "ok";
        } else {
            echo "error";
        }
        exit(0);
    }
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html class="js" dir="ltr" xml:lang="en" xmlns="http://www.w3.org/1999/xhtml" lang="en"><head>

<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Личный кабинет регионального партнера</title>

<link type="text/css" rel="stylesheet" media="all" href="../images/defaults.css">
<link type="text/css" rel="stylesheet" media="all" href="../images/system.css">
<link type="text/css" rel="stylesheet" media="all" href="../images/style.css">
<link type="text/css" rel="stylesheet" media="all" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css">
<link type="text/css" rel="stylesheet" media="all" href="//cdn.datatables.net/1.10.2/css/jquery.dataTables.min.css">

<!-- Validation -->
<link rel="stylesheet" href="../css/validationEngine.jquery.css" type="text/css" media="screen" title="no title" charset="utf-8" />
<SCRIPT type="text/javascript" src="http://www.position-relative.net/creation/formValidator/js/jquery-1.6.min.js"></script>
<!--<SCRIPT type="text/javascript" src="http://code.jquery.com/jquery-1.10.2.min.js"></script> -->
<!-- jQuery UI -->
<SCRIPT type="text/javascript" src="//code.jquery.com/ui/1.10.4/jquery-ui.js"></script>

<SCRIPT type="text/javascript" src="../js/jquery.ui.datepicker-ru.js"></script>
<SCRIPT type="text/javascript" src="../js/jquery.form.js"></SCRIPT>
<SCRIPT type="text/javascript" src="http://malsup.com/jquery/block/jquery.blockUI.1.33.js"></SCRIPT> 

<!-- Script validation -->
<!--<script type="text/javascript" src="http://www.position-relative.net/creation/formValidator/js/jquery.validationEngine.js"></script>-->

<script type="text/javascript">
$(function() {
   $("input:submit, input:button").button();
   $.datepicker.setDefaults({changeMonth: true, changeYear: true});
   
  //  $('#example').dataTable();

   var oTable;
   var asInitVals = new Array();

   $("#dialog-message").dialog({autoOpen: false, modal: true, width: 680});

   
   $("#catalog").change(function() {
//      $("#catalog").prop("disabled","true"); 
      $.ajax({url: 'get.php', type: 'POST', datatype:'html', data: 'width=180&catalog=' + $('#catalog').val(), beforeSend: function(){}, 
              success: function(msg){
                 $('#ege_fields').html(msg); //.replace(/\s+/, '')
              }})
//      $("#catalog").removeprop("disabled");
    });


    $("#edu_doc").change(function() {
        if ($(this).val() == 9) {
            $("#edit-edu_serie").attr('class', 'text-input').hide();
            $("#edit-edu_serie").parent().parent().children('td:first-child').html('Номер<span class="form-required" title="Данное поле обязательно для заполнения.">*</span>');
        } else if ($("#edit-edu_serie").attr('class') == 'text-input') {
            $("#edit-edu_serie").attr('class', 'validate[required] text-input').show();
            $("#edit-edu_serie").parent().parent().children('td:first-child').html('Серия<span class="form-required" title="Данное поле обязательно для заполнения.">*</span>');
        }
    });

   
});
function loginA() {
   $.ajax({url: 'login.php', type: 'POST', data: 'act=login&'+$('#login').serialize(),
           success: function(msg){
          msg = msg.replace(/\s+/, '');
          switch (msg) {
             case "ok":
            window.location.reload();
            break;
         case "wrongpwd":
            alert('Неправильный пароль!');
            break;
         default:
            alert('Неправильный пароль!');
          }
       }});     
}
</script>

</head>
<body class="sidebar-left">

<?php
if (!isset($_SESSION['joomlaregion'])) {
    if ($_SERVER['REMOTE_ADDR'] == $CFG_trustedip) {
        echo "<div style=\"border: 1px solid #d3d3d3; width: 250px; height: 140px; background-color: #ffffff; margin:20px auto 0pt;\"><form id=\"login\" action=\"\">\n";
        echo "<table style=\"border: none;\"><tbody style=\"border: none;\"><tr><td colspan=\"2\" style=\"text-align: center;\"><b>Вход в систему</b></td></tr>";
        echo "<tr><td style=\"width: 70px;\">Логин:</td><td><input type=\"text\" name=\"login\" />.</td></tr>";
        echo "<tr><td style=\"width: 70px;\">Пароль:</td><td><input type=\"password\" name=\"password\" />.</td></tr>";
        echo "<tr><td colspan=\"2\" style=\"text-align: center;\"><input type=\"submit\" value=\"Войти\" onclick=\"javascript: loginA(); return false;\" /></td></tr>";
        echo "</tbody></table></form></div>";
    }
    exit(0);
} 

$region_id = $_SESSION['joomlaregion'];
?>

<!-- Layout -->
  <div id="header-region" class="clear-block"></div>

    <div id="wrapper">
    <div id="container" class="clear-block">

      <div id="header">
        <div id="logo-floater">
        <h1><IMG src="../images/ckt.png" style="width: 170px;"><span>Личный кабинет регионального партнера</span></h1>        </div>

                                                    
      </div> <!-- /header -->

              <div id="sidebar-left" class="sidebar">
                    <div id="block-user-0" class="clear-block block block-user"><h2>Организация</h2>
  <div class="content">

<?php
    $rval = $msl->getarray("SELECT firm,approved,`base_password` FROM `partner_regions` WHERE id='".$region_id."'");
    echo "<div class=\"form-item\">".$rval['firm']."</div>";
?>
</div>

</div>
<div id="block-user-1" class="clear-block block block-user">

  <h2>Действия</h2>

  <div class="content"><ul class="menu">
   <li class="collapsed last"><a href="?act=addapplicant">Добавить абитуриента</a></li> 
   <li class="collapsed last"><a href="?act=listapplicant">Список абитуриентов</a></li>

<?php
/*
 * Заглушка для региона ЦКТ
 */
 
if ($region_id == 3) {
    echo "<li class=\"collapsed last\"><a href=\"?act=receipt\">Распечатать квитанцию</a></li>";
} else {
    echo "<li class=\"collapsed last\"><a href=\"index_students.php?act=basestudent\">Список студентов</a></li>
          <li class=\"collapsed last\"><a href=\"index.php?act=card\">Мои данные</a></li>";
}
?> 
<!--  <li class="collapsed last"><a href="?act=receipt">Распечатать квитанцию</a></li> -->
  <li class="collapsed last"><a href="?">Вернуться на главную</a></li>
  </ul></div>
</div>
        </div>
      
      <div id="center"><div id="squeeze"><div class="right-corner"><div class="left-corner">
                                                                                          <div class="clear-block">
            <div id="first-time">

<?php

if (isset($_REQUEST['act']) === false) {
    $_REQUEST['act'] = '';
}

switch($_REQUEST['act']) {
case "addapplicant":
    echo "<script type=\"text/javascript\" src=\"../js/jquery.validationEngine-ru.js\"></script>
          <script type=\"text/javascript\" src=\"../js/jquery.validationEngine.js\"></script>
          <script language=\"javascript\">$(function() {
              $(\"#formular\").validationEngine({failure: function() {setTimeout(\"$.validationEngine.closePrompt('.formError',true)\", 10000)}}); 
          });
          </script>";
    echo "<H1 class=\"title\">Добавление абитуриента</H1><DIV id=\"output\"></DIV>";

    if ($rval['approved'] == 0) {    
        echo "<P>Продолжение работы невозможно без подтверждения Вами сведений о региональном партнере. Для подтверждения <A href=\"?act=card\">проверьте свои данные</A>.</P>\n\n";
        break;
    }       
    echo "<P>Пожалуйста заполните следующие поля (поля отмеченные * обязательны для заполнения):</P>\n\n";
    echo "<DIV id=\"myaccordion\">";

    $form = new FormFields('index.php','formular', 180, 0, "Добавить абитуриента");
    $rpval = $msl->getarray("SELECT rf FROM `partner_regions` WHERE id='".$region_id."'");

    echo "<TABLE style=\"display: block;\"><TBODY style=\"border: none;\">"; 
    $form->hidden('region', $region_id);
    $form->hidden('act', 'addstudentinsert');

    /*$partners = $msl->getarray("SELECT agent FROM `partner_contract` WHERE id='".$region_id."'", 1);
    if (sizeof($partners) > 1) {
        $form->selectInput('Партнер', 'agent', array(2 => 'ИИТ', 1 => 'ЦКТ'));
    } else {
        $form->hidden('agent', $partners[0]['agent']);
    } */
    
    $form->hidden('agent', 1);

    $form->textInput('Фамилия', 'surname',  200, 60, 'K');
    $form->textInput('Имя', 'name', 200, 60, 'K');
    $form->textInput('Отчество', 'second_name', 200, 60, 0);

    $form->radioInput(   'Пол',              'sex',         array('M'=>'Мужской','F'=>'Женский'), 'M', 1);
    $form->dateInput( 'Дата рождения',    'birthday',        1950, date('Y')-16, 'D', 75, '1980' );
    $form->radioInput(   'Гражданство',      'citizenry',   array('Российская Федерация'=>'Российская Федерация','other'=>'Другое'), null, 1);

    $form->remark('Паспортные данные');
    $form->hidden('doc_type', 1);
    $form->textInput(array('Серия','Номер'), array('doc_serie','doc_number'),    array(45,70), array(4,6), array('N','N') );
    $form->textInput('Кем выдан',         'doc_issued',  200, 200, 'A' );
    $form->textInput('Код подразделения', 'doc_code',    100, 8, 'kodp' );
    $form->dateInput( 'Дата выдачи',           'doc_date',    1990, date('Y'), 'D' );
    $form->textInput('Место рождения',    'birthplace',  200, 100, 'A' );

    $form->remark('Адрес места жительства');
    $form->textInput('Почтовый индекс',  'homeaddress[index]', 100, 6, 'ON' );

    $aspec = $msl->getArrayById("SELECT id,CONCAT(id,' - ',name) as name FROM `reg_rf_subject` ORDER BY id ASC",'id','name');
    $form->selectInput(  'Субъект РФ', 'homeaddress[region]', $aspec, $rpval['rf'], 1);

    $form->textInput('Населенный пункт',  'homeaddress[city]', 200, 50, 1 );
    $form->textInput('Улица (квартал)',  'homeaddress[street]', 200, 60, 0 );
    $form->textInput(array('Дом','корпус','квартира'),  array('homeaddress[home]','homeaddress[building]','homeaddress[flat]'), array(25,25,25), array(5,4,4), array('A',0,0) );

    $form->remark('Адрес регистрации');
    $form->textInput('Почтовый индекс',  'regaddress[index]', 100, 6, 'ON' );
    $form->selectInput(  'Субъект РФ', 'regaddress[region]', $aspec, $rpval['rf'], 1);
    $form->textInput('Населенный пункт',  'regaddress[city]', 200, 50, 1 );
    $form->textInput('Улица (квартал)',  'regaddress[street]', 200, 60, 0 );
    $form->textInput(array('Дом','корпус','квартира'),  array('regaddress[home]','regaddress[building]','regaddress[flat]'), array(25,25,25), array(5,4,4), array('A',0,0) );

    $form->remark('Контактные данные');
    $form->phoneInput('Домашний телефон',         'homephone', array(40,70), array(5,10), 0 );
    $form->phoneInput('Мобильный телефон',        'mobile',    array(40,70), array(3,7), 1 );
    $form->textInput('e-mail',           'e-mail',      200, 90, 'OE' );

    $form->remark('Сведения об образовании');

    $catalog = new Catalog($msl);
    $bval = $catalog->getAvailableByRegion($region_id, "%abbr2% - %name% (%base%) - %qualify%", 0, 0);
    unset($catalog);

    $form->selectInput( 'Образовательная программа', 'catalog', $bval, 0, 1);

    echo "<tr><TD colspan=2  style=\"border:0px; margin: 0; padding: 0;\">";
    echo "<DIV id=\"ege_fields\" style=\"border:0px; margin: 0; padding: 0 0 0 0;\">";

    $_POST['width']=180;
    $_POST['catalog']=@array_shift(array_keys($bval));
    require('get.php'); 

    echo "</DIV></TD></tr>";

    //$form->textInput('Количество досдач (неизвестно = 0)',           'pay',      20, 3, 0, 0 );
    $form->hidden('pay', 0);
    $kval = $msl->getArrayById("SELECT id, name FROM reg_education", 'id', 'name');
    $form->selectInput(   'Тип образовательного учреждения', 'edu_base', $kval, 0, 1);

    $bdoc = $msl->getarrayById("SELECT id,name FROM `reg_edu_doc` WHERE `group`=1",'id','name');
    $form->selectInput(  'Тип документа об образовании', 'edu_doc', $bdoc, 0, 1);

    $form->textInput(array('Серия','№'),  array('edu_serie','edu_number'), array(45,75), array(10,14), array('A','N') );
    $form->dateInput( 'Дата выдачи',           'edu_date',    1990, date('Y'), 'D' );

    $form->textInput('Образовательная организация', 'edu_institution', 250, 120, 'O' );
    $form->textInput('Населенный пункт',            'edu_city',        250, 120, 'O' );
    $form->textInput('Специальность, профессия',    'edu_specialty',   250, 120, 'O' );

    $form->radioInput(   'Иностранный язык',   'language', $msl->getArrayById("SELECT id, name FROM reg_flang",'id','name'), 1, 1);
    $form->radioInput(   'Высшее образование', 'highedu',  array('0'=>'впервые','1'=>'не впервые'), 0, 1);
    
    /*
    * Ввод номера договора в системе МАМИ для региона ЦКТ
    */
 
    if ($region_id == 3) {
        $form->textInput('Номер договора в МАМИ',           'num',      50, 4, 'ON' );
    }
    echo "</TBODY></TABLE></DIV>\n\n"; 
    unset($form);

    break;

case "editapplicant":
    echo "<script language=\"javascript\">$(function() {
        $(\"#formular\").validationEngine({failure: function() {setTimeout(\"$.validationEngine.closePrompt('.formError',true)\", 10000)}}); 
    });
</script>";
    echo "<H1 class=\"title\">Редактирование данных абитуриента</H1><DIV id=\"output\"></DIV>";

    echo "<DIV id=\"myaccordion\">";

    $form = new FormFields('index.php','formular', 180, 0, "Завершить редактирование");
    $rpval = $msl->getarray("SELECT rf FROM `partner_regions` WHERE id='".$region_id."'");
    $apval = $msl->getarray("SELECT * FROM `partner_applicant` WHERE id='".$_REQUEST['id']."'");

    new FabricApplicant($appl, $msl, "r".$_REQUEST['id']);

    echo "<DIV><TABLE style=\"display: block;\"><TBODY style=\"border: none;\">"; 
    $form->hidden('region', $region_id);
    $form->hidden('id', $_REQUEST['id']);
    $form->hidden('act', 'applicantupdate');

    $form->remark('Идентификационный номер '.$_REQUEST['id']);
    $form->textInput('Фамилия',          'surname',  200, 60, 'K', $apval['surname'] );
    $form->textInput('Имя',              'name',     200, 60, 'K', $apval['name'] );
    $form->textInput('Отчество',         'second_name', 200, 60, 0, $apval['second_name'] );

    $form->radioInput(   'Пол',              'sex',         array('M'=>'Мужской','F'=>'Женский'), $apval['sex'], 1 );
    $form->dateInput( 'Дата рождения',    'birthday',        1950, date('Y')-16, 'D', 0, '1980', date('d.m.Y', strtotime($apval['birthday'])) );
    $form->textInput('Гражданство',  'citizenry',   300, 60, 'K', $apval['citizenry']);

    $form->remark('Паспортные данные');
    $form->hidden('doc_type', 1);
    $form->textInput(array('Серия','Номер'), array('doc_serie','doc_number'),    array(45,70), array(4,6), array('N','N'), array($apval['doc_serie'],$apval['doc_number']) );
    $form->textInput('Кем выдан',         'doc_issued',  200, 200, 'A', $apval['doc_issued'] );
    $form->textInput('Код подразделения', 'doc_code',    100, 8, 'kodp', $apval['doc_code'] );
    $form->dateInput( 'Дата выдачи',           'doc_date',    1990, date('Y'), 'D', 0, '1980', date('d.m.Y', strtotime($apval['doc_date']))  );
    $form->textInput('Место рождения',    'birthplace',  200, 100, 'A', $apval['birthplace'] );

    $addr = $appl->getAddress();
    
    $form->remark('Адрес места жительства');
    $form->textInput('Почтовый индекс',  'homeaddress[index]', 100, 6, 'ON', $addr[1]['index'] );

    $aspec = $msl->getArrayById("SELECT id,CONCAT(id,' - ',name) as name FROM `reg_rf_subject` ORDER BY id ASC",'id','name');
    $form->selectInput(  'Субъект РФ', 'homeaddress[region]', $aspec, $addr[1]['region'], 1);

    $form->textInput('Населенный пункт',  'homeaddress[city]', 200, 50, 1, $addr[1]['city'] );
    $form->textInput('Улица (квартал)',  'homeaddress[street]', 200, 60, 0, $addr[1]['street'] );
    $form->textInput(array('Дом','корпус','квартира'),  array('homeaddress[home]','homeaddress[building]','homeaddress[flat]'), array(25,25,25), array(5,4,4), array('A',0,0), array($addr[1]['home'],$addr[1]['building'],$addr[1]['flat']) );

    $form->remark('Адрес регистрации');
    $form->textInput('Почтовый индекс',  'regaddress[index]', 100, 6, 'ON', $addr[0]['index'] );
    $form->selectInput(  'Субъект РФ', 'regaddress[region]', $aspec, $addr[0]['region'], 1);
    $form->textInput('Населенный пункт',  'regaddress[city]', 200, 50, 1, $addr[0]['city'] );
    $form->textInput('Улица (квартал)',  'regaddress[street]', 200, 60, 0, $addr[0]['street'] );
    $form->textInput(array('Дом','корпус','квартира'),  array('regaddress[home]','regaddress[building]','regaddress[flat]'), array(25,25,25), array(5,4,4), array('A',0,0), array($addr[0]['home'],$addr[0]['building'],$addr[0]['flat']) );
   
    $form->remark('Контактные данные');
    $form->phoneInput('Домашний телефон',         'homephone', array(40,70), array(5,10), 0, array($apval['homephone_code'],$apval['homephone']) );
    $form->phoneInput('Мобильный телефон',        'mobile',    array(40,70), array(3,7), 1, array($apval['mobile_code'],$apval['mobile']) );
    $form->textInput('e-mail',           'e-mail',      200, 90, 'OE', $apval['e-mail'] );

    $form->remark('Сведения об образовании');

    $catalog = new Catalog($msl);
    $bval = $catalog->getAvailableByRegion($region_id, "%name% (%base%) - %qualify%");
    unset($catalog);
    $form->selectInput(  'Образовательная программа', 'catalog', $bval, $apval['catalog'], 1);

    echo "<tr><TD colspan=2  style=\"border:0px; margin: 0; padding: 0;\">";
    echo "<DIV id=\"ege_fields\" style=\"border:0px solid #d3d3d3; margin: 0; padding: 0 0 0 0;\">";

    $_POST['width']=180;
    $_POST['catalog']=$apval['catalog'];
    $_POST['semestr']=$apval['semestr'];
    $_POST['applicant_id']=$_REQUEST['id'];
    require('get.php'); 

    echo "</DIV></TD></tr>";

    //$form->textInput('Количество досдач (неизвестно = 0)',           'pay',      20, 3, 0, $apval['pay'] );
    $form->hidden('pay', 0);

    $kval = $msl->getArrayById("SELECT id, name FROM reg_education", 'id', 'name');
    $form->selectInput(   'Тип образовательного учреждения', 'edu_base', $kval, $apval['edu_base'], 1);

    $bdoc = $msl->getarrayById("SELECT id,name FROM `reg_edu_doc` WHERE `group`=1",'id','name');
    $form->selectInput(  'Тип документа об образовании', 'edu_doc', $bdoc, $apval['edu_doc'], 1);

    $form->textInput(array('Серия','№'),  array('edu_serie','edu_number'), array(45,65), array(10,10), array('A','N'), array($apval['edu_serie'],$apval['edu_number']) );
    $form->dateInput( 'Дата выдачи',           'edu_date',    1990, date('Y'), 'D', 0, 0, date('d.m.Y', strtotime($apval['edu_date'])));

    $form->textInput('Образовательная организация', 'edu_institution', 250, 120, 'O', $apval['edu_institution'] );
    $form->textInput('Населенный пункт',            'edu_city',        250, 120, 'O', $apval['edu_city'] );
    $form->textInput('Специальность, профессия',           'edu_specialty',      250, 120, 'O', $apval['edu_specialty'] );
    
    $form->radioInput(   'Иностранный язык',   'language', $msl->getArrayById("SELECT id, name FROM reg_flang",'id','name'), $apval['language'], 1);
    $form->radioInput(   'Высшее образование', 'highedu',  array('0'=>'впервые','1'=>'не впервые'), $apval['highedu'], 1);

    /*
    * Ввод номера договора в системе МАМИ для региона ЦКТ
    */
 
    if ($region_id == 3) {
        $form->textInput('Номер договора в МАМИ',           'num',      50, 4, 'ON', $apval['num'] );
    }
    echo "</TBODY></TABLE></DIV>\n\n"; 
    unset($form);

    break;

case "addstudentinsert":
    echo "<H1 class=\"title\">Добавление абитуриента</H1><DIV id=\"output\"></DIV>";

    echo "<P>Результат добавления:";
    require_once("insert.php");
    $ins = new Insertion();
    $id = $ins->newApplicant("", $_POST);
    
    if ($id > 0) {
        echo "абитуриент ".$_POST['surname']." ".$_POST['name']." ".$_POST['second_name']." успешно добавлен.</P>\n\n";
    } else {
        echo "произошла ошибка. Попробуйте обновить страницу.</P>\n\n";
    break;
    }
    unset($ins);

    echo "<DIV id=\"myaccordion\">\n";   

    echo "<h3>Документы для подписания</h3>";
    echo "<DIV><TABLE style=\"display: block;\"><TBODY style=\"border: none;\">";
    
    new FabricApplicant($appl, $msl, "r".$id);
    $appl->echoDocs();
    echo "<tr><TD>Для просмотра документов вам потребуется <A href=\"http://get.adobe.com/reader/\">Adobe&copy; Reader</A>.\n</TD></tr>";
    echo "</TBODY></TABLE></DIV>\n\n"; 
    unset($appl);
    break;

case "applicantupdate":
    echo "<H1 class=\"title\">Редактирование абитуриента</H1><DIV id=\"output\"></DIV>";
    echo "<P>Результат сохранения: ";
    require_once("insert.php");
    $ins = new Insertion();
    $id = $ins->updateApplicant("", $_POST);
    
    if ($id > 0) {
        $ins->insertEge("", $_POST['ege'], $_POST['id']);
        echo "данные абитуриента ".$_POST['surname']." ".$_POST['name']." ".$_POST['second_name']." успешно изменены.</P>\n\n";
    } else {
        echo "произошла ошибка. Попробуйте обновить страницу.</P>\n\n";
    break;
    }
    unset($ins);

    echo "<DIV id=\"myaccordion\">\n";   

    echo "<h3>Документы для подписания</h3>";
    echo "<DIV><TABLE style=\"display: block;\"><TBODY style=\"border: none;\">";
        
    new FabricApplicant($appl, $msl, "r".$_POST['id']);
    $appl->printDocs();
    echo "<tr><TD>Для просмотра документов вам потребуется <A href=\"http://get.adobe.com/reader/\">Adobe&copy; Reader</A>.\n</TD></tr>";
    echo "</TBODY></TABLE></DIV>\n\n"; 
    unset($appl);
    break;

case "showstudentdocuments":
    new FabricApplicant($appl, $msl, "r".$_REQUEST['id']);
    $mdl = new Moodle($msl);

    echo "<H1 class=\"title\">Работа с абитуриентом \"".$appl->surname." ".$appl->name." ".$appl->second_name."\"</H1><DIV id=\"output\"></DIV>";
    echo "<DIV id=\"myaccordion\">\n";   
    echo "<BR>";
    echo "<h4>Документы для подписания</h4>";
    echo "<DIV><TABLE style=\"display: block;\"><TBODY style=\"border: none;\">";
        
    $appl->printDocs();

    if ($region_id != 3) {
        echo "<tr><TD>Для просмотра документов вам потребуется <A href=\"http://get.adobe.com/reader/\">Adobe&copy; Reader</A>.\n</TD></tr>";
    } else {
        $email = $appl->getInfo('email','num'); 

        if ($email['e-mail'] != '') {
            echo "<tr><TD>\n";

            if ($mdl->searchUser($email['e-mail']) > 0) {
                echo "<b>Система интернет-обучения Moodle: пользователь уже создан.</b><br>";
            } else {
                echo "<script src=\"http://code.jquery.com/jquery-1.9.1.js\"></script>
                      <script language=\"javascript\">
                          function makeUser() {
                            $.ajax({type: \"POST\", url: \"index.php\",
                            data: 'act=createmoodleuser&id=r".$_REQUEST['id']."&num='+$( \"#num\" ).val(),
                            success: function(msg){alert(msg);}
                          })
                      }</script>\n";
                echo "<br>Номер личного дела в БД <INPUT type=\"text\" maxlength=4 value=\"".$email['num']."\" id=\"num\" style=\"width: 40px;\"> <A onclick=\"makeUser();\">Создать пользователя в Системе интернет-обучения</A><BR>\n";  
            }
            echo "</TD></tr>";
        }
    }

    echo "</TBODY></TABLE></DIV>\n\n"; 
    break;

case "deletestudent":
    new FabricApplicant($appl, $msl, "r".$_REQUEST['id']);
    
    echo "<H1 class=\"title\">Удаление абитуриента \"".$appl->surname." ".$appl->name." ".$appl->second_name."\"</H1><DIV id=\"output\"></DIV>";
    unset($appl);
    echo "<P>Результат удаления: ";

    if ($msl->deleteArray('admission`.`partner_applicant', array('id'=>$_REQUEST['id'],'region'=>$region_id)) && 
        $msl->deleteArray('admission`.`partner_applicant_address', array('applicant_id'=>$_REQUEST['id'])) && 
        $msl->deleteArray('admission`.`partner_applicant_scores', array('applicant_id'=>$_REQUEST['id']))) {
        echo "успешно";
    } else {
        echo "произошла ошибка. Попробуйте обновить страницу";
    } 
    echo ".</P>\n\n";
    break;

case "listapplicant":
    echo "<H1 class=\"title\">Список абитуриентов</H1><DIV id=\"output\"></DIV>";
    echo "<DIV id=\"myaccordion\">\n";   
    $rval = $msl->getarray("SELECT id,CONCAT(surname,' ',name,' ',second_name) as fio FROM partner_applicant WHERE region = '".$region_id."' ORDER by id DESC", 1);
    echo "<DIV><TABLE style=\"display: block;\"><TBODY style=\"border: none;\">"; 
    
    if ($rval == 0) {
        echo "<tr><TD><H4>У вас нет абитуриентов.</H4></TD></tr>";
    } else {
        foreach($rval as $k) {
            echo "<tr><TD><A href=\"?act=showstudentdocuments&id=".$k['id']."\">".$k['fio']."</A>&nbsp;&nbsp;
               <A href=\"?act=deletestudent&id=".$k['id']."\"><IMG src=\"../images/icons/Delete.png\" style=\"width: 16px;\" title=\"Удалить\"></A>&nbsp;
           <A href=\"?act=editapplicant&id=".$k['id']."\"><IMG src=\"../images/icons/Edit.png\" style=\"width: 16px;\" title=\"Редактировать\"></A></TD></tr>";
        }
    }

    
    echo "</TBODY></TABLE></DIV>\n\n"; 
    break;

case "basestudent":
    if ($rval['approved'] == 0) {    
        echo "<P>Продолжение работы невозможно без подтверждения Вами сведений о региональном партнере. Для подтверждения <A href=\"?act=card\">проверьте свои данные</A>.</P>\n\n";
        break;
    }
    
    echo "<SCRIPT type=\"text/javascript\" src=\"//cdn.datatables.net/1.10.2/js/jquery.dataTables.min.js\"></SCRIPT>
<SCRIPT type=\"text/javascript\" src=\"http://code.jquery.com/jquery-1.10.2.min.js\"></script>
    <script language=\"javascript\">$(function() {
        $('#example').dataTable();
    })</script>";
    echo "<H1 class=\"title\">Список студентов из базы данных</H1><DIV id=\"output\"></DIV>";
    echo "<DIV id=\"myaccordion\"><BR>\n"; 

    require_once '../class/mssql.class.php';
    $mssql = new dMssql();
    $rval = $mssql->getarray("SELECT id,surname,name,second_name FROM dbo.student WHERE region = '".$region_id."' ORDER by id DESC", 1);
    
    echo "<DIV><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"display\" id=\"example\">";
    echo "<thead><tr>
                  <th>Номер <INPUT type=\"text\" style=\"width: 55px;\" name=\"search_id\" value=\"поиск\" class=\"search_init\"></th>
          <th>Фамилия <INPUT type=\"text\" style=\"width: 125px;\" name=\"search_surname\" value=\"поиск\" class=\"search_init\"></th>
          <th>Имя</th>
          <th>Отчество</th>
          </tr>
       </thead><tbody>";

    if ($rval == 0) {
        echo "<tr><TD><H4>У вас нет студентов.</H4></TD></tr>";
    } else {
        foreach($rval as $k) {
            echo "<tr>
                  <TD>".$k['id']."</TD><TD>".$k['surname']."</TD>
                  <TD>".$k['name']."</TD><TD>".$k['second_name']."</TD></tr>\n"; //  onclick=\"selectStudent(".$k['id']."); return false;\" 
        }
    }

    echo "</TBODY></TABLE></DIV>\n\n";
    echo "<DIV id=\"dialog-message\" style=\"width: 800px;\"></DIV>\n";
    break;

case "receipt":
    if ($rval['approved'] == 0) {    
        echo "<P>Продолжение работы невозможно без подтверждения Вами сведений о региональном партнере. Для подтверждения <A href=\"?act=card\">проверьте свои данные</A>.</P>\n\n";
        break;
    }

    require_once '../class/price.class.php';
    echo "<H1 class=\"title\">Печать квитанции</H1><DIV id=\"output\"></DIV>";
    echo "<DIV id=\"myaccordion\">\n";   

    $form = new FormFields('../receipt/kvit.php','formular', 180, 0, "Распечатать квитанцию");

    echo "<DIV><TABLE style=\"display: block;\"><TBODY style=\"border: none;\">"; 
    //$form->hidden('region_id', $region_id);
    $form->selectInput(  'Организация', 'region_id', array(1 => 'ЦКТ - Россия', 3 => 'ЦКТ - Москва', 2 => 'ИИТ - Россия', 4 => 'ИИТ - Москва'), 0, 1);

    echo "<script type=\"text/javascript\">
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

    $rval = $msl->getarrayById("SELECT id,text FROM `receipt_purpose`", 'id', 'text');
    $form->selectInput(  'Назначение платежа', 'purpose', $rval, 0, 1);

    $catalog = new Catalog($msl);
    $rval = $catalog->getAvailableByRegion($region_id);
    unset($catalog);

    $form->selectInput(  'Образовательная программа', 'catalog', $rval, 0, 1);

    $prc = new Price($msl);
    $sessions = $prc->getSessions();
    $appDate = $prc->getApplicantDate();
    unset($prc);

    $form->selectInput(  'Назначенная сессия', 'date', $sessions, 0, 1);
    $form->selectInput(  'Зачисление студента было', 'tpapplicant', $appDate, 0, 1);

    $form->textInput('Номер договора',  'dn',      50, 5, 'O' );
    $form->textInput('Семестр',         's',       20, 2, 'O' );
    
    $form->textInput('ФИО плательщика',   'fio',     450, 100, 'O' );
    $form->textInput('Адрес плательщика', 'address', 450, 100, 'O' );

    $form->textInput('Количество',        'count', 20, 2, 'O', 1 );
    unset($form);

    echo "</TBODY></TABLE></DIV>\n\n";     
    break;

case "verified":
    $msl->insertArray('partner_agreement', array('region'=>$region_id, 'ip'=>sprintf('%u', ip2long($_SERVER['REMOTE_ADDR'])), 'remarks'=>$_POST['remarks']));

case "card":
    $rpval = $msl->getarray("SELECT a.*, b.name_rp as pos, c.name_rp as doc FROM `partner_regions` a 
                             LEFT JOIN `partner_position` b ON a.gposition=b.id 
                             LEFT JOIN `partner_organizational_documents` c ON a.orgdoc=c.id WHERE a.id = '".$region_id."'");

    echo "<H1 class=\"title\">Карточка регионального партнера</H1>";
    echo "<DIV id=\"myaccordion\">\n";

    echo "<DIV><TABLE style=\"display: block;\"><TBODY style=\"border: none;\">"; 

    $agreed = $msl->getarray("SELECT date, used FROM `partner_agreement` WHERE region='".$region_id."'");
    if ($agreed == 0) {      
        echo "<tr><TD colspan=2>Проверьте указанные ниже сведения:</TD></tr>";
    }
    echo "<tr><TD style=\"width: 120px;\">Организация:</TD><TD>".$rpval['firm'].".</TD></tr>";
    echo "<tr><TD style=\"width: 120px;\">Полное наименование:</TD><TD>".$rpval['longfirm'].".</TD></tr>";
    echo "<tr><TD>В лице:</TD><TD>".(($rpval['pos'] == '') ? "<FONT color=red><B>укажите должность в замечаниях</B></FONT>": $rpval['pos'])." ".$rpval['name_rp'].".</TD></tr>";
    echo "<tr><TD>На основании:</TD><TD>".$rpval['doc'].".</TD></tr>";
    echo "<tr><TD>Юридический адрес:</TD><TD>".$rpval['legaladdress'].".</TD></tr>";
    echo "<tr><TD>Фактический адрес:</TD><TD>".$rpval['physicaladdress'].".</TD></tr>";
    echo "<tr><TD>БИК:</TD><TD>".$rpval['bik'].".</TD></tr>";
    echo "<tr><TD>Кор.счет:</TD><TD>".$rpval['ks'].".</TD></tr>";
    echo "<tr><TD>Расчетный счет:</TD><TD>".$rpval['rs'].".</TD></tr>";
    echo "<tr><TD>Банк:</TD><TD>".$rpval['bank'].".</TD></tr>";
    echo "<tr><TD>ИНН/КПП:</TD><TD>".$rpval['inn']."/".$rpval['kpp'].".</TD></tr>";
    
    $contracts = $msl->getarray("SELECT * FROM `partner_contract` WHERE id='".$region_id."' ORDER BY agent ASC LIMIT 2;", 1);
    if ($contracts != 0) {
        foreach ($contracts as $v) {
            echo "<tr><td>Договор с ";
            if ($v['agent'] == 2) {
                echo "ИИТ";
            } else {
                echo "ЦКТ";
            }
            echo ":</td><td>".$v['num']." от ".date('d.m.Y', strtotime($v['date'])).".</td></tr>";
        }
    }
    
    echo "<tr><TD>Электронная почта:</TD><TD><A href=\"mailto:".$rpval['e-mail']."\">".$rpval['e-mail']."</A>.</TD></tr>";

    if ($agreed == 0) {    
        echo "<FORM method=\"POST\"><tr><TD style=\"vertical-align: top;\">Замечания:</TD><TD><TEXTAREA name=\"remarks\" WRAP=\"virtual\" COLS=\"90\" ROWS=\"3\"></TEXTAREA></TD></tr>";
        echo "<INPUT type=\"hidden\" name=\"act\" value=\"verified\">";
        echo "<tr><TD colspan=2 style=\"text-align: center;\"><INPUT type=submit value=\"Отправить\"></TD></tr></FORM>";
    } else {
        echo "<tr><TD colspan=2>Данные подтверждены ".date( 'd.m.Y в h:i', strtotime($agreed['date'])).".";
        if ($agreed['used'] == 1) {
            echo " Замечания выполнены. В случае изменения Ваших данных, просьба незамедлительно связаться с нами по электронной почте.";
        }
        echo "</TD></tr>\n";
    }

    echo "</TBODY></TABLE></DIV>\n\n";    
    break;

default:
    echo "<H1 class=\"title\">Главная страница</H1><DIV id=\"output\"></DIV>";

    echo "<P></P>\n\n"; //Пожалуйста заполните следующие поля (поля отмеченные * обязательны для заполнения):</P>\n\n";
    echo "<DIV id=\"myaccordion\">";

    echo "<h3>Добро пожаловать в Личный кабинет регионального партнера</h3>";

//    echo "<P>Данная система предназначена для работы регионального партнераформирования комплекта документов абитуриента, поступающего через Центр Компьютерных Технологий в \"Московский государственный машиностроительный университет (МАМИ)\".</P>";
//    echo "<h3>Пароль для базы данных</h3>";
//    echo "<P>При установке базы данных требуется Ваш пароль: <B>".$rval['base_password']."</B></P>";

    echo "<h3>Порядок работы с абитуриентами</h3>";
    echo "<P>Для формирования договоров на нового абитуриента выберите в меню слева пункт \"Добавить абитуриента\". После заполнения всех необходимых полей (поле количество досдач оставить 0, если количество платных досдач неизвестно. При этом будет формироваться дополнительное соглашение и квитанция для дополнительного соглашения без указания сумм), нажмите кнопку \"Добавить абитуриента\". После добавления Вы сможете распечатать или сохранить весь необходимый комплект документов.</P>";
    echo "<P>Также, имеется возможность просмотреть список уже оформленных абитуриентов и их документов. Для этого в меню слева выберите пункт \"Список абитуриентов\". При выборе конкретного абитуриента Вы получите список его документов.</P>";
    
/*    echo "<h3>Квитанция на оплату</h3>";
    echo "<P>Для формирования квитанции необходимо выбрать в меню слева пункт \"Распечатать квитанцию\". Выберите организацию (ИИТ или ЦКТ), назначение платежа, образовательную программу. Поля номер договора, семестр, ФИО плательщика, адрес плательщика являются необязательными. Поле количество требуется для указания количества пересдач или досдач, в иных случаях указывайте 1. После заполнения всех необходимых полей, нажмите кнопку \"Распечатать квитанцию\". Полученный pdf-документ, содержащий квитанцию со всеми реквизитами, можно открыть или сохранить.</P>";
*/

    echo "<h3>Сведения по студентам</h3>";
    echo "<P>Вы можете получить сведения по студентам, их ведомость успеваемости или квитанцию на оплату в режиме реального времени. Для этого необходимо выбрать в меню слева пункт \"Список студентов\", найти необходимого студента и нажать на него. В появившемся окне Вы можете вывести ведомость успеваемости или квитанцию в формате HTML или PDF. Формат HTML является гораздо более \"компактным\" по размеру полученного файла, но может иметь проблемы с выводом на печать (зависит от настроек браузера, принтера и тп).</P>";
//    echo "<DIV><TABLE style=\"display: block;\"><TBODY style=\"border: none;\">"; 
//   echo "</TBODY></TABLE></DIV>\n\n"; 

}
unset($msl);


echo '<P>Если у Вас появились вопросы, свяжитесь с нашими сотрудниками по телефонам: +7 (499) 127-7496, +7 (499) 127-7453 или <A href="mailto:iit@ins-iit.ru">по электронной почте</A>.</p></div></div>';
                    
?>

<div id="footer">© 2009-<?php echo date('Y'); ?>, ins-iit.ru Team<div id="block-system-0" class="clear-block block block-system">
</div>
</div>
      </div></div></div></div> <!-- /.left-corner, /.right-corner, /#squeeze, /#center -->
    </div>
  </div>
</body></html>
