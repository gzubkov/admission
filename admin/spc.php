<?php
require_once('../../conf.php');
require_once('../class/mysql.class.php');
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html class="js" dir="ltr" xml:lang="en" xmlns="http://www.w3.org/1999/xhtml" lang="en"><head>

<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
 
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Поступление - Работа с абитуриентами</title>

<link type="text/css" rel="stylesheet" media="all" href="../images/defaults.css">
<link type="text/css" rel="stylesheet" media="all" href="../images/system.css">
<link type="text/css" rel="stylesheet" media="all" href="../images/style.css">
<link type="text/css" rel="stylesheet" media="all" href="../css/smoothness/jquery-ui-1.8.7.custom.css">	

<style type="text/css">
   .dataTables_info { padding: 0px; }
   .dataTables_paginate { padding: 8px; }


/*
 *  File:         demo_table.css
 *  CVS:          $Id$
 *  Description:  CSS descriptions for DataTables demo pages
 *  Author:       Allan Jardine
 *  Created:      Tue May 12 06:47:22 BST 2009
 *  Modified:     $Date$ by $Author$
 *  Language:     CSS
 *  Project:      DataTables
 *
 *  Copyright 2009 Allan Jardine. All Rights Reserved.
 *
 * ***************************************************************************
 * DESCRIPTION
 *
 * The styles given here are suitable for the demos that are used with the standard DataTables
 * distribution (see www.datatables.net). You will most likely wish to modify these styles to
 * meet the layout requirements of your site.
 *
 * Common issues:
 *   'full_numbers' pagination - I use an extra selector on the body tag to ensure that there is
 *     no conflict between the two pagination types. If you want to use full_numbers pagination
 *     ensure that you either have "example_alt_pagination" as a body class name, or better yet,
 *     modify that selector.
 *   Note that the path used for Images is relative. All images are by default located in
 *     ../images/ - relative to this CSS file.
 */

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * DataTables features
 */

.dataTables_wrapper {
	position: relative;
	min-height: 302px;
	clear: both;
	_height: 302px;
	zoom: 1; /* Feeling sorry for IE */
}

.dataTables_processing {
	position: absolute;
	top: 50%;
	left: 50%;
	width: 250px;
	height: 30px;
	margin-left: -125px;
	margin-top: -15px;
	padding: 14px 0 2px 0;
	border: 1px solid #ddd;
	text-align: center;
	color: #999;
	font-size: 14px;
	background-color: white;
}

.dataTables_length {
	width: 40%;
	float: left;
}

.dataTables_filter {
	width: 50%;
	float: right;
	text-align: right;
}

.dataTables_info {
	width: 60%;
	float: left;
}

.dataTables_paginate {
	width: 44px;
	* width: 50px;
	float: right;
	text-align: right;
}

/* Pagination nested */
.paginate_disabled_previous, .paginate_enabled_previous, .paginate_disabled_next, .paginate_enabled_next {
	height: 19px;
	width: 19px;
	margin-left: 3px;
	float: left;
}

.paginate_disabled_previous {
	background-image: url('../images/back_disabled.jpg');
}

.paginate_enabled_previous {
	background-image: url('../images/back_enabled.jpg');
}

.paginate_disabled_next {
	background-image: url('../images/forward_disabled.jpg');
}

.paginate_enabled_next {
	background-image: url('../images/forward_enabled.jpg');
}



/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * DataTables display
 */
table.display {
	margin: 0 auto;
	clear: both;
	width: 100%;
}

table.display thead th {
	padding: 3px 18px 3px 10px;
	border-bottom: 1px solid black;
	font-weight: bold;
	cursor: pointer;
	* cursor: hand;
}

table.display tfoot th {
	padding: 3px 18px 3px 10px;
	border-top: 1px solid black;
	font-weight: bold;
}

table.display tr.heading2 td {
	border-bottom: 1px solid #aaa;
}

