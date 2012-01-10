<?php
require_once('../../conf.php');
require_once('../../../modules/mysql.php');
require_once('../class/forms.class.php');
require_once('../class/catalog.class.php');

if ($_SESSION['rights'] == 'admin' && $_SESSION['md_rights'] == md5($CFG_salted.$_SESSION['rights'])) {
    if ($_REQUEST['region'] > 0) $_SESSION['joomlaregion'] = $_REQUEST['region'];
}
if ($_SESSION['joomlaregion'] == 0) exit(0); 
$region_id = $_SESSION['joomlaregion'];

$msl = new dMysql();
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

</script>

</head>
<body class="sidebar-left">



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
    $rval = $msl->getarray("SELECT firm,approved FROM `partner_regions` WHERE id='".$region_id."'");
    print "<div class=\"form-item\">".$rval['firm']."</div>";
?>
</div>

</div>
<div id="block-user-1" class="clear-block block block-user">

  <h2>Действия</h2>

  <div class="content"><ul class="menu">
  <li class="collapsed last"><a href="?act=addstudent">Добавить абитуриента</a></li>
  <li class="collapsed last"><a href="?act=liststudent">Список абитуриентов</a></li> 
  <li class="collapsed last"><a href="?act=basestudent">Список студентов</a></li> 
<!--  <li class="collapsed last"><a href="?act=receipt">Распечатать квитанцию</a></li> -->
  <li class="collapsed last"><a href="?">Вернуться на главную</a></li>
  </ul></div>
</div>
        </div>
      
      <div id="center"><div id="squeeze"><div class="right-corner"><div class="left-corner">
                                                                                          <div class="clear-block">
            <div id="first-time">

<?php

class Applicant
{
    private $id;

    public function __construct($id) 
    {
        $this->id = $id;
        return 0;
    }
   
    public function __destruct() 
    {
        return 0;
    }

    public function printDocumentList() 
    {
        global $msl;
        $id = $this->id;
	$r = $msl->getarray("SELECT birthday, semestr, pay FROM admission.`partner_applicant` WHERE id='".$id."' LIMIT 1;");
        print "<DIV><TABLE style=\"display: block;\"><TBODY style=\"border: none;\">";
    	switch($r['semestr']) {
	case 0:
	    print "<TR><TD><A href=\"documents/anketa.php?applicant=".$id."\" target=\"_blank\">Заявление абитуриента (на первый семестр)</A></TD></TR>\n";
	    print "<TR><TD><A href=\"documents/anketa2.php?applicant=".$id."\" target=\"_blank\">Заявление абитуриента (на второй и выше)</A></TD></TR>\n";
	    print "<TR><TD><A href=\"documents/ds_ckt.php?applicant=".$id."\" target=\"_blank\">Дополнительное соглашение</A></TD></TR>\n";
	    print "<TR><TD><A href=\"../receipt/kvit.php?applicant=".$id."&purpose=3\" target=\"_blank\">Квитанция согласно доп.соглашения</A></TD></TR>\n";
	    break;
        case 1:
            print "<TR><TD><A href=\"documents/anketa.php?applicant=".$id."\" target=\"_blank\">Заявление абитуриента</A></TD></TR>\n";
	    break;
	default:
	    print "<TR><TD><A href=\"documents/anketa2.php?applicant=".$id."\" target=\"_blank\">Заявление абитуриента</A></TD></TR>\n";
	    print "<TR><TD><A href=\"documents/ds_ckt.php?applicant=".$id."\" target=\"_blank\">Дополнительное соглашение</A></TD></TR>\n";
	    print "<TR><TD><A href=\"../receipt/kvit.php?applicant=".$id."&purpose=3\" target=\"_blank\">Квитанция согласно доп.соглашения</A></TD></TR>\n";
	    break;
    	}
    	print "<TR><TD><A href=\"documents/diplom.php?applicant=".$id."\" target=\"_blank\">Заявление на возврат оригинала документа об образовании</A> (даты не ставить)</TD></TR>\n";
    	print "<TR><TD><A href=\"documents/dog_ckt.php?applicant=".$id."\">Договор на оказание платных образовательных услуг</A> (3 экземпляра)</TD></TR>\n";
    	print "<TR><TD><A href=\"documents/dog_ckt_rp.php?applicant=".$id."\">Договор об организации обучения гражданина на платной основе</A> (3 экземпляра)</TD></TR>\n";	    
    	print "<TR><TD><A href=\"documents/opis.php?applicant=".$id."\" target=\"_blank\">Опись документов личного дела</A></TD></TR>\n";
    	print "<TR><TD><A href=\"../receipt/kvit.php?applicant=".$id."&purpose=1\" target=\"_blank\">Квитанция на оплату обучения</A></TD></TR>\n";

	if ((time()-strtotime($r['birthday']))<567648000) {
	    print "<TR><TD><A href=\"../documents/dop_net_18.pdf\" target=\"_blank\">Дополнение к договору</A> (2 экземпляра, если нет 18 лет)</TD></TR>\n";	
	}

	print "<TR><TD>Для просмотра документов вам потребуется <A href=\"http://get.adobe.com/reader/\">Adobe&copy; Reader</A>.\n</TD></TR>";
	
	print "</TBODY></TABLE></DIV>\n\n";         
    }

