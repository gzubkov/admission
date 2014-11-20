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


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Личный кабинет регионального партнера</title>

<link type="text/css" rel="stylesheet" media="all" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css" />
<?php
/*<link type="text/css" rel="stylesheet" media="all" href="//cdn.datatables.net/plug-ins/725b2a2115b/integration/bootstrap/3/dataTables.bootstrap.css">

<!-- Validation -->
<link rel="stylesheet" href="../css/validationEngine.jquery.css" type="text/css" media="screen" title="no title" charset="utf-8" />
<!--<SCRIPT type="text/javascript" src="http://www.position-relative.net/creation/formValidator/js/jquery-1.6.min.js"></script>-->
<SCRIPT type="text/javascript" src="http://code.jquery.com/jquery-1.10.2.min.js"></script>
<!-- jQuery UI -->
<SCRIPT type="text/javascript" src="//code.jquery.com/ui/1.10.4/jquery-ui.js"></script>

<SCRIPT type="text/javascript" src="http://malsup.com/jquery/block/jquery.blockUI.1.33.js"></SCRIPT> 
<SCRIPT type=\"text/javascript\" src=\"//cdn.datatables.net/1.10.2/js/jquery.dataTables.min.js\"></SCRIPT>
<!-- Script validation -->
<!--<script type="text/javascript" src="http://www.position-relative.net/creation/formValidator/js/jquery.validationEngine.js"></script>-->
*/
?>
<!--<link rel="stylesheet" type="text/css" href="//netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap.min.css" />-->
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" />
<link rel="stylesheet" type="text/css" href="http://cdn.datatables.net/1.10.2/css/jquery.dataTables.css" />
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.6.2/css/bootstrap-select.min.css" />
<!--<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/plug-ins/725b2a2115b/integration/bootstrap/3/dataTables.bootstrap.css">-->
<link type="text/css" rel="stylesheet" media="all" href="../images/defaults.css" />
<link type="text/css" rel="stylesheet" media="all" href="../images/system.css" />
<link type="text/css" rel="stylesheet" media="all" href="../images/style.css" />
<style>
.dataTables_length label{
    padding-top: 10px;
    font-weight: normal;
}
</style>
<script type="text/javascript" language="javascript" src="//code.jquery.com/jquery-1.11.1.min.js"></script>
<script type="text/javascript" src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.6.2/js/bootstrap-select.min.js"></script>
<script type="text/javascript" language="javascript" src="//cdn.datatables.net/1.10.2/js/jquery.dataTables.min.js"></script>
<!--<script type="text/javascript" language="javascript" src="//cdn.datatables.net/plug-ins/725b2a2115b/integration/bootstrap/3/dataTables.bootstrap.js"></script>-->
<script type="text/javascript" charset="utf-8">
$(document).ready(function() {
    // DataTable
    var table = $('#example').DataTable({
        "sDom": '<"top">rt<"bottom"><"clear"lp>',
        "lengthMenu": [ [10, 50, -1], [10, 50, "Все"] ],
        "columnDefs": [ 
            {"targets": 2, "searchable": false, "orderable": false},
            {"targets": 3, "searchable": false, "orderable": false}
        ],
        "language": {
            //url: '//cdn.datatables.net/plug-ins/725b2a2115b/i18n/Russian.json',
            search: "Поиск по фамилии или номеру личного дела:",
            sLengthMenu:   "Показывать по _MENU_ записей",
            sZeroRecords:  "Записи отсутствуют.",
            "sInfo":         "Показано с _START_ по _END_ из _TOTAL_ записей",
            "sInfoEmpty":    "Студентов нет",
            "sInfoFiltered": "(отфильтровано из _MAX_ записей)",
            "sInfoPostFix":  "",
            "sUrl":          "",
            paginate: {
                first:      "<<",
                previous:   "<",
                next:       ">",
                last:       ">>"
            }
        }
    });

    $("input[allowed=onlynumbers]").keypress(function (e) {
        //if the letter is not digit then don't type anything
        if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
            return false;
        }
    });

    // Apply the search
    table.columns().eq( 0 ).each( function ( colIdx ) {
        $( 'input', table.column( colIdx ).header() ).on( 'keyup change', function () {
            table
                .column( colIdx )
                .search( this.value )
                .draw();
        } );
    } );

    // Show menu
    $('#example tbody').on('click', 'tr', function () {
        var trsel = this; 
        $.ajax({url: 'student.php', type: 'POST', data: 'id='+$('td', this).eq(0).text(),
                success: function (msg) {
                    $('#myModalLabel').text($('td', trsel).eq(1).text() + ' ' + $('td', trsel).eq(2).text() + ' ' + $('td', trsel).eq(3).text() + ' (' + $('td', trsel).eq(0).text() + ')' );
                    $('#myModal .modal-body').html(msg);
                    $('#myModal').modal({show: true});
                } 
        })
    } );


   // oTable = $('#example').dataTable( {
        
    //"bJQueryUI": true,
//"sDom": 'rtip<"clear">',
//"iDisplayLength": 20,
//// "bStateSave": true,
}); 
</script>

