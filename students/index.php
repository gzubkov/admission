<?php
require_once('../../conf.php');
require_once('../../../modules/mysql.php');
require_once('../class/forms.class.php');
require_once('../class/catalog.class.php');
require_once('../class/price.class.php');

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html class="js" dir="ltr" xml:lang="en" xmlns="http://www.w3.org/1999/xhtml" lang="en"><head>

<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
 
  
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Личный кабинет студента</title>

<link type="text/css" rel="stylesheet" media="all" href="../images/defaults.css">
<link type="text/css" rel="stylesheet" media="all" href="../images/system.css">
<link type="text/css" rel="stylesheet" media="all" href="../images/style.css">
<link type="text/css" rel="stylesheet" media="all" href="../css/custom-theme/jquery-ui-1.8.custom.css">	


<link href="css/jquery.alerts.css" rel="stylesheet" type="text/css" media="screen" />

<!-- Validation -->
<link rel="stylesheet" href="css/validationEngine.jquery.css" type="text/css" media="screen" title="no title" charset="utf-8" />
</head>
<body class="sidebar-left">

<SCRIPT type="text/javascript" src="../js/jquery-1.4.2.min.js"></script>
<!-- jQuery UI -->
<SCRIPT type="text/javascript" src="../js/jquery-ui-1.8.custom.min.js"></script>
<SCRIPT type="text/javascript" src="../js/jquery.ui.datepicker-ru.js"></script>
<SCRIPT type="text/javascript" src="../js/jquery.form.js"></SCRIPT>
<SCRIPT type="text/javascript" src="../js/jquery.blockUI.js"></SCRIPT>
<SCRIPT type="text/javascript">
	$(function() {
		     $("input:submit, input:button").button();
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
	 case "wrongpwd":
	    alert('Номер паспорта абитуриента указан неверно');
	    break;
	 default:
            alert('При передаче формы возникла ошибка. Пожалуйста попробуйте еще раз');
      }     
   },
   error: function(msg) {
      alert('При обработке формы возникла ошибка. Пожалуйста попробуйте еще раз');
   }
});


</SCRIPT>
<!-- Layout -->
  <div id="header-region" class="clear-block"></div>

    <div id="wrapper">
    <div id="container" class="clear-block">

      <div id="header">
        <div id="logo-floater">
        <h1><IMG src="../images/ckt.png" style="width: 170px;"><span>Личный кабинет студента</span></h1>        </div>

                                                    
      </div> <!-- /header -->

              <div id="sidebar-left" class="sidebar">
<?php 
class Student{
    public function isLogin() {
        if (isset($_SESSION['student_id'])) {
            if (is_numeric($_SESSION['student_id'])) {
	        return true;
	    } 
	} 
        return false;
    }
}

