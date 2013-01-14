<?php
require_once('../conf.php');
require_once('class/mysql.class.php');
require_once('class/forms.class.php');
require_once('class/catalog.class.php');
require_once('class/documents.class.php');
$msl = new dMysql();

//$_SESSION['step_num'] = 2;
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html dir="ltr" xml:lang="ru" xmlns="http://www.w3.org/1999/xhtml" lang="ru"><head>
<link rel="icon" href="images/favicon.ico" type="image/x-icon"/> 
<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
 
  
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Поступление - Анкета</title>

<link type="text/css" rel="stylesheet" media="all" href="images/defaults.css" />
<link type="text/css" rel="stylesheet" media="all" href="images/system.css" />
<link type="text/css" rel="stylesheet" media="all" href="images/style.css" />
<link type="text/css" rel="stylesheet" media="all" href="css/smoothness/jquery-ui-1.8.7.custom.css" />	


<link href="css/jquery.alerts.css" rel="stylesheet" type="text/css" media="screen" />

<!-- Validation -->
<link rel="stylesheet" href="css/validationEngine.jquery.css" type="text/css" media="screen" title="no title" charset="utf-8" />
</head>
<body class="sidebar-left">

<script type="text/javascript" src="js/jquery-1.4.4.min.js"></script>
<!-- jQuery UI -->
<script type="text/javascript" src="js/jquery-ui-1.8.7.custom.min.js"></script>
<!--<SCRIPT type="text/javascript" src="js/jquery.ui.datepicker.js"></script>-->
<script type="text/javascript" src="js/jquery.ui.datepicker-ru.js"></script>
<script type="text/javascript" src="js/jquery.form.js"></script>
<script type="text/javascript" src="js/jquery.blockUI.js"></script>
<script type="text/javascript" src="js/jquery.alerts.js"></script>
<script type="text/javascript" src="js/ajaxupload.js"></script>
<!-- Script validation -->
<script type="text/javascript" src="js/jquery.validationEngine-ru.js"></script>
<script type="text/javascript" src="js/jquery.validationEngine.js"></script>

<script type="text/javascript">
	$(function() {
		     $("input:submit, input:button").button();
                     $.datepicker.setDefaults({changeMonth: true, changeYear: true});
	});

$.ajaxSetup({
   type: "POST", 
   cache: false,
   dataType: 'html',
   beforeSend: function() {
      $.blockUI({ message: 'Ваш запрос обрабатывается...' })
   },
   success: function(msg) {
      $.unblockUI();
      switch(msg.replace(/\s+/, '')) {
         case "ok":
	    window.location.reload();
	    break;
	 case "emailused":
	    alert('Адрес электронной почты уже использован. Укажите другой адрес электронной почты.');
	    window.location.reload();
	    break; 
	 default:
            alert('При передаче формы возникла ошибка. Пожалуйста попробуйте еще раз');
      }     
   },
   error: function(msg) {
      alert('При обработке формы возникла ошибка. Пожалуйста попробуйте еще раз');
   }
});

function startPart()
{
   setTimeout("endPart()", 10000);
}

function endPart()
{
   $.validationEngine.closePrompt('.formError',true);
}