table.display td {
	padding: 3px 10px;
}

table.display td.center {
	text-align: center;
}

*/

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * DataTables sorting
 */

.sorting_asc {
	background: url('../images/sort_asc.png') no-repeat center right;
}

.sorting_desc {
	background: url('../images/sort_desc.png') no-repeat center right;
}

.sorting {
	background: url('../images/sort_both.png') no-repeat center right;
}

.sorting_asc_disabled {
	background: url('../images/sort_asc_disabled.png') no-repeat center right;
}

.sorting_desc_disabled {
	background: url('../images/sort_desc_disabled.png') no-repeat center right;
}





/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * DataTables row classes
 */
table.display tr.odd.gradeA {
	background-color: #ddffdd;
}

table.display tr.even.gradeA {
	background-color: #eeffee;
}

table.display tr.odd.gradeC {
	background-color: #ddddff;
}

table.display tr.even.gradeC {
	background-color: #eeeeff;
}

table.display tr.odd.gradeX {
	background-color: #ffdddd;
}

table.display tr.even.gradeX {
	background-color: #ffeeee;
}

table.display tr.odd.gradeU {
	background-color: #ddd;
}

table.display tr.even.gradeU {
	background-color: #eee;
}


tr.odd {
	background-color: #E2E4FF;
}

tr.even {
	background-color: white;
}





/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Misc
 */
.dataTables_scroll {
	clear: both;
}

.dataTables_scrollBody {
	*margin-top: -1px;
}

.top, .bottom {
	padding: 15px;
	background-color: #F5F5F5;
	border: 1px solid #CCCCCC;
}

.top .dataTables_info {
	float: none;
}

.clear {
	clear: both;
}

.dataTables_empty {
	text-align: center;
}

tfoot input {
	margin: 0.5em 0;
	width: 100%;
	color: #444;
}

tfoot input.search_init {
	color: #999;
}

td.group {
	background-color: #d1cfd0;
	border-bottom: 2px solid #A19B9E;
	border-top: 2px solid #A19B9E;
}

td.details {
	background-color: #d1cfd0;
	border: 2px solid #A19B9E;
}


.example_alt_pagination div.dataTables_info {
	width: 40%;
}

.paging_full_numbers {
	width: 400px;
	height: 22px;
	line-height: 22px;
}

.paging_full_numbers span.paginate_button,
 	.paging_full_numbers span.paginate_active {
	border: 1px solid #aaa;
	-webkit-border-radius: 5px;
	-moz-border-radius: 5px;
	padding: 2px 5px;
	margin: 0 3px;
	cursor: pointer;
	*cursor: hand;
}

.paging_full_numbers span.paginate_button {
	background-color: #ddd;
}

.paging_full_numbers span.paginate_button:hover {
	background-color: #ccc;
}

.paging_full_numbers span.paginate_active {
	background-color: #99B3FF;
}

table.display tr.even.row_selected td {
	background-color: #B0BED9;
}

table.display tr.odd.row_selected td {
	background-color: #9FAFD1;
}


/*
 * Sorting classes for columns
 */
/* For the standard odd/even */
tr.odd td.sorting_1 {
	background-color: #D3D6FF;
}

tr.odd td.sorting_2 {
	background-color: #DADCFF;
}

tr.odd td.sorting_3 {
	background-color: #E0E2FF;
}

tr.even td.sorting_1 {
	background-color: #EAEBFF;
}

tr.even td.sorting_2 {
	background-color: #F2F3FF;
}

tr.even td.sorting_3 {
	background-color: #F9F9FF;
}


/* For the Conditional-CSS grading rows */
/*
 	Colour calculations (based off the main row colours)
  Level 1:
		dd > c4
		ee > d5
	Level 2:
	  dd > d1
	  ee > e2
 */
tr.odd.gradeA td.sorting_1 {
	background-color: #c4ffc4;
}

tr.odd.gradeA td.sorting_2 {
	background-color: #d1ffd1;
}

