<?php
require_once('../../conf.php');
require_once('../../../modules/mysql.php');
require_once('../class/forms.class.php');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html dir="ltr" xml:lang="en" xmlns="http://www.w3.org/1999/xhtml" lang="en"><head>

<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
 
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Поступление - Работа с абитуриентами</title>

<link type="text/css" rel="stylesheet" media="all" href="../images/defaults.css" />
<link type="text/css" rel="stylesheet" media="all" href="../images/system.css" />
<link type="text/css" rel="stylesheet" media="all" href="../images/style.css" />
<link type="text/css" rel="stylesheet" media="all" href="../css/smoothness/jquery-ui-1.8.7.custom.css" />	
<link type="text/css" rel="stylesheet" media="all" href="../css/datatables.css" />	

<style type="text/css">
   .dataTables_info { padding: 0px; }
   .dataTables_paginate { padding: 8px; }
</style>


<script type="text/javascript" src="../js/jquery-1.4.4.min.js"></script>
<script type="text/javascript" src="../js/jquery-ui-1.8.7.custom.min.js"></script>

<script type="text/javascript" src="../js/jquery.ui.datepicker-ru.js"></script>
<script type="text/javascript" src="../js/jquery.dataTables.js"></script>
<script type="text/javascript" src="../js/jquery.blockUI.js"></script>
<script type="text/javascript" src="../js/jquery.alerts.js"></script>
<script type="text/javascript" src="../js/FixedHeader.min.js"></script>
<script type="text/javascript">

var oTable;
var asInitVals = new Array();
			
$(document).ready(function() {
   $("input:submit, input:button").button();
   $.datepicker.setDefaults({changeMonth: true, changeYear: true});
   $("#dialog").dialog("destroy");
   $("#dialog-message").dialog(
     {autoOpen: false,
      modal: true,
      width: 600,		
      resizable: false
   });
   
   $("#dialog-message2").dialog(
     {autoOpen: false,
      modal: true,
      width: 600,		
      resizable: false
   });

   $("#selectAll").live('click',function() {
        var checked_status = this.checked;
        $("input[name=selectAppl]").each(function() {
            this.checked = checked_status;
        });
    }); 

   $("#deleteSelected").live('click', function() {
       $.blockUI({ message: $('#deleteall'), css: { width: '275px', height: '105px' }}); 
   });

   $('#deleteall .yes').click(function() { 
      $.blockUI({ message: 'Удаление абитуриентов...' }); 
      $("input[name=selectAppl]:checked").each(function() {
         $.ajax({url: 'get.php', type: 'POST', data:'act=deleteapplicant&id='+$(this).val()});
      });
      window.location.reload();
      $.unblockUI(); 
   }); 

   $('#deleteall .no').click(function() { 
      $.unblockUI(); 
      return false; 
   }); 
   

   oTable = $('#example').dataTable( {
      "bJQueryUI": true,
      "bStateSave": true,
      "sPaginationType": "full_numbers",
      "aaSorting": [[1, 'desc']],
      "aoColumns": [ 
         {"bSearchable": false,
  	  "bSortable": false},
	 null, null, null,
	 null, null,
	 {"bSearchable": false,
  	  "bSortable": false},
	 {"bSearchable": false,
  	  "bSortable": false}],
      "oLanguage": {
	"sProcessing":   "Подождите...",
	"sLengthMenu":   "Показать _MENU_ записей",
	"sZeroRecords":  "Записи отсутствуют.",
	"sInfo":         "Записи с _START_ до _END_ из _TOTAL_ записей",
	"sInfoEmpty":    "Записи по указанному фильтру отсутствуют",
	"sInfoFiltered": "(выбрано из _MAX_ записей)",
	"sInfoPostFix":  "",
	"sSearch":       "Поиск:",
	"sUrl":          "",
	"oPaginate": {
		"sFirst": " Первая ",
		"sPrevious": " Предыдущая ",
		"sNext": " Следующая ",
		"sLast": " Последняя "
	},
	"sSearch": "Поиск по всем полям:"}
				} );
				
   $('#example tbody tr td:not([id^=check])').live( 'click', function () {
      var parentr = $(this).parent('tr');
      $.ajax({url: 'get.php', type: 'POST', data: 'act=getspecialties&id='+parentr.find('td:eq(1)').html(),
              success: function(msg){
	        $('#dialog-message').html(msg)
                                    .dialog( "option", "title", parentr.find('td:eq(2)').html()+' '+parentr.find('td:eq(3)').html()+' '+parentr.find('td:eq(4)').html()+' ('+parentr.find('td:eq(1)').html()+') - образовательные программы' )
                                    .dialog('open');
	     }});       
});





   $("thead input").keyup( function () {
      oTable.fnFilter( this.value, $("thead input").index(this) );
   } );
				
				
				
				/*
				 * Support functions to provide a little bit of 'user friendlyness' to the textboxes in 
				 * the footer
				 */
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
			} );

function loginA() {
   $.ajax({url: 'get.php', type: 'POST', data: 'act=login&'+$('#login').serialize(),
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
		    alert('Фигвам!');
	      }
	   }});     
}

function unloginA() {
   $.ajax({url: 'get.php', type: 'POST', data: 'act=unlogin',
           success: function(msg){
	      msg = msg.replace(/\s+/, '');
	      switch (msg) {
	         case "ok":
		    window.location.reload();
		    break;
		 default:
		    alert('Фигвам!');
	      }
	   }});     
}

</script>


</head>
<body class="sidebar-left">


<?php