    public function getSurnameNS($array = 0) 
    {
        global $msl;
        $r = $msl->getarray("SELECT surname,name,second_name FROM `partner_applicant` WHERE id='".$this->id."'");
	if (!$array) {
	    return $r['surname']." ".$r['name']." ".$r['second_name'];
	}
	return $r;
    } 
}

if ($rval['approved'] == 0) {      
    print "<P>Продолжение работы невозможно без подтверждения Вами сведений о региональном партнере. Для подтверждения <A href=\"card.php\" target=\"_blank\">проверьте свои данные</A>.</P>\n\n";
} else {
$act=$_REQUEST['act'];

switch($act) {
case "addstudent":
    print "<H1 class=\"title\">Добавление абитуриента</H1><DIV id=\"output\"></DIV>";

    print "<P>Пожалуйста заполните следующие поля (поля отмеченные * обязательны для заполнения):</P>\n\n";
    print "<DIV id=\"myaccordion\">";

    $form = new FormFields('index.php','formular', 180, 0, "Добавить абитуриента");
    $rpval = $msl->getarray("SELECT rf FROM `partner_regions` WHERE id='".$region_id."'");

    print "<DIV><TABLE style=\"display: block;\"><TBODY style=\"border: none;\">"; 
    $form->hidden('region', $region_id);
    $form->hidden('act', 'addstudentinsert');

    $form->tdBox( 'text', 'Фамилия',          'surname',  200, 60, K ); 
    $form->tdBox( 'text', 'Имя',              'name',     200, 60, K ); 
    $form->tdBox( 'text', 'Отчество',         'second_name', 200, 60, 0 ); 

    $form->tdRadio(   'Пол',              'sex',         array('M'=>'Мужской','F'=>'Женский'), M, 1);
    $form->tdDateBox( 'Дата рождения',    'birthday',        1950, date('Y')-16, D, 75, '1980' );
    $form->tdRadio(   'Гражданство',      'citizenry',   array('Российская Федерация'=>'Российская Федерация','other'=>'Другое'), null, 1);

    $form->tdBox( 'remark', 'Паспортные данные'); 
    $form->hidden('doc_type', 1);
    $form->tdBox( 'text', array('Серия','Номер'), array('doc_serie','doc_number'),    array(45,70), array(4,6), array(N,N) ); 
    $form->tdBox( 'text', 'Кем выдан',         'doc_issued',  200, 200, A ); 
    $form->tdBox( 'text', 'Код подразделения', 'doc_code',    100, 8, 'kodp' ); 
    $form->tdDateBox( 'Дата выдачи',           'doc_date',    1990, date('Y'), D );
    $form->tdBox( 'text', 'Место рождения',    'birthplace',  200, 100, A ); 

    $form->tdBox( 'remark', 'Адрес места жительства'); 
    $form->tdBox( 'text', 'Почтовый индекс',  'homeaddress-index', 100, 6, N ); 

    $aspec = $msl->getArrayById("SELECT id,CONCAT(id,' - ',name) as name FROM `reg_rf_subject` ORDER BY id ASC",'id','name');
    $form->tdSelect(  'Субъект РФ', 'homeaddress-region', $aspec, $rpval['rf'], 1);

    $form->tdBox( 'text', 'Населенный пункт',  'homeaddress-city', 200, 50, K );
    $form->tdBox( 'text', 'Улица (квартал)',  'homeaddress-street', 200, 60, 0 );
    $form->tdBox( 'text', array('Дом','корпус','квартира'),  array('homeaddress-home','homeaddress-building','homeaddress-flat'), array(25,25,25), array(5,4,4), array(A,0,0) );  

    $form->tdBox( 'remark', 'Адрес регистрации'); 
    $form->tdBox( 'text', 'Почтовый индекс',  'regaddress[index]', 100, 6, N ); 
    $form->tdSelect(  'Субъект РФ', 'regaddress[region]', $aspec, $rpval['rf'], 1);
    $form->tdBox( 'text', 'Населенный пункт',  'regaddress[city]', 200, 50, K );
    $form->tdBox( 'text', 'Улица (квартал)',  'regaddress[street]', 200, 60, 0 );
    $form->tdBox( 'text', array('Дом','корпус','квартира'),  array('regaddress[home]','regaddress[building]','regaddress[flat]'), array(25,25,25), array(5,4,4), array(A,0,0) );  

    $form->tdBox( 'remark', 'Контактные данные'); 
    $form->tdBox( 'phone', 'Домашний телефон',         'homephone', array(40,70), array(5,10), 1 );
    $form->tdBox( 'phone', 'Мобильный телефон',        'mobile',    array(40,70), array(3,7), 1 );
    $form->tdBox( 'text', 'e-mail',           'e-mail',      200, 90, OE ); 

    $form->tdBox( 'remark', 'Сведения об образовании');

    $catalog = new Catalog();
    $bval = $catalog->getAvailableByRegion($region_id, "%name% (%base%) - %qualify%");
    unset($catalog);
    $form->tdSelect(  'Образовательная программа', 'catalog', $bval, 0, 1);

    print "<TR><TD colspan=2  style=\"border:0px; margin: 0; padding: 0;\">";
    print "<DIV id=\"ege_fields\" style=\"border:0px solid #d3d3d3; margin: 0; padding: 0 0 0 0;\">";

    $_POST['width']=180;
    $_POST['catalog']=@array_shift(array_keys($bval));
    require('get.php'); 

    print "</DIV></TD></TR>";

    $form->tdBox( 'text', 'Количество досдач (неизвестно = 0)',           'pay',      20, 3, 0, 0 ); 

    $kval = $msl->getArrayById("SELECT id, name FROM reg_education", 'id', 'name');
    $form->tdSelect(   'Тип учебного заведения', 'edu_base', $kval, 0, 1);

    $bdoc = $msl->getarrayById("SELECT id,name FROM `reg_edu_doc` WHERE `group`=1",'id','name');
    $form->tdSelect(  'Тип документа об образовании', 'edu_doc', $bdoc, 0, 1);

    $form->tdBox( 'text', array('Серия','№'),  array('edu_serie','edu_number'), array(45,65), array(10,10), array(A,N) );
    $form->tdDateBox( 'Дата выдачи',           'edu_date',    1990, date('Y'), D );

    $form->tdBox( 'text', 'Учебное заведение',           'edu_institution',      250, 90, O ); 
    $form->tdBox( 'text', 'Специальность',           'edu_specialty',      250, 90, O ); 

    $form->tdRadio(   'Иностранный язык',   'language', $msl->getArrayById("SELECT id, name FROM reg_flang",'id','name'), 1, 1);
    $form->tdRadio(   'Высшее образование', 'highedu',  array('0'=>'впервые','1'=>'не впервые'), 0, 1);
    print "</TBODY></TABLE></DIV>\n\n"; 
    unset($form);

    break;

case "editapplicant":
    print "<H1 class=\"title\">Редактирование данных абитуриента</H1><DIV id=\"output\"></DIV>";

    print "<DIV id=\"myaccordion\">";

    $form = new FormFields('index.php','formular', 180, 0, "Завершить редактирование");
    $rpval = $msl->getarray("SELECT rf FROM `partner_regions` WHERE id='".$region_id."'");
    $apval = $msl->getarray("SELECT * FROM `partner_applicant` WHERE id='".$_REQUEST['id']."'");

    print "<DIV><TABLE style=\"display: block;\"><TBODY style=\"border: none;\">"; 
    $form->hidden('region', $region_id);
    $form->hidden('id', $_REQUEST['id']);
    $form->hidden('act', 'applicantupdate');

    $form->tdBox( 'remark', 'Идентификационный номер '.$_REQUEST['id']); 
    $form->tdBox( 'text', 'Фамилия',          'surname',  200, 60, K, $apval['surname'] ); 
    $form->tdBox( 'text', 'Имя',              'name',     200, 60, K, $apval['name'] ); 
    $form->tdBox( 'text', 'Отчество',         'second_name', 200, 60, 0, $apval['second_name'] ); 

    $form->tdRadio(   'Пол',              'sex',         array('M'=>'Мужской','F'=>'Женский'), $apval['sex'], 1 );
    $form->tdDateBox( 'Дата рождения',    'birthday',        1950, date('Y')-16, D, 0, '1980', date('d.m.Y', strtotime($apval['birthday'])) );
    $form->tdBox( 'text', 'Гражданство',  'citizenry',   300, 60, K, $apval['citizenry']);

    $form->tdBox( 'remark', 'Паспортные данные'); 
    $form->hidden('doc_type', 1);
    $form->tdBox( 'text', array('Серия','Номер'), array('doc_serie','doc_number'),    array(45,70), array(4,6), array(N,N), array($apval['doc_serie'],$apval['doc_number']) ); 
    $form->tdBox( 'text', 'Кем выдан',         'doc_issued',  200, 200, A, $apval['doc_issued'] ); 
    $form->tdBox( 'text', 'Код подразделения', 'doc_code',    100, 8, 'kodp', $apval['doc_code'] ); 
    $form->tdDateBox( 'Дата выдачи',           'doc_date',    1990, date('Y'), D, 0, '1980', date('d.m.Y', strtotime($apval['doc_date']))  );
    $form->tdBox( 'text', 'Место рождения',    'birthplace',  200, 100, A, $apval['birthplace'] ); 

    $form->tdBox( 'remark', 'Адрес места жительства'); 
    $form->tdBox( 'text', 'Почтовый индекс',  'homeaddress-index', 100, 6, N, $apval['homeaddress-index'] ); 

    $aspec = $msl->getArrayById("SELECT id,CONCAT(id,' - ',name) as name FROM `reg_rf_subject` ORDER BY id ASC",'id','name');
    $form->tdSelect(  'Субъект РФ', 'homeaddress-region', $aspec, $apval['homeaddress-region'], 1);

    $form->tdBox( 'text', 'Населенный пункт',  'homeaddress-city', 200, 50, K, $apval['homeaddress-city'] );
    $form->tdBox( 'text', 'Улица (квартал)',  'homeaddress-street', 200, 60, 0, $apval['homeaddress-street'] );
if ($apval['homeaddress-flat'] == 0) $apval['homeaddress-flat'] == '';
    $form->tdBox( 'text', array('Дом','корпус','квартира'), array('homeaddress-home','homeaddress-building','homeaddress-flat'), array(25,25,25), array(5,4,4), array(A,0,0), array($apval['homeaddress-home'],$apval['homeaddress-building'],$apval['homeaddress-flat']));  
// 

    $form->tdBox( 'remark', 'Адрес регистрации'); 
    $form->tdBox( 'text', 'Почтовый индекс',  'regaddress', 450, 250, 1, $apval['regaddress'] ); 
   
    $form->tdBox( 'remark', 'Контактные данные'); 
    $form->tdBox( 'phone', 'Домашний телефон',         'homephone', array(40,70), array(5,10), 1, array($apval['homephone_code'],$apval['homephone']) );
    $form->tdBox( 'phone', 'Мобильный телефон',        'mobile',    array(40,70), array(3,7), 1, array($apval['mobile_code'],$apval['mobile']) );
    $form->tdBox( 'text', 'e-mail',           'e-mail',      200, 90, OE, $apval['e-mail'] ); 

    $form->tdBox( 'remark', 'Сведения об образовании');

    $catalog = new Catalog();
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
    $form->tdSelect(   'Тип учебного заведения', 'edu_base', $kval, $apval['edu_base'], 1);

    $bdoc = $msl->getarrayById("SELECT id,name FROM `reg_edu_doc` WHERE `group`=1",'id','name');
    $form->tdSelect(  'Тип документа об образовании', 'edu_doc', $bdoc, $apval['edu_doc'], 1);

    $form->tdBox( 'text', array('Серия','№'),  array('edu_serie','edu_number'), array(45,65), array(10,10), array(A,N), array($apval['edu_serie'],$apval['edu_number']) );
    $form->tdDateBox( 'Дата выдачи',           'edu_date',    1990, date('Y'), D, 0, 0, date('d.m.Y', strtotime($apval['edu_date'])));

    $form->tdBox( 'text', 'Учебное заведение',           'edu_institution',      250, 90, O, $apval['edu_institution'] ); 
    $form->tdBox( 'text', 'Специальность',           'edu_specialty',      250, 90, O, $apval['edu_specialty'] ); 

    $form->tdRadio(   'Иностранный язык',   'language', $msl->getArrayById("SELECT id, name FROM reg_flang",'id','name'), $apval['language'], 1);
    $form->tdRadio(   'Высшее образование', 'highedu',  array('0'=>'впервые','1'=>'не впервые'), $apval['highedu'], 1);
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
        $ins->insertEge("", $_POST['ege'], $id);
        print "абитуриент ".$_POST['surname']." ".$_POST['name']." ".$_POST['second_name']." успешно добавлен.</P>\n\n";
    } else {
        print "произошла ошибка. Попробуйте обновить страницу.</P>\n\n";
	break;
    }
    unset($ins);

    print "<DIV id=\"myaccordion\">\n";   

    print "<h3>Документы для подписания</h3>";
    $appl = new Applicant($id);
    $appl->printDocumentList();
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
    $appl = new Applicant($_POST['id']);
    $appl->printDocumentList();
    unset($appl);
    break;

case "showstudentdocuments":
    $appl = new Applicant($_REQUEST['id']);
    
    print "<H1 class=\"title\">Работа с абитуриентом \"".$appl->getSurnameNS()."\"</H1><DIV id=\"output\"></DIV>";
    print "<DIV id=\"myaccordion\">\n";   
    print "<BR>";
    print "<h4>Документы для подписания</h4>";
    $appl->printDocumentList();
    break;

case "deletestudent":
    $appl = new Applicant($_REQUEST['id']);
    
    print "<H1 class=\"title\">Удаление абитуриента \"".$appl->getSurnameNS()."\"</H1><DIV id=\"output\"></DIV>";
    unset($appl);
    print "<P>Результат удаления: ";


    if ($msl->deleteArray('admission`.`partner_applicant', array('id'=>$_REQUEST['id'],'region'=>$region_id))) {
        print "успешно";
    } else {
        print "произошла ошибка. Попробуйте обновить страницу";
    }
    print ".</P>\n\n";
    break;

case "liststudent":
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
    $rval = $msl->getarray("SELECT id,surname,name,second_name FROM `students_base`.`student` WHERE region = '".$region_id."' ORDER by id DESC", 1);
    
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
    print "<H1 class=\"title\">Печать квитанции</H1><DIV id=\"output\"></DIV>";
    print "<DIV id=\"myaccordion\">\n";   

    $form = new FormFields('../receipt/kvit.php','formular', 180, 0, "Распечатать квитанцию");

    print "<DIV><TABLE style=\"display: block;\"><TBODY style=\"border: none;\">"; 
    $form->hidden('region_id', $region_id);
    $form->tdSelect(  'Организация', 'partner_id', array(1=>'ЦКТ', 2=>'ИИТ'), 0, 1);
    
    $rval = $msl->getarrayById("SELECT id,text FROM `receipt_purpose`", 'id', 'text');
    $form->tdSelect(  'Назначение платежа', 'purpose', $rval, 0, 1);

    $catalog = new Catalog();
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

default:
    print "<H1 class=\"title\">Главная страница</H1><DIV id=\"output\"></DIV>";

    print "<P></P>\n\n"; //Пожалуйста заполните следующие поля (поля отмеченные * обязательны для заполнения):</P>\n\n";
    print "<DIV id=\"myaccordion\">";

    print "<h3>Добро пожаловать в Личный кабинет регионального партнера</h3>";

    print "<P>Данная система предназначена для формирования комплекта документов абитуриента, поступающего через Центр Компьютерных Технологий в Московский государственный технический университет \"МАМИ\".</P>";

    print "<h3>Порядок работы</h3>";
    print "<P>Для формирования договоров на нового абитуриента выберите в меню слева пункт \"Добавить абитуриента\". После заполнения всех необходимых полей (поле количество досдач оставить 0, если количество платных досдач неизвестно. При этом будет формироваться дополнительное соглашение и квитанция для дополнительного соглашения без указания сумм), нажмите кнопку \"Добавить абитуриента\". После добавления Вы сможете распечатать или сохранить весь необходимый комплект документов.</P>";
    print "<P>Также, имеется возможность просмотреть список уже оформленных абитуриентов и их документов. Для этого в меню слева выберите пункт \"Список абитуриентов\". При выборе конкретного абитуриента Вы получите список его документов.</P>";
/*    
    print "<h3>Квитанция на оплату</h3>";
    print "<P>Для формирования квитанции необходимо выбрать в меню слева пункт \"Распечатать квитанцию\". Выберите организацию (ИИТ или ЦКТ), назначение платежа, образовательную программу. Поля номер договора, семестр, ФИО плательщика, адрес плательщика являются необязательными. Поле количество требуется для указания количества пересдач или досдач, в иных случаях указывайте 1. После заполнения всех необходимых полей, нажмите кнопку \"Распечатать квитанцию\". Полученный pdf-документ, содержащий квитанцию со всеми реквизитами, можно открыть или сохранить.</P>";
*/
//    print "<DIV><TABLE style=\"display: block;\"><TBODY style=\"border: none;\">"; 
//   print "</TBODY></TABLE></DIV>\n\n"; 

}
unset($msl);
}

print '<P>Если у Вас появились вопросы, свяжитесь с нашими сотрудниками по телефонам: +7 (495) 663-1562, +7 (495) 663-1505 или <A href="mailto:iit@ins-iit.ru">по электронной почте</A>.</p></div></div>';
                    
?>

<div id="footer">© 2009-2011, ins-iit.ru Team<div id="block-system-0" class="clear-block block block-system">
</div>
</div>
      </div></div></div></div> <!-- /.left-corner, /.right-corner, /#squeeze, /#center -->

      
    </div> <!-- /container -->
  </div>
<!-- /layout -->

</body></html>
