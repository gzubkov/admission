<?php
require_once('../../conf.php');
require_once('../class/mysql.class.php');
require_once('../class/mssql.class.php');
require_once('../class/forms.class.php');
require_once('../class/catalog.class.php');

/*
if ($_SESSION['rights'] == 'admin' && $_SESSION['md_rights'] == md5($CFG_salted.$_SESSION['rights'])) {
    if ($_REQUEST['region'] > 0) $_SESSION['joomlaregion'] = $_REQUEST['region'];
}
*/
//if ($_SESSION['joomlaregion'] == 0) exit(0); 
$university_id = 1;

$msl = new dMysql();
$mssql = new dMssql();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html class="js" dir="ltr" xml:lang="en" xmlns="http://www.w3.org/1999/xhtml" lang="en"><head>

<!--<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>-->
 
  
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Личный кабинет регионального партнера</title>
<link type="text/css" rel="stylesheet" media="all" href="../images/defaults.css">
<link type="text/css" rel="stylesheet" media="all" href="../images/system.css">
<link type="text/css" rel="stylesheet" media="all" href="../images/style.css">
<link type="text/css" rel="stylesheet" media="all" href="../css/smoothness/jquery-ui-1.8.7.custom.css">	
<link type="text/css" rel="stylesheet" media="all" href="../css/datatables.css">	

<style type="text/css">
   .dataTables_info { padding: 0px; }
   .dataTables_paginate { padding: 8px; }
</style>


<SCRIPT type="text/javascript" src="../js/jquery-1.4.4.min.js"></script>
<!-- jQuery UI -->
<SCRIPT type="text/javascript" src="../js/jquery-ui-1.8.7.custom.min.js"></script>

<SCRIPT type="text/javascript" src="../js/jquery.ui.datepicker-ru.js"></script>
<SCRIPT type="text/javascript" src="../js/jquery.dataTables.js"></SCRIPT>
<SCRIPT type="text/javascript" src="../js/jquery.blockUI.js"></SCRIPT>
<SCRIPT type="text/javascript" src="../js/jquery.alerts.js"></SCRIPT>
<SCRIPT type="text/javascript" src="../js/FixedHeader.min.js"></SCRIPT>
<SCRIPT type="text/javascript" charset="utf-8">

var oTable;
var asInitVals = new Array();
			