</head>
<body class="sidebar-left">
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Закрыть</span></button>
        <h4 class="modal-title" id="myModalLabel"></h4>
      </div>
      <div class="modal-body" id="myModalText"></div>
   <!--   <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
      </div> -->
    </div>
  </div>
</div>

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

$region_id = $_SESSION['joomlaregion'];
$rval = $msl->getarray("SELECT firm,approved,`base_password` FROM `partner_regions` WHERE id='".$region_id."'");
echo "<div class=\"form-item\">".$rval['firm']."</div>";

?>
</div>

</div>
<div id="block-user-1" class="clear-block block block-user">

  <h2>Действия</h2>

  <div class="content"><ul class="menu">
   <li class="collapsed last"><a href="index.php?act=addapplicant">Добавить абитуриента</a></li> 
   <li class="collapsed last"><a href="index.php?act=listapplicant">Список абитуриентов</a></li>

<?php
/*
 * Заглушка для региона ЦКТ
 */
 
if ($region_id != 3) {
    echo "<li class=\"collapsed last\"><a href=\"index_students.php\">Список студентов</a></li>";
}
?> 
   <li class="collapsed last"><a href="index.php?act=card">Мои данные</a></li> 
<!--  <li class="collapsed last"><a href="?act=receipt">Распечатать квитанцию</a></li> -->
  <li class="collapsed last"><a href="index.php">Вернуться на главную</a></li>
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
case "basestudent":

    if ($rval['approved'] == 0) {    
        echo "<P>Продолжение работы невозможно без подтверждения Вами сведений о региональном партнере. Для подтверждения <A href=\"?act=card\">проверьте свои данные</A>.</P>\n\n";
        break;
    }
    
    echo "<H1 class=\"title\">Список студентов из базы данных</H1><DIV id=\"output\"></DIV>";
    echo "<div id=\"myaccordion\"><br>\n"; 

    require_once '../class/mssql.class.php';
    $mssql = new dMssql();
    $rval = $mssql->getarray("SELECT id,surname,name,second_name FROM dbo.student WHERE region = '".$region_id."' ORDER by id DESC", 1);

    if ($rval == 0) {
        echo "<tr><TD><H4>У вас нет студентов.</H4></TD></tr>";
    } else {    
        echo "<DIV><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"display\" id=\"example\">";
        echo "<thead><tr>
                <th><input type=\"text\" style=\"width: 55px;\" name=\"search_id\" placeholder=\"Номер\" class=\"search_init\" allowed=\"onlynumbers\"></th>
                <th><input type=\"text\" style=\"width: 125px;\" name=\"search_surname\" placeholder=\"Фамилия\" class=\"search_init\"></th>
                <th>Имя</th>
                <th>Отчество</th>
              </tr></thead><tbody>";

        foreach($rval as $k) {
            echo "<tr><td>".$k['id']."</td><td>".$k['surname']."</td>
                  <td>".$k['name']."</TD><TD>".$k['second_name']."</td></tr>\n";
        }
    }

    echo "</TBODY></TABLE></DIV>\n\n";
    break;
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
