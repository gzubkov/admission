<?php
require_once('../../conf.php');
require_once('../class/mysql.class.php');
require_once('../class/forms.class.php');
require_once('../class/catalog.class.php');
require_once('../class/documents.class.php');
require_once('../class/moodle.class.php');

$msl = new dMysql();

if (isset($_SESSION['rights'])) {
    if ($_SESSION['rights'] == 'admin' && $_SESSION['md_rights'] == md5($CFG_salted.$_SESSION['rights'])) {
        if (isset($_REQUEST['region'])) {
            $_SESSION['joomlaregion'] = $_REQUEST['region'];
        } 
    }
}

if ($_REQUEST['act'] == 'createmoodleuser') {
    new FabricApplicant($appl, $msl, $_REQUEST['id']);
    $mdl = new Moodle($msl);
    
    $addr = end($appl->getAddress());	
    $rval = $appl->getInfo('email');
    $num  = $_REQUEST['num'];

    $id = $mdl->createUser($appl->name, $appl->surname, $rval['e-mail'], '7428bd7aa76b3ae591ada0f46a2b22e8', $addr['city'], $num);
    if ($id == 0) {
        echo "error";
	exit(0);
    }
 
    $appl->setNum($num);    
    
    $cat = new Catalog(&$msl);
    $spc = $cat->getInfo($appl->catalog);
    unset($cat);

    $to      = $appl->surname." ".$appl->name." ".$appl->second_name."<".$rval['e-mail'].">, <gzubkov@gmail.com>";
    $subject = "Интернет-обучение";
    $message = "<html><body>
        <p>Уважаем".$appl->inflection().", ".$appl->name." ".$appl->second_name."!</p>
        <p>Вы зачислены на ".$spc['type']." «".$spc['name']."» системы электронного обучения (<A href=\"http://moodle.ins-iit.ru/\">http://moodle.ins-iit.ru/</A>)</p>
        <p>Для входа в систему используйте адрес электронной почты как логин и временный пароль \"123456\".</p>
 	<P>По всем возникающим вопросам обращайтесь +7 (499) 1277453 доб.20, Татьяна Викторовна.</P>
	<P>С уважением, Электронная приемная комиссия</P></body></html>";

    $headers  = "Content-type: text/html; charset=utf-8 \r\n From: Система интернет-обучения <iit@ins-iit.ru>\r\n";
   
    if (mail($to, $subject, $message, $headers) && $id > 0) {
	print "ok";
    } else {
        print "error";
    }
    exit(0);
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html class="js" dir="ltr" xml:lang="en" xmlns="http://www.w3.org/1999/xhtml" lang="en"><head>

<!--<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>-->
 
  
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Личный кабинет регионального партнера</title>

<link type="text/css" rel="stylesheet" media="all" href="../images/defaults.css">
<link type="text/css" rel="stylesheet" media="all" href="../images/system.css">
<link type="text/css" rel="stylesheet" media="all" href="../images/style.css">
<!--<link type="text/css" rel="stylesheet" media="all" href="../css/custom-theme/jquery-ui-1.8.custom.css">-->	
<link type="text/css" rel="stylesheet" media="all" href="../css/smoothness/jquery-ui-1.8.7.custom.css">	
<link type="text/css" rel="stylesheet" media="all" href="http://datatables.net/release-datatables/media/css/demo_table_jui.css">	

<!-- Validation -->
<link rel="stylesheet" href="../css/validationEngine.jquery.css" type="text/css" media="screen" title="no title" charset="utf-8" />

<SCRIPT type="text/javascript" src="../js/jquery-1.4.4.min.js"></script>
<!-- jQuery UI -->
<SCRIPT type="text/javascript" src="../js/jquery-ui-1.8.7.custom.min.js"></script>
<!--<SCRIPT type="text/javascript" src="../js/jquery.ui.datepicker.js"></script>
-->
<SCRIPT type="text/javascript" src="../js/jquery.ui.datepicker-ru.js"></script>
<SCRIPT type="text/javascript" src="../js/jquery.form.js"></SCRIPT>
<SCRIPT type="text/javascript" src="../js/jquery.blockUI.js"></SCRIPT>
<!-- <SCRIPT type="text/javascript" src="../js/jquery.dataTables.js"></SCRIPT> -->
<SCRIPT type="text/javascript" src="http://datatables.net/download/build/jquery.dataTables.min.js"></SCRIPT>
<!-- Script validation -->
<script type="text/javascript" src="../js/jquery.validationEngine.js" type="text/javascript"></script>
<script type="text/javascript" src="../js/jquery.validationEngine-ru.js"></script>

<script type="text/javascript">
	$(function() {
		     $("input:submit, input:button").button();
                     $.datepicker.setDefaults({changeMonth: true, changeYear: true});
	});

$(document).ready(function() {
    var oTable;
    var asInitVals = new Array();

    $("#dialog-message").dialog(
     {autoOpen: false,
      modal: true,
      width: 680
   });
		     
   $("#formular").validationEngine({failure: function() {setTimeout("$.validationEngine.closePrompt('.formError',true)", 10000)}}); 

   $("#catalog").change(function(){
      $("#catalog").attr("disabled","disabled"); 
      $.ajax({url: 'get.php', type: 'POST', datatype:'html', data: 'width=180&catalog=' + $('#catalog').val(), beforeSend: function(){}, 
              success: function(msg){
                 $('#ege_fields').html(msg); //.replace(/\s+/, '')
              }})
      $("#catalog").attr("disabled",""); 
   }) 

   oTable = $('#example').dataTable( {
      "bJQueryUI": true,
"sDom": 'rtip<"clear">',
"iDisplayLength": 20,
//      "bStateSave": true,
      "sPaginationType": "full_numbers",
      "aoColumns": [ 
         null, null, 
	 {"bSearchable": false,
  	  "bSortable": false},
	 {"bSearchable": false,
  	  "bSortable": false}],
      "oLanguage": {
	"sProcessing":   "Подождите...",
	"sLengthMenu":   "", //Показать _MENU_ записей",
	"sZeroRecords":  "Записи отсутствуют.",
	"sInfo":         "Записи с _START_ до _END_ из _TOTAL_",
	"sInfoEmpty":    "Записи по указанному фильтру отсутствуют",
	"sInfoFiltered": "(выбрано из _MAX_ записей)",
	"sInfoPostFix":  "",
	"sSearch":       "Поиск:",
	"sUrl":          "",
	"oPaginate": {
		"sFirst": " << ",
		"sPrevious": " < ",
		"sNext": " > ",
		"sLast": " >> "
	}, 
	"sSearch": "Поиск по всем полям:"},
"aLengthMenu": [[20, 50, -1], [20, 50, "All"]]

				} );

$("thead input").keyup( function () {
			/* Filter on the column (the index) of this element */
			oTable.fnFilter( this.value, $("thead input").index(this) );
		} );
		
               $("thead input").each( function (i) {
			asInitVals[i] = this.value;
		} );
		
		$("thead input").focus( function () {
			if ( this.className == "search_init" )
			{
				this.className = "";
				this.value = "";
			}
		} );
		
		$("thead input").blur( function (i) {
			if ( this.value == "" )
			{
				this.className = "search_init";
				this.value = asInitVals[$("thead input").index(this)];
			}
		} );
		

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

function unloginA() {
   $.ajax({url: 'login.php', type: 'POST', data: 'act=exit',
           success: function(msg){
	      msg = msg.replace(/\s+/, '');
	      switch (msg) {
	         case "ok":
		    window.location.reload();
		    break;
		 default:
		    alert('Ошибка при выходе!');
	      }
	   }});     
}
</script>

</head>
<body class="sidebar-left">

<?php
if (!isset($_SESSION['joomlaregion'])) {
    if ($_SERVER['REMOTE_ADDR'] == $CFG_trustedip) {
        print "<div style=\"border: 1px solid #d3d3d3; width: 250px; height: 140px; background-color: #ffffff; margin:20px auto 0pt;\"><form id=\"login\" action=\"\">\n";
        print "<table style=\"border: none;\"><tbody style=\"border: none;\">";
        print "<tr><td colspan=\"2\" style=\"text-align: center;\"><b>Вход в систему</b></td></tr>";
        print "<tr><td style=\"width: 70px;\">Логин:</td>";
        print "<td><input type=\"text\" name=\"login\" />.</td></tr>";
        print "<tr><td style=\"width: 70px;\">Пароль:</td>";
        print "<td><input type=\"password\" name=\"password\" />.</td></tr>";
        print "<tr><td colspan=\"2\" style=\"text-align: center;\"><input type=\"submit\" value=\"Войти\" onclick=\"javascript: loginA(); return false;\" /></td></tr>";
        print "</tbody></table>";   
        print "</form></div>";
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
    print "<div class=\"form-item\">".$rval['firm']."</div>";
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
 
if ($region_id != 3) {
    print "<li class=\"collapsed last\"><a href=\"?act=basestudent\">Список студентов</a></li>";
}
?> 
   <li class="collapsed last"><a href="?act=card">Мои данные</a></li> 
<!--  <li class="collapsed last"><a href="?act=receipt">Распечатать квитанцию</a></li> -->
  <li class="collapsed last"><a href="?">Вернуться на главную</a></li>
  </ul></div>
</div>
        </div>
      
      <div id="center"><div id="squeeze"><div class="right-corner"><div class="left-corner">
                                                                                          <div class="clear-block">
            <div id="first-time">

<?php

if (!isset($_REQUEST['act'])) $_REQUEST['act'] = '';

switch($_REQUEST['act']) {
case "addapplicant":
    print "<H1 class=\"title\">Добавление абитуриента</H1><DIV id=\"output\"></DIV>";

    if ($rval['approved'] == 0) {    
        print "<P>Продолжение работы невозможно без подтверждения Вами сведений о региональном партнере. Для подтверждения <A href=\"?act=card\">проверьте свои данные</A>.</P>\n\n";
        break;
    }	    
    print "<P>Пожалуйста заполните следующие поля (поля отмеченные * обязательны для заполнения):</P>\n\n";
    print "<DIV id=\"myaccordion\">";

    $form = new FormFields('index.php','formular', 180, 0, "Добавить абитуриента");
    $rpval = $msl->getarray("SELECT rf FROM `partner_regions` WHERE id='".$region_id."'");

    print "<TABLE style=\"display: block;\"><TBODY style=\"border: none;\">"; 
    $form->hidden('region', $region_id);
    $form->hidden('act', 'addstudentinsert');

    $form->tdBox( 'text', 'Фамилия',          'surname',  200, 60, 'K' ); 
    $form->tdBox( 'text', 'Имя',              'name',     200, 60, 'K' ); 
    $form->tdBox( 'text', 'Отчество',         'second_name', 200, 60, 0 ); 

    $form->tdRadio(   'Пол',              'sex',         array('M'=>'Мужской','F'=>'Женский'), 'M', 1);
    $form->tdDateBox( 'Дата рождения',    'birthday',        1950, date('Y')-16, 'D', 75, '1980' );
    $form->tdRadio(   'Гражданство',      'citizenry',   array('Российская Федерация'=>'Российская Федерация','other'=>'Другое'), null, 1);

    $form->tdBox( 'remark', 'Паспортные данные'); 
    $form->hidden('doc_type', 1);
    $form->tdBox( 'text', array('Серия','Номер'), array('doc_serie','doc_number'),    array(45,70), array(4,6), array('N','N') ); 
    $form->tdBox( 'text', 'Кем выдан',         'doc_issued',  200, 200, 'A' ); 
    $form->tdBox( 'text', 'Код подразделения', 'doc_code',    100, 8, 'kodp' ); 
    $form->tdDateBox( 'Дата выдачи',           'doc_date',    1990, date('Y'), 'D' );
    $form->tdBox( 'text', 'Место рождения',    'birthplace',  200, 100, 'A' ); 

    $form->tdBox( 'remark', 'Адрес места жительства'); 
    $form->tdBox( 'text', 'Почтовый индекс',  'homeaddress[index]', 100, 6, 'ON' ); 

    $aspec = $msl->getArrayById("SELECT id,CONCAT(id,' - ',name) as name FROM `reg_rf_subject` ORDER BY id ASC",'id','name');
    $form->tdSelect(  'Субъект РФ', 'homeaddress[region]', $aspec, $rpval['rf'], 1);

    $form->tdBox( 'text', 'Населенный пункт',  'homeaddress[city]', 200, 50, 1 );
    $form->tdBox( 'text', 'Улица (квартал)',  'homeaddress[street]', 200, 60, 0 );
    $form->tdBox( 'text', array('Дом','корпус','квартира'),  array('homeaddress[home]','homeaddress[building]','homeaddress[flat]'), array(25,25,25), array(5,4,4), array('A',0,0) );  

    $form->tdBox( 'remark', 'Адрес регистрации'); 
    $form->tdBox( 'text', 'Почтовый индекс',  'regaddress[index]', 100, 6, 'ON' ); 
    $form->tdSelect(  'Субъект РФ', 'regaddress[region]', $aspec, $rpval['rf'], 1);
    $form->tdBox( 'text', 'Населенный пункт',  'regaddress[city]', 200, 50, 1 );
    $form->tdBox( 'text', 'Улица (квартал)',  'regaddress[street]', 200, 60, 0 );
    $form->tdBox( 'text', array('Дом','корпус','квартира'),  array('regaddress[home]','regaddress[building]','regaddress[flat]'), array(25,25,25), array(5,4,4), array('A',0,0) );  

    $form->tdBox( 'remark', 'Контактные данные'); 
    $form->tdBox( 'phone', 'Домашний телефон',         'homephone', array(40,70), array(5,10), 0 );
    $form->tdBox( 'phone', 'Мобильный телефон',        'mobile',    array(40,70), array(3,7), 1 );
    $form->tdBox( 'text', 'e-mail',           'e-mail',      200, 90, 'OE' ); 

    $form->tdBox( 'remark', 'Сведения об образовании');

    $catalog = new Catalog($msl);
    $bval = $catalog->getAvailableByRegion($region_id, "%abbr2% - %name% (%base%) - %qualify%", 0, 0);
    unset($catalog);

    $form->tdSelect(  'Образовательная программа', 'catalog', $bval, 0, 1);

    print "<TR><TD colspan=2  style=\"border:0px; margin: 0; padding: 0;\">";
    print "<DIV id=\"ege_fields\" style=\"border:0px; margin: 0; padding: 0 0 0 0;\">";

    $_POST['width']=180;
    $_POST['catalog']=@array_shift(array_keys($bval));
    require('get.php'); 

    print "</DIV></TD></TR>";

    $form->tdBox( 'text', 'Количество досдач (неизвестно = 0)',           'pay',      20, 3, 0, 0 ); 

    $kval = $msl->getArrayById("SELECT id, name FROM reg_education", 'id', 'name');
    $form->tdSelect(   'Тип образовательного учреждения', 'edu_base', $kval, 0, 1);

    $bdoc = $msl->getarrayById("SELECT id,name FROM `reg_edu_doc` WHERE `group`=1",'id','name');
    $form->tdSelect(  'Тип документа об образовании', 'edu_doc', $bdoc, 0, 1);

    $form->tdBox( 'text', array('Серия','№'),  array('edu_serie','edu_number'), array(45,65), array(10,10), array('A','N') );
    $form->tdDateBox( 'Дата выдачи',           'edu_date',    1990, date('Y'), 'D' );

    $form->tdBox( 'text', 'Образовательная организация', 'edu_institution', 250, 120, 'O' ); 
    $form->tdBox( 'text', 'Населенный пункт',            'edu_city',        250, 120, 'O' ); 
    $form->tdBox( 'text', 'Специальность, профессия',    'edu_specialty',   250, 120, 'O' ); 

    $form->tdRadio(   'Иностранный язык',   'language', $msl->getArrayById("SELECT id, name FROM reg_flang",'id','name'), 1, 1);
    $form->tdRadio(   'Высшее образование', 'highedu',  array('0'=>'впервые','1'=>'не впервые'), 0, 1);
    
    /*
    * Ввод номера договора в системе МАМИ для региона ЦКТ
    */
 
    if ($region_id == 3) {
        $form->tdBox( 'text', 'Номер договора в МАМИ',           'num',      50, 4, 'ON' );
    }
    print "</TBODY></TABLE></DIV>\n\n"; 
    unset($form);

    break;

case "editapplicant":
    print "<H1 class=\"title\">Редактирование данных абитуриента</H1><DIV id=\"output\"></DIV>";

    print "<DIV id=\"myaccordion\">";

    $form = new FormFields('index.php','formular', 180, 0, "Завершить редактирование");
    $rpval = $msl->getarray("SELECT rf FROM `partner_regions` WHERE id='".$region_id."'");
    $apval = $msl->getarray("SELECT * FROM `partner_applicant` WHERE id='".$_REQUEST['id']."'");

    new FabricApplicant($appl, $msl, "r".$_REQUEST['id']);

    print "<DIV><TABLE style=\"display: block;\"><TBODY style=\"border: none;\">"; 
    $form->hidden('region', $region_id);
    $form->hidden('id', $_REQUEST['id']);
    $form->hidden('act', 'applicantupdate');

    $form->tdBox( 'remark', 'Идентификационный номер '.$_REQUEST['id']); 
    $form->tdBox( 'text', 'Фамилия',          'surname',  200, 60, 'K', $apval['surname'] ); 
    $form->tdBox( 'text', 'Имя',              'name',     200, 60, 'K', $apval['name'] ); 
    $form->tdBox( 'text', 'Отчество',         'second_name', 200, 60, 0, $apval['second_name'] ); 

    $form->tdRadio(   'Пол',              'sex',         array('M'=>'Мужской','F'=>'Женский'), $apval['sex'], 1 );
    $form->tdDateBox( 'Дата рождения',    'birthday',        1950, date('Y')-16, 'D', 0, '1980', date('d.m.Y', strtotime($apval['birthday'])) );
    $form->tdBox( 'text', 'Гражданство',  'citizenry',   300, 60, 'K', $apval['citizenry']);

    $form->tdBox( 'remark', 'Паспортные данные'); 
    $form->hidden('doc_type', 1);
    $form->tdBox( 'text', array('Серия','Номер'), array('doc_serie','doc_number'),    array(45,70), array(4,6), array('N','N'), array($apval['doc_serie'],$apval['doc_number']) ); 
    $form->tdBox( 'text', 'Кем выдан',         'doc_issued',  200, 200, 'A', $apval['doc_issued'] ); 
    $form->tdBox( 'text', 'Код подразделения', 'doc_code',    100, 8, 'kodp', $apval['doc_code'] ); 
    $form->tdDateBox( 'Дата выдачи',           'doc_date',    1990, date('Y'), 'D', 0, '1980', date('d.m.Y', strtotime($apval['doc_date']))  );
    $form->tdBox( 'text', 'Место рождения',    'birthplace',  200, 100, 'A', $apval['birthplace'] ); 

    $addr = $appl->getAddress();
    
    $form->tdBox( 'remark', 'Адрес места жительства'); 
    $form->tdBox( 'text', 'Почтовый индекс',  'homeaddress[index]', 100, 6, 'ON', $addr[0]['index'] ); 

    $aspec = $msl->getArrayById("SELECT id,CONCAT(id,' - ',name) as name FROM `reg_rf_subject` ORDER BY id ASC",'id','name');
    $form->tdSelect(  'Субъект РФ', 'homeaddress[region]', $aspec, $addr[0]['region'], 1);

    $form->tdBox( 'text', 'Населенный пункт',  'homeaddress[city]', 200, 50, 1, $addr[0]['city'] );
    $form->tdBox( 'text', 'Улица (квартал)',  'homeaddress[street]', 200, 60, 0, $addr[0]['street'] );
    $form->tdBox( 'text', array('Дом','корпус','квартира'),  array('homeaddress[home]','homeaddress[building]','homeaddress[flat]'), array(25,25,25), array(5,4,4), array('A',0,0), array($addr[0]['home'],$addr[0]['building'],$addr[0]['flat']) );  

    $form->tdBox( 'remark', 'Адрес регистрации'); 
    $form->tdBox( 'text', 'Почтовый индекс',  'regaddress[index]', 100, 6, 'ON', $addr[1]['index'] ); 
    $form->tdSelect(  'Субъект РФ', 'regaddress[region]', $aspec, $addr[1]['region'], 1);
    $form->tdBox( 'text', 'Населенный пункт',  'regaddress[city]', 200, 50, 1, $addr[1]['city'] );
    $form->tdBox( 'text', 'Улица (квартал)',  'regaddress[street]', 200, 60, 0, $addr[1]['street'] );
    $form->tdBox( 'text', array('Дом','корпус','квартира'),  array('regaddress[home]','regaddress[building]','regaddress[flat]'), array(25,25,25), array(5,4,4), array('A',0,0), array($addr[1]['home'],$addr[1]['building'],$addr[1]['flat']) );  
   
    $form->tdBox( 'remark', 'Контактные данные'); 
    $form->tdBox( 'phone', 'Домашний телефон',         'homephone', array(40,70), array(5,10), 0, array($apval['homephone_code'],$apval['homephone']) );
    $form->tdBox( 'phone', 'Мобильный телефон',        'mobile',    array(40,70), array(3,7), 1, array($apval['mobile_code'],$apval['mobile']) );
    $form->tdBox( 'text', 'e-mail',           'e-mail',      200, 90, 'OE', $apval['e-mail'] ); 

    $form->tdBox( 'remark', 'Сведения об образовании');

    $catalog = new Catalog($msl);
    $bval = $catalog->getAvailableByRegion($region_id, "%name% (%base%) - %qualify%");
    unset($catalog);
    $form->tdSelect(  'Образовательная программа', 'catalog', $bval, $apval['catalog'], 1);

    print "<TR><TD colspan=2  style=\"border:0px; margin: 0; padding: 0;\">";
    print "<DIV id=\"ege_fields\" style=\"border:0px solid #d3d3d3; margin: 0; padding: 0 0 0 0;\">";

    $_POST['width']=180;
    $_POST['catalog']=$apval['catalog'];
    $_POST['semestr']=$apval['semestr'];
    $_POST['applicant_id']=$_REQUEST['id'];
    require('get.php'); 

    print "</DIV></TD></TR>";

    $form->tdBox( 'text', 'Количество досдач (неизвестно = 0)',           'pay',      20, 3, 0, $apval['pay'] ); 

    $kval = $msl->getArrayById("SELECT id, name FROM reg_education", 'id', 'name');
    $form->tdSelect(   'Тип образовательного учреждения', 'edu_base', $kval, $apval['edu_base'], 1);

    $bdoc = $msl->getarrayById("SELECT id,name FROM `reg_edu_doc` WHERE `group`=1",'id','name');
    $form->tdSelect(  'Тип документа об образовании', 'edu_doc', $bdoc, $apval['edu_doc'], 1);

    $form->tdBox( 'text', array('Серия','№'),  array('edu_serie','edu_number'), array(45,65), array(10,10), array('A','N'), array($apval['edu_serie'],$apval['edu_number']) );
    $form->tdDateBox( 'Дата выдачи',           'edu_date',    1990, date('Y'), 'D', 0, 0, date('d.m.Y', strtotime($apval['edu_date'])));

    $form->tdBox( 'text', 'Образовательная организация', 'edu_institution', 250, 120, 'O', $apval['edu_institution'] ); 
    $form->tdBox( 'text', 'Населенный пункт',            'edu_city',        250, 120, 'O', $apval['edu_city'] ); 
    $form->tdBox( 'text', 'Специальность, профессия',           'edu_specialty',      250, 120, 'O', $apval['edu_specialty'] ); 
    
    $form->tdRadio(   'Иностранный язык',   'language', $msl->getArrayById("SELECT id, name FROM reg_flang",'id','name'), $apval['language'], 1);
    $form->tdRadio(   'Высшее образование', 'highedu',  array('0'=>'впервые','1'=>'не впервые'), $apval['highedu'], 1);

    /*
    * Ввод номера договора в системе МАМИ для региона ЦКТ
    */
 
    if ($region_id == 3) {
        $form->tdBox( 'text', 'Номер договора в МАМИ',           'num',      50, 4, 'ON', $apval['num'] );
    }
    print "</TBODY></TABLE></DIV>\n\n"; 
    unset($form);

    break;

case "addstudentinsert":
    print "<H1 class=\"title\">Добавление абитуриента</H1><DIV id=\"output\"></DIV>";

    print "<P>Результат добавления:";
    require_once("insert.php");
    $ins = new Insertion();
    $id = $ins->newApplicant("", $_POST);
    
    if ($id > 0) {
        print "абитуриент ".$_POST['surname']." ".$_POST['name']." ".$_POST['second_name']." успешно добавлен.</P>\n\n";
    } else {
        print "произошла ошибка. Попробуйте обновить страницу.</P>\n\n";
	break;
    }
    unset($ins);

    print "<DIV id=\"myaccordion\">\n";   

    print "<h3>Документы для подписания</h3>";
    print "<DIV><TABLE style=\"display: block;\"><TBODY style=\"border: none;\">";
    
    new FabricApplicant($appl, $msl, "r".$id);
    $appl->printDocs();
    print "<TR><TD>Для просмотра документов вам потребуется <A href=\"http://get.adobe.com/reader/\">Adobe&copy; Reader</A>.\n</TD></TR>";
    print "</TBODY></TABLE></DIV>\n\n"; 
    unset($appl);
    break;

case "applicantupdate":
    print "<H1 class=\"title\">Редактирование абитуриента</H1><DIV id=\"output\"></DIV>";
    print "<P>Результат сохранения:";
    require_once("insert.php");
    $ins = new Insertion();
    $id = $ins->updateApplicant("", $_POST);
    
    if ($id > 0) {
        $ins->insertEge("", $_POST['ege'], $_POST['id']);
        print "данные абитуриента ".$_POST['surname']." ".$_POST['name']." ".$_POST['second_name']." успешно изменены.</P>\n\n";
    } else {
        print "произошла ошибка. Попробуйте обновить страницу.</P>\n\n";
	break;
    }
    unset($ins);

    print "<DIV id=\"myaccordion\">\n";   

    print "<h3>Документы для подписания</h3>";
    print "<DIV><TABLE style=\"display: block;\"><TBODY style=\"border: none;\">";
    	
    new FabricApplicant($appl, $msl, "r".$_POST['id']);
    $appl->printDocs();
    print "<TR><TD>Для просмотра документов вам потребуется <A href=\"http://get.adobe.com/reader/\">Adobe&copy; Reader</A>.\n</TD></TR>";
    print "</TBODY></TABLE></DIV>\n\n"; 
    unset($appl);
    break;

case "showstudentdocuments":
    new FabricApplicant($appl, $msl, "r".$_REQUEST['id']);
    $mdl = new Moodle($msl);

    print "<H1 class=\"title\">Работа с абитуриентом \"".$appl->surname." ".$appl->name." ".$appl->second_name."\"</H1><DIV id=\"output\"></DIV>";
    print "<DIV id=\"myaccordion\">\n";   
    print "<BR>";
    print "<h4>Документы для подписания</h4>";
    print "<DIV><TABLE style=\"display: block;\"><TBODY style=\"border: none;\">";
    	
    $appl->printDocs();

    if ($region_id != 3) {
        print "<TR><TD>Для просмотра документов вам потребуется <A href=\"http://get.adobe.com/reader/\">Adobe&copy; Reader</A>.\n</TD></TR>";
    } else {
        print "<TR><TD>\n";
	$email = $appl->getInfo('email','num');	

	if ($mdl->searchUser($email['e-mail']) > 0) {
	    print "<b>Система интернет-обучения Moodle: пользователь уже создан.</b><br>";
	} else {
            print "<script src=\"http://code.jquery.com/jquery-1.9.1.js\"></script>
<script language=\"javascript\">
                   function makeUser() {
		          $.ajax({
      type: \"POST\",
      url: \"index.php\",
      data: 'act=createmoodleuser&id=r".$_REQUEST['id']."&num='+$( \"#num\" ).val(),
      success: function(msg){alert(msg);}
		   })
}
	           </script>\n";
            print "<br>Номер личного дела в БД <INPUT type=\"text\" maxlength=4 value=\"".$email['num']."\" id=\"num\" style=\"width: 40px;\"> <A onclick=\"makeUser();\">Создать пользователя в Системе интернет-обучения</A><BR>\n";  
	}
        print "</TD></TR>";

    }

    print "</TBODY></TABLE></DIV>\n\n"; 
    break;

case "deletestudent":
    new FabricApplicant($appl, $msl, "r".$_REQUEST['id']);
    
    print "<H1 class=\"title\">Удаление абитуриента \"".$appl->surname." ".$appl->name." ".$appl->second_name."\"</H1><DIV id=\"output\"></DIV>";
    unset($appl);
    print "<P>Результат удаления: ";

    if ($msl->deleteArray('admission`.`partner_applicant', array('id'=>$_REQUEST['id'],'region'=>$region_id)) && 
       	$msl->deleteArray('admission`.`partner_applicant_address', array('applicant_id'=>$_REQUEST['id'])) && 
       	$msl->deleteArray('admission`.`partner_applicant_scores', array('applicant_id'=>$_REQUEST['id']))) {
        print "успешно";
    } else {
        print "произошла ошибка. Попробуйте обновить страницу";
    } 
    print ".</P>\n\n";
    break;

case "listapplicant":
    print "<H1 class=\"title\">Список абитуриентов</H1><DIV id=\"output\"></DIV>";
    print "<DIV id=\"myaccordion\">\n";   
    $rval = $msl->getarray("SELECT id,CONCAT(surname,' ',name,' ',second_name) as fio FROM partner_applicant WHERE region = '".$region_id."' ORDER by id DESC", 1);
    print "<DIV><TABLE style=\"display: block;\"><TBODY style=\"border: none;\">"; 
    
    if ($rval == 0) {
        print "<TR><TD><H4>У вас нет абитуриентов.</H4></TD></TR>";
    } else {
        foreach($rval as $k) {
            print "<TR><TD><A href=\"?act=showstudentdocuments&id=".$k['id']."\">".$k['fio']."</A>&nbsp;&nbsp;
	           <A href=\"?act=deletestudent&id=".$k['id']."\"><IMG src=\"../images/icons/Delete.png\" style=\"width: 16px;\" title=\"Удалить\"></A>&nbsp;
		   <A href=\"?act=editapplicant&id=".$k['id']."\"><IMG src=\"../images/icons/Edit.png\" style=\"width: 16px;\" title=\"Редактировать\"></A></TD></TR>";
        }
    }

    
    print "</TBODY></TABLE></DIV>\n\n"; 
    break;

case "basestudent":
    if ($rval['approved'] == 0) {    
        print "<P>Продолжение работы невозможно без подтверждения Вами сведений о региональном партнере. Для подтверждения <A href=\"?act=card\">проверьте свои данные</A>.</P>\n\n";
        break;
    }	    
    print "<SCRIPT language=\"javascript\">
           $('#example tbody tr td').live( 'click', function () {
           var parentr = $(this).parent('tr');
    	   $.ajax({url: 'student.php', type: 'POST', data: 'id='+parentr.find('td:eq(0)').html(),
              success: function(msg){
$('#dialog-message').html(msg)
                    .dialog('option', 'title', parentr.find('td:eq(1)').html()+' '+parentr.find('td:eq(2)').html()+' '+parentr.find('td:eq(3)').html()+' ('+parentr.find('td:eq(0)').html()+')' )
		    .dialog({ resizable: false })
		    .dialog('open'); 
		    } })
		   })
</SCRIPT>";

    print "<H1 class=\"title\">Список студентов</H1><DIV id=\"output\"></DIV>";
    print "<DIV id=\"myaccordion\"><BR>\n"; 

    require_once('../class/mssql.class.php');
    $mssql = new dMssql();
    $rval = $mssql->getarray("SELECT id,surname,name,second_name FROM dbo.student WHERE region = '".$region_id."' ORDER by id DESC", 1);
    
    print "<DIV><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"display\" id=\"example\">";
    print "<thead><tr>
                  <th>Номер</th>
		  <th>Фамилия</th>
		  <th>Имя</th>
		  <th>Отчество</th>
		  </tr><tr>
		  <th><INPUT type=\"text\" style=\"width: 55px;\" name=\"search_id\" value=\"поиск\" class=\"search_init\"></th>
		  <th><INPUT type=\"text\" style=\"width: 125px;\" name=\"search_surname\" value=\"поиск\" class=\"search_init\"></th>
		  <th></th>
		  <th></th>
		  </tr>
	   </thead><tbody>";
if ($rval == 0) {
        print "<TR><TD><H4>У вас нет студентов.</H4></TD></TR>";
    } else {
        foreach($rval as $k) {
            print "<TR><TD>".$k['id']."</TD><TD>".$k['surname']."</TD><TD>".$k['name']."</TD><TD>".$k['second_name']."</TD></TR>\n";
        }
    }
print "</TBODY></TABLE></DIV>\n\n";
//    print "<FORM method=POST id=\"dialog-form\"><DIV id=\"dialog-message\" style=\"width: 800px;\"></DIV></FORM>\n";
    print "<DIV id=\"dialog-message\" style=\"width: 800px;\"></DIV>\n";
    break;

case "receipt":
    if ($rval['approved'] == 0) {    
        print "<P>Продолжение работы невозможно без подтверждения Вами сведений о региональном партнере. Для подтверждения <A href=\"?act=card\">проверьте свои данные</A>.</P>\n\n";
        break;
    }	    
    print "<H1 class=\"title\">Печать квитанции</H1><DIV id=\"output\"></DIV>";
    print "<DIV id=\"myaccordion\">\n";   

    $form = new FormFields('../receipt/kvit.php','formular', 180, 0, "Распечатать квитанцию");

    print "<DIV><TABLE style=\"display: block;\"><TBODY style=\"border: none;\">"; 
    $form->hidden('region_id', $region_id);
    $form->tdSelect(  'Организация', 'partner_id', array(1=>'ЦКТ', 2=>'ИИТ'), 0, 1);
    
    $rval = $msl->getarrayById("SELECT id,text FROM `receipt_purpose`", 'id', 'text');
    $form->tdSelect(  'Назначение платежа', 'purpose', $rval, 0, 1);

    $catalog = new Catalog($msl);
    $rval = $catalog->getAvailableByRegion($region_id);
    unset($catalog);

    $form->tdSelect(  'Образовательная программа', 'catalog', $rval, 0, 1);

    $form->tdBox( 'text', 'Номер договора',  'dn',      50, 5, O ); 
    $form->tdBox( 'text', 'Семестр',         's',       20, 2, O ); 
    
    $form->tdBox( 'text', 'ФИО плательщика',   'fio',     450, 100, O ); 
    $form->tdBox( 'text', 'Адрес плательщика', 'address', 450, 100, O ); 
    $form->tdBox( 'text', 'Количество',        'count', 20, 2, O, 1 ); 

    unset($form);

    print "</TBODY></TABLE></DIV>\n\n";     
    break;

case "verified":
    $msl->insertArray('partner_agreement', array('region'=>$region_id, 'ip'=>sprintf('%u', ip2long($_SERVER['REMOTE_ADDR'])), 'remarks'=>$_POST['remarks']));

case "card":
    $rpval = $msl->getarray("SELECT a.*, b.name_rp as pos, c.name_rp as doc FROM `partner_regions` a 
    	     		     LEFT JOIN `partner_position` b ON a.gposition=b.id LEFT JOIN `partner_organizational_documents` c ON a.orgdoc=c.id WHERE a.id = '".$region_id."'");

    print "<H1 class=\"title\">Карточка регионального партнера</H1>";
    print "<DIV id=\"myaccordion\">\n";

    print "<DIV><TABLE style=\"display: block;\"><TBODY style=\"border: none;\">"; 
$agreed = $msl->getarray("SELECT date, used FROM `partner_agreement` WHERE region='".$region_id."'");
if ($agreed == 0) {      
   print "<TR><TD colspan=2>Проверьте указанные ниже сведения:</TD></TR>";
}
      print "<TR><TD style=\"width: 120px;\">Организация:</TD><TD>".$rpval['firm'].".</TD></TR>";
      print "<TR><TD style=\"width: 120px;\">Полное наименование:</TD><TD>".$rpval['longfirm'].".</TD></TR>";
      print "<TR><TD>В лице:</TD><TD>".(($rpval['pos'] == '') ? "<FONT color=red><B>укажите должность в замечаниях</B></FONT>": $rpval['pos'])." ".$rpval['name_rp'].".</TD></TR>";
      print "<TR><TD>На основании:</TD><TD>".$rpval['doc'].".</TD></TR>";
      print "<TR><TD>Юридический адрес:</TD><TD>".$rpval['legaladdress'].".</TD></TR>";
      print "<TR><TD>Фактический адрес:</TD><TD>".$rpval['physicaladdress'].".</TD></TR>";
      print "<TR><TD>БИК:</TD><TD>".$rpval['bik'].".</TD></TR>";
      print "<TR><TD>Кор.счет:</TD><TD>".$rpval['ks'].".</TD></TR>";
      print "<TR><TD>Рассчетный счет:</TD><TD>".$rpval['rs'].".</TD></TR>";
      print "<TR><TD>Банк:</TD><TD>".$rpval['bank'].".</TD></TR>";
      print "<TR><TD>ИНН/КПП:</TD><TD>".$rpval['inn']."/".$rpval['kpp'].".</TD></TR>";
    if ($rpval['ckt_num'] != '') {
        print "<TR><TD>Договор с ЦКТ:</TD><TD>".$rpval['ckt_num']." от ".date('j.m.Y', strtotime($rpval['ckt_date'])).".</TD></TR>";
    } 
    print "<TR><TD>Электронная почта:</TD><TD><A href=\"mailto:".$rpval['e-mail']."\">".$rpval['e-mail']."</A>.</TD></TR>";

if ($agreed == 0) {    
    print "<FORM method=\"POST\"><TR><TD style=\"vertical-align: top;\">Замечания:</TD><TD><TEXTAREA name=\"remarks\" WRAP=\"virtual\" COLS=\"90\" ROWS=\"3\"></TEXTAREA></TD></TR>";
    print "<INPUT type=\"hidden\" name=\"act\" value=\"verified\">";
    print "<TR><TD colspan=2 style=\"text-align: center;\"><INPUT type=submit value=\"Отправить\"></TD></TR></FORM>";
} else {
    print "<TR><TD colspan=2>Данные подтверждены ".date( 'j.m.Y в h:i', strtotime($agreed['date'])).".";
    if ($agreed['used'] == 1) {print " Замечания выполнены. В случае изменения Ваших данных, просьба незамедлительно связаться с нами по электронной почте.";}
    print "</TD></TR>\n";
}

    print "</TBODY></TABLE></DIV>\n\n";    
    break;

default:
    print "<H1 class=\"title\">Главная страница</H1><DIV id=\"output\"></DIV>";

    print "<P></P>\n\n"; //Пожалуйста заполните следующие поля (поля отмеченные * обязательны для заполнения):</P>\n\n";
    print "<DIV id=\"myaccordion\">";

    print "<h3>Добро пожаловать в Личный кабинет регионального партнера</h3>";

//    print "<P>Данная система предназначена для работы регионального партнераформирования комплекта документов абитуриента, поступающего через Центр Компьютерных Технологий в \"Московский государственный машиностроительный университет (МАМИ)\".</P>";
//    print "<h3>Пароль для базы данных</h3>";
//    print "<P>При установке базы данных требуется Ваш пароль: <B>".$rval['base_password']."</B></P>";

    print "<h3>Порядок работы с абитуриентами</h3>";
    print "<P>Для формирования договоров на нового абитуриента выберите в меню слева пункт \"Добавить абитуриента\". После заполнения всех необходимых полей (поле количество досдач оставить 0, если количество платных досдач неизвестно. При этом будет формироваться дополнительное соглашение и квитанция для дополнительного соглашения без указания сумм), нажмите кнопку \"Добавить абитуриента\". После добавления Вы сможете распечатать или сохранить весь необходимый комплект документов.</P>";
    print "<P>Также, имеется возможность просмотреть список уже оформленных абитуриентов и их документов. Для этого в меню слева выберите пункт \"Список абитуриентов\". При выборе конкретного абитуриента Вы получите список его документов.</P>";
    
/*    print "<h3>Квитанция на оплату</h3>";
    print "<P>Для формирования квитанции необходимо выбрать в меню слева пункт \"Распечатать квитанцию\". Выберите организацию (ИИТ или ЦКТ), назначение платежа, образовательную программу. Поля номер договора, семестр, ФИО плательщика, адрес плательщика являются необязательными. Поле количество требуется для указания количества пересдач или досдач, в иных случаях указывайте 1. После заполнения всех необходимых полей, нажмите кнопку \"Распечатать квитанцию\". Полученный pdf-документ, содержащий квитанцию со всеми реквизитами, можно открыть или сохранить.</P>";
*/

    print "<h3>Сведения по студентам</h3>";
    print "<P>Вы можете получить сведения по студентам, их ведомость успеваемости или квитанцию на оплату в режиме реального времени. Для этого необходимо выбрать в меню слева пункт \"Список студентов\", найти необходимого студента и нажать на него. В появившемся окне Вы можете вывести ведомость успеваемости или квитанцию в формате HTML или PDF. Формат HTML является гораздо более \"компактным\" по размеру полученного файла, но может иметь проблемы с выводом на печать (зависит от настроек браузера, принтера и тп).</P>";
//    print "<DIV><TABLE style=\"display: block;\"><TBODY style=\"border: none;\">"; 
//   print "</TBODY></TABLE></DIV>\n\n"; 

}
unset($msl);


print '<P>Если у Вас появились вопросы, свяжитесь с нашими сотрудниками по телефонам: +7 (499) 127-7496, +7 (499) 127-7453 или <A href="mailto:iit@ins-iit.ru">по электронной почте</A>.</p></div></div>';
                    
?>

<div id="footer">© 2009-2013, ins-iit.ru Team<div id="block-system-0" class="clear-block block block-system">
</div>
</div>
      </div></div></div></div> <!-- /.left-corner, /.right-corner, /#squeeze, /#center -->
    </div>
  </div>
</body></html>