$student = new Student();
if ($student->isLogin()) {
?>
                    <div id="block-user-0" class="clear-block block block-user"><h2>Студент</h2>
<?php

$student_id = $_SESSION['student_id'];

    $r = getarray("SELECT surname, name, second_name, region, catalog, semestr FROM `students_base`.student WHERE id='".$student_id."' LIMIT 1",0);
    $spec = getarray("SELECT f.abbreviation, b.name FROM admission.catalogs a 
                  LEFT JOIN admission.specialties b ON a.specialty=b.id 
                  LEFT JOIN admission.`universities_departments` c ON b.department=c.id 
                  LEFT JOIN admission.`universities_faculties` d ON c.faculty=d.id 
		  LEFT JOIN admission.`universities` f ON d.university=f.id 		  
                  WHERE a.base_id='".$r['catalog']."'");
    print $r['surname']." ".$r['name']." ".$r['second_name'];

print "<H2>Регион</H2>";

switch($r['region']) 
{
    case '1':
        print "Москва";
	break;

    case '176':
        print "Индивидуалы";
	break;

    default:
        $reg = getarray("SELECT name,physicaladdress,inn FROM admission.partner_regions WHERE id='".$r['region']."'");
        print $reg['name'];
}

print "<H2>Специальность</H2>";
print $spec['name'];
?>


        </div><INPUT type="submit" onclick="javascript: $.ajax({url: 'login.php', data: 'act=exit'});" value="Выйти из системы"> 
<?php } else { 
print "<DIV id=\"block-user-0\" class=\"clear-block block block-user\"><h2>Вход в систему</h2>\n";

print "<div class=\"form-item\" id=\"edit-name-wrapper\">";
print "<label for=\"edit-name\">Номер договора:</label>
          <input maxlength=\"5\" name=\"num\" id=\"num\" size=\"15\" type=\"text\">"; //</div>";
   print "<div class=\"form-item\" id=\"edit-name-wrapper\">";
   print "<label for=\"edit-pass\">Номер паспорта:</label>
          <input name=\"pass\" id=\"pass\" maxlength=\"10\" size=\"15\" type=\"text\"></div>";
?>
</div>
<INPUT type="submit" onclick="javascript: $.ajax({url: 'login.php', data: 'pass='+$('#pass').val()+'&num='+$('#num').val()});" value="Войти в систему">
</div>
<?php } ?>

      </div>
      <div id="center"><div id="squeeze"><div class="right-corner"><div class="left-corner">
                                                                                          <div class="clear-block">
            <div id="first-time">

<?php



    //print "<H1 class=\"title\">Главная страница</H1><DIV id=\"output\"></DIV>";

    print "<P></P>\n\n"; //Пожалуйста заполните следующие поля (поля отмеченные * обязательны для заполнения):</P>\n\n";
    print "<DIV id=\"myaccordion\">";

    

    
if ($student->isLogin()) {
    print "<h3>Добро пожаловать в Личный кабинет студента</h3>";
    print "<P>Для просмотра документов в формате PDF вам потребуется <A href=\"http://get.adobe.com/reader/\">Adobe&copy; Reader</A>.</P>";
   
    print "<h3>Сводная ведомость успеваемости</h3>";
    print "<P>Для получения сводной ведомости успеваемости выберите формат.</P>";
    print "<INPUT type=\"button\" value=\"В формате HTML\" onclick=\"javascript: window.location.href='journal_html.php';\"><INPUT type=\"button\" value=\"В формате PDF\" onclick=\"javascript: window.location.href='journal.php';\"><BR><BR>";
   
    print "<h3>Квитанция на оплату</h3>";
    print "<P>Для формирования квитанции необходимо указать назначение платежа и количество (в случае, если оплачиваются пересдачи). Затем нажмите \"Распечатать квитанцию\". Полученный pdf-документ, содержащий квитанцию со всеми реквизитами, можно распечатать или сохранить.</P>";

    $price = new Price();
    $pdate = $price->getDateByStudent($student_id);
    $sessions = $price->getSessions();
    unset($price);

    $days = floor((strtotime($pdate['date_end'])-mktime())/84600);
    if ($days < 80) {
        print "<P style=\"color: #ff0000; font-weight: bold;\">Внимание! Расценки действительны только до ".date('d.m.Y',strtotime($pdate['date_end'])).". Дальнейшая стоимость услуг может изменяться.</P>";
    }

    $form = new FormFields('../receipt/kvit.php','formular', 180, 0, "Распечатать квитанцию");

    print "<DIV><TABLE style=\"display: block;\"><TBODY style=\"border: none;\">"; 
    
    $rval = getarrayById("SELECT id,text FROM `receipt_purpose` WHERE `student`='1'", 'id', 'text');
    foreach($rval as $k=>$v) {
        $replace = array("%dn%" => $_SESSION['student_id'],
	                 "%s%" => $r['semestr']+1);
	$rval[$k] = strtr($v, $replace);
    }
    $form->tdSelect(  'Назначение платежа', 'purpose', $rval, 0, 1);

    $form->tdSelect(  'Сессия', 'date', $sessions, 0, 1);

    $form->tdBox( 'text', 'Количество пересдач',        'count', 20, 2, 'O', 1 ); 
    $form->hidden( 'student', $student_id );

    unset($form);
    print "</TBODY></TABLE></DIV>\n\n";

    print "<h3>Координаты регионального партнера</h3>";
    print "<DIV><TABLE style=\"display: block;\"><TBODY style=\"border: none;\">"; 
    if ($r['region'] == 1 || $r['region'] == 176) {
        print "<TR><TD>Адрес:</TD><TD>";
        print "117152, г.Москва, Загородное шоссе, д.7, корп.5, стр.1.</TD></TR><TR><TD>Телефон:</TD><TD>+7 (495) 663-1562, +7 (495) 663-1505.</TD></TR>";
    } else {
        if ($reg['inn'] != 0) {
	    print "<TR><TD>Адрес:</TD><TD>";
            print $reg['physicaladdress'].".</TD></TR>";
	} else {
	    print "<TR><TD colspan=2><FONT color=red><B>Ваш региональный представитель не предоставил данные.</B></FONT> В связи с этим, при печати квитанции не будут указаны реквезиты регионального партнера.</TD></TR>";
	}
    }
// добавить телефон и факс!
    print "</TBODY></TABLE></DIV>\n\n";

} else {
    print "<P>Уважаемые студенты! Для Вашего удобства существует возможность из личного кабинета получить сводную ведомость успеваемости и распечатать квитанцию на оплату различных услуг.</P>";

    print "<P>Для входа в личный кабинет введите номер договора и номер Вашего паспорта.</P>";
}



print '<P>Если у Вас появились вопросы, свяжитесь с нашими сотрудниками по телефонам (добавочный 10): +7 (495) 663-1562, +7 (495) 663-1505 или <A href="mailto:iit@ins-iit.ru">по электронной почте</A>.</p></div></div>';
                    
?>

<div id="footer">© 2009-2011, ins-iit.ru Team<div id="block-system-0" class="clear-block block block-system">

<!--<div id="dialog_validate" class="myDialog">Проверьте правильность заполнения всех полей формы и нажмите "Отправить".</div>-->



</div>
</div>
      </div></div></div></div> <!-- /.left-corner, /.right-corner, /#squeeze, /#center -->

      
    </div> <!-- /container -->
  </div>
<!-- /layout -->

</body></html>