$(document).ready(function() {
   var ajaxready = false;
   $("#specialty :nth-child(1)").attr("selected", "selected");
   $("#user-login-form").validationEngine({
      success: function() {
         $.ajax({url: 'login.php', data: $("#user-login-form").serialize()}); 
      }	})

   $("#formular").validationEngine({
      success: function() {
         $.ajax({url: 'insert.php', data: $("#formular").serialize()}); 
      },
      failure: startPart()	}) 

   $("#catalog").change(function(){
      $("#catalog").attr("disabled","disabled"); 
      $.ajax({url: 'get.php', data: 'width=180&catalog=' + $('#catalog').val(), beforeSend: function(){}, 
              success: function(msg){
                 $('#ege_fields').html(msg);
              }})
      $("#catalog").attr("disabled",""); 
      })
   $("#edu_base :nth-child(1)").attr("selected", "selected");

   $('#edudoc_typeradio').append('Аттестат.').children('label').hide(); 
$('#edudoc_typeradio').children('input').hide()
   $('#edudoc_typeradio').val('1');

   $("#edu_base").change(function(){
      $('#edudoc_typeradio').html($('#edudoc_typeradio').html().replace('Аттестат.', '').replace('Диплом.', ''));

      switch($("#edu_base").val()) {
         case '1':
            $('#edudoc_typeradio').children('label').hide(); 
$('#edudoc_typeradio').children('input').hide()

	    $('#edudoc_typeradio').append('Аттестат.').val('1');
      	    break;
         case '2':
	 case '3':
            $('#edudoc_typeradio').children('label').hide(); 
	    $('#edudoc_typeradio').children('input').hide()
	    $('#edudoc_typeradio').append('Диплом.').val('2');
      	    break;
	 default:
	    $('#edudoc_typeradio').children('label').show(); 
	    $('#edudoc_typeradio').children('input').show();
      } 
   })   
});

</script>

  <div id="header-region" class="clear-block"></div>

    <div id="wrapper">
    <div id="container" class="clear-block">

      <div id="header">
        <div id="logo-floater">
        <h1><span>Электронная приемная комиссия</span></h1>        </div>
      </div>

              <div id="sidebar-left" class="sidebar">
                    <div id="block-user-0" class="clear-block block block-user">
<?php

if (!isset($_SESSION['applicant_id'])) {
   print "<h2>Вход в систему</h2>";
} else {
   print "<h2></h2>";
}
?>
  <div class="content"><form accept-charset="UTF-8" method="post" id="user-login-form">
<div>
<?php
if (!isset($_SESSION['applicant_id'])) {
    print "<div class=\"form-item\" id=\"edit-name-wrapper\">";
    print "<label for=\"edit-name\">E-mail: <span class=\"form-required\" title=\"Поле обязательно для заполнения.\">*</span></label>
           <input maxlength=\"60\" name=\"name\" id=\"edit-name\" size=\"15\" class=\"validate[required,custom[email]] text-input\" type=\"text\" /></div>";
    print "<div class=\"form-item\">";
    print "<label for=\"edit-pass\"><span title=\"Номер и серия паспорта, введенные слитно\">Данные паспорта</span>: <span class=\"form-required\" title=\"Поле обязательно для заполнения.\">*</span></label><input name=\"pass\" id=\"edit-pass\" maxlength=\"60\" size=\"15\" class=\"validate[required,custom[all]]\" type=\"password\" /></div>";
    print "<input name=\"op\" id=\"edit-submit\" value=\"Войти\" class=\"form-submit\" type=\"submit\" />";
} else {
    $rvalx = $msl->getarray("SELECT surname, name, second_name, birthday FROM `reg_applicant` WHERE id='".$_SESSION['applicant_id']."'");
    print "<div class=\"form-item\" id=\"edit-name-wrapper\">";
    print "Вы вошли как ".$rvalx['surname']." ".$rvalx['name']." ".$rvalx['second_name'];
    print "</div>";
    if ($_SESSION['step_num'] > 1) {
        print "<div class=\"form-item\" id=\"edit-name-wrapper\"><input type=\"button\" onclick=\"javascript: $.ajax({url: 'login.php', data: 'act=exit'});\" value=\"Выйти\" /></div>";
    }
}
?>
</div></form>
</div>
</div>
<div id="block-user-1" class="clear-block block block-user">

  <h2>Навигация</h2>

  <div class="content"><ul class="menu"><li class="collapsed last"><a href="http://www.ins-iit.ru/">Главная</a></li>
</ul></div>
</div>
        </div>
      
      <div id="center"><div id="squeeze"><div class="right-corner"><div class="left-corner">
                                                                                          <div class="clear-block">
            <div id="first-time">

<?php
if (isset($_SESSION['step_num'])) {
    $step_num = 0 + $_SESSION['step_num'];
} else {
    $step_num = 0;
}
   