tr.odd.gradeA td.sorting_3 {
	background-color: #d1ffd1;
}

tr.even.gradeA td.sorting_1 {
	background-color: #d5ffd5;
}

tr.even.gradeA td.sorting_2 {
	background-color: #e2ffe2;
}

tr.even.gradeA td.sorting_3 {
	background-color: #e2ffe2;
}

tr.odd.gradeC td.sorting_1 {
	background-color: #c4c4ff;
}

tr.odd.gradeC td.sorting_2 {
	background-color: #d1d1ff;
}

tr.odd.gradeC td.sorting_3 {
	background-color: #d1d1ff;
}

tr.even.gradeC td.sorting_1 {
	background-color: #d5d5ff;
}

tr.even.gradeC td.sorting_2 {
	background-color: #e2e2ff;
}

tr.even.gradeC td.sorting_3 {
	background-color: #e2e2ff;
}

tr.odd.gradeX td.sorting_1 {
	background-color: #ffc4c4;
}

tr.odd.gradeX td.sorting_2 {
	background-color: #ffd1d1;
}

tr.odd.gradeX td.sorting_3 {
	background-color: #ffd1d1;
}

tr.even.gradeX td.sorting_1 {
	background-color: #ffd5d5;
}

tr.even.gradeX td.sorting_2 {
	background-color: #ffe2e2;
}

tr.even.gradeX td.sorting_3 {
	background-color: #ffe2e2;
}

tr.odd.gradeU td.sorting_1 {
	background-color: #c4c4c4;
}

tr.odd.gradeU td.sorting_2 {
	background-color: #d1d1d1;
}

tr.odd.gradeU td.sorting_3 {
	background-color: #d1d1d1;
}

tr.even.gradeU td.sorting_1 {
	background-color: #d5d5d5;
}

tr.even.gradeU td.sorting_2 {
	background-color: #e2e2e2;
}

tr.even.gradeU td.sorting_3 {
	background-color: #e2e2e2;
}


/*
 * Row highlighting example
 */
.ex_highlight #example tbody tr.even:hover, #example tbody tr.even td.highlighted {
	background-color: #ECFFB3;
}

.ex_highlight #example tbody tr.odd:hover, #example tbody tr.odd td.highlighted {
	background-color: #E6FF99;
}

.ex_highlight_row #example tr.even:hover {
	background-color: #ECFFB3;
}

.ex_highlight_row #example tr.even:hover td.sorting_1 {
	background-color: #DDFF75;
}

.ex_highlight_row #example tr.even:hover td.sorting_2 {
	background-color: #E7FF9E;
}

.ex_highlight_row #example tr.even:hover td.sorting_3 {
	background-color: #E2FF89;
}

.ex_highlight_row #example tr.odd:hover {
	background-color: #E6FF99;
}

.ex_highlight_row #example tr.odd:hover td.sorting_1 {
	background-color: #D6FF5C;
}

.ex_highlight_row #example tr.odd:hover td.sorting_2 {
	background-color: #E0FF84;
}

.ex_highlight_row #example tr.odd:hover td.sorting_3 {
	background-color: #DBFF70;
}


/*
 * KeyTable
 */
table.KeyTable td {
	border: 3px solid transparent;
}

table.KeyTable td.focus {
	border: 3px solid #3366FF;
}

table.display tr.gradeA {
	background-color: #eeffee;
}

table.display tr.gradeC {
	background-color: #ddddff;
}

table.display tr.gradeX {
	background-color: #ffdddd;
}

table.display tr.gradeU {
	background-color: #ddd;
}

div.box {
	height: 100px;
	padding: 10px;
	overflow: auto;
	border: 1px solid #8080FF;
	background-color: #E5E5FF;
}

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
      width: 600,		
      buttons: {
         Ok: function() {
	    $(this).dialog('close');
	 }
      }
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
   