if (isset($_SESSION['rights']) && $_SESSION['rights'] == 'admin' && $_SESSION['md_rights'] == md5($CFG_salted.$_SESSION['rights'])) {
   print "<A onclick=\"unloginA();\">Выйти</A>\n";

   print "<div style=\"border: 1px solid #d3d3d3; background-color: #ffffff; width: 98%; margin:0 auto;\">";
   print "<P><b><CENTER>Работа с абитуриентами</CENTER></b></P>\n";

   print "<div style=\"border: none; width: 98%; margin:0 auto;\">";

   $mappl = getarray("SELECT id, num, surname, name, second_name, `e-mail`, `create_date`, step, INET_NTOA(ip) as ip FROM `reg_applicant` ORDER by id DESC");
   print "<table border=0 cellspacing=0 cellpadding=0 id=example class=display>";

   print "<THEAD>
              <tr>
	       <TH style=\"text-align: center; \"><input type=\"checkbox\" id=\"selectAll\"></TH>
               <TH style=\"text-align: center; \"><input type=\"text\" name=\"search_id\" value=\"id\" style=\"width: 35px;\" class=\"search_init\"/></TH>
               <TH><input type=\"text\" name=\"search_surname\" value=\"Фамилия\" class=\"search_init\"/></TH>
               <TH><input type=\"text\" name=\"search_name\" value=\"Имя\" class=\"search_init\"/></TH>
               <TH><input type=\"text\" name=\"search_secondname\" value=\"Отчество\" class=\"search_init\"/></TH>
               <TH><input type=\"text\" name=\"search_email\" value=\"e-mail\" class=\"search_init\"/></TH>
               <TH>Время регистрации</TH>
               <TH>Шаг</TH>
              </tr>
          </THEAD>";
   print "<tbody>";
   
   foreach($mappl as $key => $r) {
      print "<tr><td style=\"width: 1px; text-align: center; \" id=\"check".$key."\">";
      print "<input type=\"checkbox\" id=\"selectAppl[]\" name=\"selectAppl\" value=\"".$r['id']."\"></td>";
      print "<td style=\"width: 60px; text-align: center; ".(($r['num'] > 0) ? "text-decoration: line-through;":"")."\">".$r['id']."</td><td>".$r['surname']."</td><td>".$r['name']."</td><td>".$r['second_name']."</td>";
      print "<td>".$r['e-mail']."</td><td>".date('d-m H:i',strtotime($r['create_date']))." <IMG src=\"../images/clients.png\" style=\"height: 20px; vertical-align: bottom\" title=\"".$r['ip']."\"></td>\n";
      print "<td style=\"width: 15px; text-align: center; \">".$r['step']."</td></tr>";
   }
   print "</tbody>";
   print "</table><br /><input type=\"button\" id=\"deleteSelected\" value=\"Удалить выбранных\"></div><br />";
   print "</div>";

   print "<div id=\"dialog-message\"></div>
   	  <div id=\"dialog-message2\">";


print "<script language=javascript>
$('#dialog-message2form').submit(function() {
$.ajax({url: 'get.php', type: 'POST', data: $(this).serialize(), beforeSend: function() {\$.blockUI({ centerY: 0, css: { top: '10px', left: '', right: '10px' }, message: 'Ваш запрос обрабатывается...' })}, success: function(msg) {\$.unblockUI(); $('#dialog-message2').dialog('close'); if (msg != 1){alert(msg)};}})
return false;
});
</script>";
$form = new FormFields('get.php','dialog-message2form', 250, 0, 'Добавить документ');
   print "<div><table style=\"border: 0px;\"><tbody style=\"border: 0px;\">\n";
   $msl = new dMysql();
   $bdoc = $msl->getarrayById("SELECT id,name FROM `reg_edu_doc`",'id','name');

   $form->hidden('act', 'attachdoc');
   $form->hidden('aid',  '');
   $form->tdSelect(  'Документ', 'doctype', $bdoc, 0, 1);

   $form->tdBox( 'text', array('Серия','№'),  array('docserie','docnumber'), array(45,65), array(10,10), array('A','N') );
   $form->tdDateBox( 'Дата выдачи',           'docdate',    1990, date('Y'), 'D' );
   $form->tdBox( 'text', 'Кем выдан',  'docinstitution', 150, 300, 'A' );
   $form->tdBox( 'text', 'Специальность',  'docspecialty', 150, 60, 0 );
/*
print "<A onclick=\"$.ajax({url: 'get.php', type: 'POST', data:'act=attachDoc&aid=".$id."&doctype='+$('#doctype option:selected').val()+'$docserie='+$('#docserie').val()+'&catalog='+$('#catalog".$val['id']." option:selected').val(), beforeSend: function() {\$.blockUI({ centerY: 0, css: { top: '10px', left: '', right: '10px' }, message: 'Ваш запрос обрабатывается...' })}, success: function(msg) {\$.unblockUI(); $('#dialog-message').dialog('close'); if (msg != 1){alert(msg)}}})\">Добавить документ</A>"; */
print "</div>";

/*   print "<br /><HR><br /><A onclick=\"javascript: $('#editsettings').toggle();\">Изменить настройки</A>";
   print "<div id=\"editsettings\" style=\"display: none;\">";

   print "Дата подачи оригиналов: <input type=text name=dateorig><br />";
   print "<input type=button value=\"Сохранить\">";
   print "</div>";
*/
   print "<div id=\"deleteall\" style=\"display:none; cursor: default; width: 275px; height: 125px; align: center;\"> 
        <p>Вы действительно хотите удалить абитуриентов?</p> 
        <input type=\"button\" class=\"yes\" value=\"Да\" /> 
        <input type=\"button\" class=\"no\" value=\"Нет\" /></div> ";

} else {
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


?>
</body></html>