class FormFields2 extends FormFields 
{
    public function __construct($action, $fid, $tdwidth, $border, $submit_name="Отправить", $charset="UTF-8", $method="post") {
        parent::__construct($action, $fid, $tdwidth, $border, $submit_name, $charset, $method);
    }

    public function __destruct() {
        if (!isset($_SESSION['step_num'])) {
	    $_SESSION['step_num'] = 0;
	}
        switch($_SESSION['step_num']) {
            case 0:
	        print "<input type=\"submit\" value=\"Я согласен\" class=\"submit\" /></div>";
                print "</form>\n";
                break;
            case 3:
	        print "</form>\n";
                break;
            case 2:
	        print "<tr><td align=\"center\" width=\"100%\">";
	        print "<input type=\"hidden\" name=\"act\" value=\"\" id=\"act\" />";
	        /* $fval = $msl->getarray("SELECT COUNT(id) AS cnt FROM !!!!! WHERE applicant_id='".$_SESSION['applicant_id']."'");
	        if ($fval['cnt'] < 2) {
	            print "<input type=\"button\" class=\"submit\" value=\"Подать заявление на еще одну специальность\" onclick=\"javascript: $('#act').val('add'); $('#formular').submit();\">"; 
	        } */
      	        print "<input type=\"button\" class=\"submit\" value=\"Перейти на следующий шаг\" onclick=\"javascript: $('#act').val(''); $('#formular').submit();\" /></td></tr></form>\n";
                break;
            default:
	        print "<tr><td align=\"center\" width=\"100%\"><input type=\"submit\" class=\"submit\" value=\"Отправить\">";
	        print "</td></tr></form>\n";
	}
    }
}

print '<h1 class="title">Шаг '.($step_num+1).' из 5</h1><div id="output"></div>';