/*   oTable = $('#example').dataTable( {
      "bJQueryUI": true,
      "bStateSave": true,
      "sPaginationType": "full_numbers",
      "aaSorting": [[0, 'asc']],
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
*/
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


</HEAD>
<BODY class="sidebar-left">


<?php
print "<BR>\n";
      

if ($_SESSION['rights'] == 'admin' && $_SESSION['md_rights'] == md5($CFG_salted.$_SESSION['rights'])) {
   $msl = new dMysql();
   print "<A onclick=\"unloginA();\">Выйти</A>\n";

   print "<DIV style=\"border: 1px solid #d3d3d3; background-color: #ffffff; width: 620px; margin:0 auto;\">";
   print "<P><CENTER>Образовательные программы</CENTER></P><BR>\n";

   print "<DIV style=\"border: none; width: 98%; margin:0 auto;\">";

   $query = "SELECT g.id, g.abbreviation, c.name, d.short, c.spec_code, c.qualify, a.internet, a.applicable, a.archive, a.term, a.basicsemestr FROM catalogs a 
                  LEFT JOIN specialties c ON a.specialty=c.id 
		  LEFT JOIN education_type d ON a.baseedu=d.id 
		  LEFT JOIN admission.`universities_departments` e ON c.department=e.id 
                  LEFT JOIN admission.`universities_faculties` f ON e.faculty=f.id 
		  LEFT JOIN admission.`universities` g ON f.university=g.id ORDER BY g.id ASC, a.id DESC";

   $mappl = $msl->getarray($query);
   print "<TABLE border=0 cellspacing=0 cellpadding=0 id=example class=display>";

   /*print "<THEAD>
              <TR>
	       <TH style=\"text-align: center; \"><INPUT type=\"checkbox\" id=\"selectAll\"></TH>
               <TH style=\"text-align: center; \"><INPUT type=\"text\" name=\"search_id\" value=\"id\" style=\"width: 35px;\" class=\"search_init\"/></TH>
               <TH><INPUT type=\"text\" name=\"search_surname\" value=\"Фамилия\" class=\"search_init\"/></TH>
               <TH><INPUT type=\"text\" name=\"search_name\" value=\"Имя\" class=\"search_init\"/></TH>
               <TH><INPUT type=\"text\" name=\"search_secondname\" value=\"Отчество\" class=\"search_init\"/></TH>
               <TH><INPUT type=\"text\" name=\"search_email\" value=\"e-mail\" class=\"search_init\"/></TH>
               <TH>Дата создания</TH>
               <TH>Шаг</TH>
              </TR>
          </THEAD>"; */
   print "<TBODY>";

   $temp = "";
   $temp2 = "";   

    foreach($mappl as $key => $r) {
        if ($r['abbreviation'] != $temp) {print "<TR><TH colspan=5 style=\"font-weight: bold; text-align: center;\">".$r['abbreviation']."</TH></TR>"; $temp = $r['abbreviation'];}
        print "<TR>";
        if ($r['spec_code'] != $temp2) {
            print "<TD style=\"text-decoration: ".($r['archive'] == 1 ? 'line-through' : 'none').";\">".$r['spec_code']."</TD><TD style=\"text-decoration: ".($r['archive'] == 1 ? 'line-through' : 'none').";\">".$r['name']." (".$r['qualify'].")</TD>"; 
	    $temp2 = $r['spec_code'];
        } else {
	    print "<TD></TD><TD></TD>";
	}
      print "<TD>".$r['short']." (".$r['term'].")</TD>";
      print "<TD>".($r['internet'] ? "И" : "")."</TD><TD>".($r['applicable'] ? "ЭП (".$r['basicsemestr'].")" : "")."</TD></TR>";
   }
   print "</TBODY>";
   print "</TABLE><BR>"; 
   print "</DIV>";

   print "<DIV id=\"dialog-message\"></DIV>\n";

} 
?>
</BODY></HTML>