$(document).ready(function() {
   $("input:submit, input:button").button();
   $("#dialog").dialog("destroy");
   $("#dialog-message").dialog(
     {autoOpen: false,
      modal: true,
      width: 600, resizable: false });

   oTable = $('#example').dataTable( {
      "bJQueryUI": true,
"sDom": 'rtip<"clear">',
"iDisplayLength": 20,
      "sPaginationType": "full_numbers",
      "aaSorting": [[0, 'asc']],
      "aoColumns": [ 
         null, null, {"bSearchable": false,
  	  "bSortable": false},{"bSearchable": false,
  	  "bSortable": false}], 
      "oLanguage": {
	"sProcessing":   "Подождите...",
	"sLengthMenu":   "",
	"sZeroRecords":  "Записи отсутствуют.",
	"sInfo":         "Записи с _START_ по _END_ из _TOTAL_ записей",
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
				
   $('#example tbody tr td').live( 'click', function () {
      var parentr = $(this).parent('tr');
      $.ajax({url: 'student.php', type: 'POST', data: 'id='+parentr.find('td:eq(0)').html(),
              success: function(msg){
	        $('#dialog-message').html(msg)
                                    .dialog( "option", "title", parentr.find('td:eq(1)').html()+' '+parentr.find('td:eq(2)').html()+' '+parentr.find('td:eq(3)').html()+' ('+parentr.find('td:eq(0)').html()+')' )
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
   $.ajax({url: '../admin/get.php', type: 'POST', data: 'act=login&'+$('#login').serialize(),
           success: function(msg){
	      msg = msg.replace(/\s+/, '');
	      switch (msg) {
	         case "university":
		    window.location.reload();
		    break;
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
   $.ajax({url: '../admin/get.php', type: 'POST', data: 'act=unlogin',
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


</HEAD>
<BODY class="sidebar-left">

<?php
if ($_SESSION['university'] < 1) {
print "<BR><DIV style=\"border: 1px solid #d3d3d3; width: 250px; height: 140px; background-color: #ffffff; margin:0 auto;\"><FORM id=\"login\">\n";
   print "<TABLE border=0><TBODY style=\"border: none;\">";
   print "<TR><TD colspan=2 style=\"text-align: center;\"><B>Вход в систему</B></TD></TR>";
   print "<TR><TD style=\"width: 70px;\">Логин:</TD>";
   print "<TD><INPUT type=\"text\" name=\"login\">.</TD></TR>";
   print "<TR><TD style=\"width: 70px;\">Пароль:</TD>";
   print "<TD><INPUT type=\"password\" name=\"password\">.</TD></TR>";
   print "<TR><TD colspan=2 style=\"text-align: center;\"><INPUT type=\"submit\" value=\"Войти\" onclick=\"javascript: loginA(); return false;\"></TD></TR>";
   print "</TBODY></TABLE>";   
   print "</FORM></DIV>";
   exit(0);
}
?> 
<!-- Layout -->
  <div id="header-region" class="clear-block"></div>

    <div id="wrapper">
    <div id="container" class="clear-block">

      <div id="header">
        <div id="logo-floater">
        <h1><IMG src="../images/ckt.png" style="width: 170px;"><span>Личный кабинет представителя ВУЗа</span></h1>        </div>

                                                    
      </div> <!-- /header -->

              <div id="sidebar-left" class="sidebar">
                    <div id="block-user-0" class="clear-block block block-user"><h2>Учебное заведение</h2>
  <div class="content">

<?php
    $rval = $msl->getarray("SELECT type,abbreviation FROM `universities` WHERE id='".$university_id."'");
    print "<div class=\"form-item\">".$rval['type']." ".$rval['abbreviation']."</div>";
?>
</div>

</div>
<div id="block-user-1" class="clear-block block block-user">

  <h2>Действия</h2>

  <div class="content"><ul class="menu">
  <li class="collapsed last"><a href="">Список студентов</a></li> 
  <li class="collapsed last"><a href="http://ins-iit.ru/">Вернуться на главную</a></li>
  </ul></div>
</div>
        </div>
      
      <div id="center"><div id="squeeze"><div class="right-corner"><div class="left-corner" style="padding: 60px 25px 1em 35px;">
                                                                                          <div class="clear-block">
<!--            <div id="first-time"> -->

<?php

    print "<H1 class=\"title\">Список студентов</H1><BR>";

   print "<DIV style=\"border: none; width: 98%; margin:0 auto;\">";
   $spec = $msl->getarray("SELECT a.id, a.`base_id` FROM `admission`.`catalogs` a  
							       LEFT JOIN admission.specialties c ON a.specialty=c.id 
							       LEFT JOIN admission.`universities_departments` d ON c.department=d.id 
                  					       LEFT JOIN admission.`universities_faculties` e ON d.faculty=e.id WHERE e.university='".$university_id."' ORDER by a.id ASC", 1); 
	
$arr = array();
foreach($spec as $v) {
    if ($v['base_id'] != 0) $arr[] = $v['base_id'];
}

$arr2 = array();
foreach($spec as $v) $arr2[] = $v['id'];

$profile = $msl->getarray("SELECT `base_id` FROM `admission`.`catalogs_profiles` WHERE `catalog` IN (".implode(',',$arr2).")", 1);
foreach ($profile as $v) {
    $arr[] = $v['base_id'];
} 
   $rval = $mssql->getarray("SELECT a.id,a.surname,a.name,a.second_name FROM dbo.student a WHERE a.catalog IN (".implode(',',$arr).");",1);

   print "<TABLE border=0 cellspacing=0 cellpadding=0 id=example class=display>";

   print "<THEAD>
              <TR>
	       <TH style=\"text-align: center; \"><INPUT type=\"text\" name=\"search_id\" value=\"id\" style=\"width: 35px;\" class=\"search_init\"/></TH>
               <TH><INPUT type=\"text\" name=\"search_surname\" value=\"Фамилия\" class=\"search_init\"/></TH>
               <TH>Имя</TH>
               <TH>Отчество</TH>
              </TR>
          </THEAD>";
   print "<TBODY>";
   
   foreach($rval as $r) {
      print "<TD style=\"width: 60px; text-align: center;\">".$r['id']."</TD><TD>".$r['surname']."</TD><TD>".$r['name']."</TD><TD>".$r['second_name']."</TD></TR>";
   }
   print "</TBODY>";
   print "</TABLE></DIV>";
 

   print "<DIV id=\"dialog-message\"></DIV>\n";



print 'Если у Вас появились вопросы, свяжитесь с нашими сотрудниками по телефонам: +7 (499) 1277453 <A href="mailto:iit@ins-iit.ru">по электронной почте</A>.';
?>

<div id=footer style="margin:1em">© 2009-2011, ins-iit.ru Team</div>
      </div></div></div></div> <!-- /.left-corner, /.right-corner, /#squeeze, /#center -->

      
    </div> <!-- /container -->
  </div>
<!-- /layout -->

</body></html>