if ($step_num == 0) {
   if (isset($_REQUEST['global_sid'])) {
       $_SESSION['global_sid'] = $_REQUEST['global_sid'];
   }

   print "<div id=\"myaccordion\">";

   $form = new FormFields2('insert.php','formular', 93, 0);

   print "<div><table style=\"display: block;\"><tbody style=\"border: none;\">"; 
   $form->tdBox( 'text', 'Фамилия',          'surname',  200, 60, 'K' ); 
   $form->tdBox( 'text', 'Имя',              'name',     200, 60, 'K' ); 
   $form->tdBox( 'text', 'Отчество (при наличии)',         'second_name', 200, 60, 0 ); 
   $form->tdBox( 'text', 'e-mail',           'e-mail',      200, 90, 'E' ); 

   print "</tbody></table></div>\n\n"; 
   print "<p><b>Для поступающих на второй и выше семестры:</b> в соответствии с требованиями Федерального закона <a href=\"http://www.mami.ru/pk/files/152-FZ.pdf\" target=\"_blank\">«О персональных данных» от 27.07.2006 №152-ФЗ</a>  даю согласие на сбор и обработку моих персональных данных (далее – ПД) на срок с момента подписания согласия до 31.12.2013 в необходимом для зачисления в «Университет машиностроения» объеме.</p>
<p><b>Адрес и наименование оператора, получающего разрешение на обработку ПД:</b> 107023, г. Москва, Б. Семеновская ул., д. 38; Государственное образовательное учреждение высшего профессионального образования «Московский государственный машиностроительный университет (МАМИ)».</p>
<p><b>Цель обработки ПД:</b> обеспечение соблюдения законов и иных нормативных правовых актов, обеспечении личной безопасности, обеспечение сохранности имущества оператора, Субъекта ПД и третьих лиц, статистические или иные научные цели при условии полного обезличивания ПД.</p>
<p><b>Перечень ПД, на обработку которых даю согласие:</b> фамилия, имя, отчество; пол; число, месяц и год рождения; место рождения; адрес; сведения об образовании; номера телефонов; реквизиты документа, удостоверяющего личность и гражданство; результаты ЕГЭ или вступительных испытаний; реквизиты документа об образовании; иные данные, предусмотренные законодательством РФ.</p>
<p><b>Перечень действий с ПД, на совершение которых даю согласие:</b> сбор, систематизация, накопление, распространение, хранение, уточнение, передача, обезличивание, блокирование, уничтожение.</p>
<p><b>Способы обработки ПД:</b> на бумажных носителях, с помощью информационной системы ПД.</p>
<p><b>Порядок отзыва согласия по инициативе Субъекта ПД:</b> субъект ПД в любой момент имеет право отозвать свое согласие в необходимом объеме на основании письменного заявления.</p>";
   unset($form);
}
if ($step_num == 1) {
   $form = new FormFields2('insert.php','formular', 180, 0);

   print "<P>Пожалуйста заполните следующие поля (поля отмеченные * обязательны для заполнения):</P>\n\n";
   print "<div id=\"myaccordion\">\n";   

   // ------ 1 ------
   print "<h3>Общие сведения</h3>";
   print "<div><table style=\"display: block;\"><TBODY style=\"border: none;\">"; 

   $form->tdRadio(   'Пол',              'sex',         array('M'=>'Мужской','F'=>'Женский'), 0, 1);
   $form->tdDateBox( 'Дата рождения',    'birthday',        1950, date('Y')-16, 'D' );

//   $rval = $msl->getarray("SELECT * FROM reg_citizenry");
//   foreach($rval as $key => $val) $tval[$val['id']] = $val['name'];
   
//   $form->tdSelect('Гражданство',      'citizenry',   $tval, 0, 1);
   $form->tdRadio(   'Гражданство',      'citizenry',   array('Российская Федерация'=>'Российская Федерация','other'=>'Другое'), 0, 1);

   print "</TBODY></table></div>\n\n";

   // ------ 2 ------
   print '<h3>Паспортные данные</h3>';
   print "<div><table style=\"display: block;\"><TBODY style=\"border: none;\">";

   print "<input type=\"hidden\" name=\"doc_type\" value=1>\n";
   $form->tdBox( 'text', array('Серия','Номер'), array('doc_serie','doc_number'),    array(45,70), array(4,6), array('N','N') ); 
   $form->tdBox( 'text', 'Кем выдан',         'doc_issued',  200, 200, 'A' ); 
   $form->tdBox( 'text', 'Код подразделения', 'doc_code',    100, 8, 'Okodp' ); 
   $form->tdDateBox( 'Дата выдачи',           'doc_date',    1990, date('Y'), 'D' );
   $form->tdBox( 'text', 'Место рождения',    'birthplace',  200, 100, 'A' ); 
  
   print "</TBODY></table></div>";

   // ------ 3 ------
   print "<h3>Адрес проживания</h3>";
   print "<div><table style=\"display: block;\"><TBODY style=\"border: none;\">"; 

   $form->tdBox( 'text', 'Почтовый индекс',  'homeaddress-index', 100, 6, 'N' ); 

   $aspec = $msl->getArrayById("SELECT id,CONCAT(id,' - ',name) as name FROM `reg_rf_subject` ORDER BY id ASC",'id','name');
   $form->tdSelect(  'Субъект РФ', 'homeaddress-region', $aspec, 77, 1);

   $form->tdBox( 'text', 'Населенный пункт',  'homeaddress-city', 200, 50, 'K' );
   $form->tdBox( 'text', 'Улица (квартал)',  'homeaddress-street', 200, 60, 0 );
   $form->tdBox( 'text', array('Дом','корпус','квартира'),  array('homeaddress-home','homeaddress-building','homeaddress-flat'), array(25,25,25), array(5,4,4), array('A',0,0) );

   print "<tr><TD colspan=\"2\"><LABEL><input type=\"checkbox\" id=\"regaddressashome\" name=\"regaddressashome\" value=\"1\" checked onclick=\"javascript: $('#regaddress_form').toggle();\">Совпадает с адресом регистрации.</LABEL>";
   print "</TBODY></table></div>";

   // ------ 3 ------
   print "<div id=\"regaddress_form\" style=\"display:none;\">";
   print "<h3>Адрес регистрации</h3>";
   print "<div><table style=\"display: block;\"><TBODY style=\"border: none;\">"; 

   $form->tdBox( 'text', 'Почтовый индекс',  'regaddress-index', 100, 6, 'ON' ); 

   $form->tdSelect(  'Субъект РФ', 'regaddress-region', $aspec, 77, 0);

   $form->tdBox( 'text', 'Населенный пункт',  'regaddress-city', 200, 50, 'OK' );
   $form->tdBox( 'text', 'Улица (квартал)',  'regaddress-street', 200, 60, 0 );
   $form->tdBox( 'text', array('Дом','корпус','квартира'),  array('regaddress-home','regaddress-building','regaddress-flat'), array(25,25,25), array(5,4,4), array(0,0,0) );
   print "</TBODY></table></div></div>";

   // ------ 4   ------
   print "<h3>Контактные данные</h3>";
   print "<div><table style=\"display: block;\"><TBODY style=\"border: none;\">"; 

   $form->tdBox( 'phone', 'Домашний телефон',         'homephone', array(40,70), array(5,10), 1 );
   $form->tdBox( 'phone', 'Мобильный телефон',        'mobile',    array(40,70), array(3,7), 1 );
   
   print "</TBODY></table></div>";

   
   
   // ------ 5 ------
   print "<h3>Сведения об имеющемся образовании</h3>";
   print "<div><table style=\"display: block;\"><TBODY style=\"border: none;\">"; 
   $kval = $msl->getarrayById("SELECT id, name FROM reg_education", 'id','name');
   $form->tdSelect(   'Тип учебного заведения', 'edu_base', $kval, 0, 1);

if (0) {
   $form->tdRadio(   'Тип документа об образовании', 'edudoc_type',  array('1'=>'аттестат','2'=>'диплом'), 0, 1);
   $form->tdBox( 'text', array('Серия','№'),  array('edudoc[serie]','edudoc[number]'), array(45,65), array(10,10), array('A','N') );
   $form->tdDateBox( 'Дата выдачи',           'edudoc_date',    1990, date('Y'), 'D' );
   $form->tdRadio(   'Подаю копию', 'edudoc_copy',  array('1'=>'да','0'=>'нет'), 1, 1);
}   

   $sval = $msl->getarrayById("SELECT id, name FROM reg_flang",'id','name');
   $form->tdRadio(   'Изучаемый иностранный язык', 'language', $sval, 0, 1);

   $form->tdRadio(   'Высшее образование получаю', 'highedu',  array('0'=>'впервые','1'=>'не впервые'), 0, 1);
   print "</TBODY></table></div>\n";


   $form->hidden('region', '1'); // id региона статичное (1 - интернет)

   print "</TBODY></table></div>\n";
   unset($form);
   print "</div>";

   
}

if ($step_num == 3) {
   print "<P>Пожалуйста загрузите документы об образовании (поля отмеченные * обязательны для заполнения):</P>";
   print "<script type=\"text/javascript\">
$(document).ready(function(){
	var button = $('#button1'), interval;
	
new AjaxUpload(button,
{
action: 'upload.php',
onSubmit : function(file, ext){
   if ( $('#edit-docserie').val().length == 0) {jAlert('Не введена Серия документа!'); return false;}
   if ( $('#edit-docnumber').val().length == 0) {jAlert('Не введен Номер документа!'); return false;}
   if ( $('#docdate').val().length == 0) {jAlert('Не выбрана Дата выдачи документа!'); return false;}
   if ( $('#edit-docinstitution').val().length == 0) {jAlert('Не введено наименование учреждения!'); return false;}

   $.blockUI({ message: 'Добавление файла...' })

   if (ext && /^(jpg|png|jpeg|gif|tiff|bmp)$/.test(ext)){
				this.setData({
'doc_serie': $('#edit-docserie').val(),
'doc_number': $('#edit-docnumber').val(),
'doc_date': $('#docdate').val(),
'doc_institution': $('#edit-docinstitution').val(),
'doc_specialty': $('#edit-docspecialty').val(),
'doc_id': $('#doctype').val(),";
print "'applicant_id': '".$_SESSION['applicant_id']."',";
print "'applicant_hash': '".md5($CFG_salted.$_SESSION['applicant_id'])."',";
print "				});
   } else {
      $.unblockUI();
      jAlert('Ошибка. Допускаются только картинки.');
      return false;				
   } 
},
onComplete: function(file, response){
   $.unblockUI();  
   response = response.replace(/\s+/, '');

   if (response === \"success\") {			
      $('#filemode').show();
      $('#uploader .files').append('<LI>' + $('#doctype option:selected').text() + ' ' + $('#edit-docserie').val() +' № ' + $('#edit-docnumber').val() + ' (' + file + ').</LI>');
      $('#fileupload').clearForm();
   } else {
      jAlert('Ошибка добавления файла.');
   }			
}
	});
});

function nextStep() {
   if ($('#uploader .files').children().size() == 0) {
      jAlert('Не добавлено ни одного документа.');
      return false;  
   }
   $.ajax({url: 'insert.php'}); 
}
</script>";

        
   $form = new FormFields2('insert.php','fileupload', 250, 0);
   print "<div><table style=\"border: 0px;\"><TBODY style=\"border: 0px;\">\n";
   
   $bdoc = $msl->getarrayById("SELECT id,name FROM `reg_edu_doc`",'id','name');
   $form->tdSelect(  'Тип загружаемого документа', 'doctype', $bdoc, 0, 1);

   $form->tdBox( 'text', array('Серия','№'),  array('docserie','docnumber'), array(45,65), array(10,10), array('A','N') );
   $form->tdDateBox( 'Дата выдачи',           'docdate',    1990, date('Y'), 'D' );
   $form->tdBox( 'text', 'Наименование учреждения, выдавшего документ',  'docinstitution', 150, 300, 'A' );
   $form->tdBox( 'text', 'Специальность',  'docspecialty', 150, 60, 0 );
   
   print "<tr><td><input type=\"button\" id=\"button1\" class=\"button\" value=\"Загрузить файлы\"></td></tr>";
   print "<tr><TD colspan=2><div id=\"filemode\"";
   
   $rval = $msl->getarray("SELECT name,serie,number,date,filename FROM `reg_applicant_edu_doc` a LEFT JOIN `reg_edu_doc` b ON a.edu_doc = b.id WHERE a.applicant = '".$_SESSION['applicant_id']."'", 1);
   if ($rval == 0) print " style=\"display: none;\"";
   print "><P>Загруженные файлы:</P><div id=\"uploader\"><OL class=\"files\">";

   if (is_array($rval)) {
      foreach ($rval as $key=>$val) {
         print "<LI>".$val['name']." ".$val['serie']." № ".$val['number']." (".$val['filename'].").</LI>\n";
      }
   }

   print "</OL></div></div></td></tr>";
   print "<tr><TD colspan=2><input type=\"button\" value=\"Перейти на следующий шаг\" onclick=\"nextStep();\"></td></tr>";
   print "</TBODY></table></div>";
   unset($form);
}

if ($step_num == 2) {
   $form = new FormFields2('insert.php','formular', 210, 0);

   print "<P>Пожалуйста заполните следующие поля (поля отмеченные * обязательны для заполнения):</P>\n\n";
   print "<div id=\"myaccordion\">\n";   
   print "<h3>Выбор образовательной программы</h3>";
   print "<div><table style=\"display: block;\"><TBODY style=\"border: none;\">"; 

$cat = new Catalog(&$msl);

//$bval = $cat->getAvailableByPgid(1);
$bval = $cat->getAvailableSpecialtiesByPgid(1);
$form->tdSelect(  'Выбранная образовательная программа', 'catalog', $bval, (isset($_SESSION['global_sid']) ? $_SESSION['global_sid'] : 0), 1);
   
print "</TBODY></table></div>";


print "<div id=\"ege_fields\">";

if (isset($_SESSION['global_sid'])) {
    $_POST['catalog']=$_SESSION['global_sid'];
} else {
    foreach($bval as $key=>$val) {
        if (!is_null($val)) break;
    }
    $_POST['catalog']=$key;
}
$_POST['width']=210;

require('get.php'); 
 print "</div>\n\n";

print "</div>";

   unset($form);
   print "</div>\n\n";

   print "<P>Вы можете в любой момент проверить состояние дел по выбранной вами образовательной программе, выполнив вход в систему. Для входа используется адрес электронной почты и данные паспорта (серия и номер слитно).</p>";   
}

if ($step_num == 4) {
    print "<P>Распечатайте и подпишите следующие документы:</P>";
    print "<div id=\"myaccordion\">\n";   

    $appl = new Applicant($msl, $_SESSION['applicant_id']);
    $cat  = new Catalog(&$msl);

    $spc = $cat->getInfo($appl->catalog);
    
    print "<h3>Комплект документов для зачисления на ".$spc['type']." ".$spc['spec_code']." \"".$spc['name']."\"</h3>";
    print "<div><table style=\"display: block;\"><TBODY style=\"border: none;\">"; 
   
    switch ($appl->type){
    case 1:
	print "<tr><TD style=\"font-weight: bold; color: #ff0000;\">Внимание! Во всех документах даты не ставить!</td></tr>";
      	$appl->printDocs('',1); 

	print "<tr><td>Для просмотра документов вам потребуется <A href=\"http://get.adobe.com/reader/\">Adobe&copy; Reader</A>.</td></tr>";
	break;

    default:
	print "<tr><td>Ваши документы для поступления находятся на рассмотрении. После рассмотрения документов, Вы сможете распечатать с личного кабинета необходимые документы. Для входа в систему используйте адрес электронной почты в качестве логина и в качестве пароля серию и номер паспорта, написанные слитно. О результатах рассмотрения документов Вы будете уведомлены с помощью сообщения на ваш e-mail.</td></tr>";
    }
    
    unset($cat);
   
    print "</TBODY></table></div>\n\n";
    print "</div>";
}

print '<p>Если у Вас в процессе заполнения формы появились вопросы, свяжитесь с нашими сотрудниками по телефонам: +7 (499) 1277453, +7 (499) 1277496 или <a href="mailto:iit@ins-iit.ru">по электронной почте</a>.</p></div></div>';
?>

<div id="footer">© 2009-2013, ins-iit.ru Team<div id="block-system-0" class="clear-block block block-system">

<!--<div id="dialog_validate" class="myDialog">Проверьте правильность заполнения всех полей формы и нажмите "Отправить".</div>-->



</div>
</div>
      </div></div></div></div> <!-- /.left-corner, /.right-corner, /#squeeze, /#center -->

      
    </div> <!-- /container -->
  </div>
<!-- /layout -->
<!-- Google Code for &#1048;&#1085;&#1090;&#1077;&#1088;&#1085;&#1077;&#1090;-&#1088;&#1077;&#1075;&#1080;&#1089;&#1090;&#1088;&#1072;&#1094;&#1080;&#1103; Conversion Page -->
<script type="text/javascript">
/* <![CDATA[ */
var google_conversion_id = 1015600493;
var google_conversion_language = "ru";
var google_conversion_format = "2";
var google_conversion_color = "ffffff";
var google_conversion_label = "P5GUCJPGiAMQ7aqj5AM";
var google_conversion_value = 0;
if (300) {
  google_conversion_value = 300;
}
/* ]]> */
</script>
<script type="text/javascript" src="http://www.googleadservices.com/pagead/conversion.js">
</script>
<noscript>
<div style="display:inline;">
<img height="1" width="1" style="border-style:none;" alt="" src="http://www.googleadservices.com/pagead/conversion/1015600493/?value=300&amp;label=P5GUCJPGiAMQ7aqj5AM&amp;guid=ON&amp;script=0"/>
</div>
</noscript>

</body></html>
