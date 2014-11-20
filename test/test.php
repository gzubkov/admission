<?php
require_once('../../conf.php');
require_once('../class/mysql.class.php');
require_once('latex.php');
$msl = new dMysql();

$_SESSION['uid'] = 1;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Поступление - система тестирования</title>
<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
<SCRIPT type="text/javascript" src="../js/jquery-1.4.2.min.js"></script>
<!-- jQuery UI -->
<SCRIPT type="text/javascript" src="../js/jquery-ui-1.8.custom.min.js"></script>
<SCRIPT type="text/javascript" src="../js/jquery.blockUI.js"></SCRIPT>
<link type="text/css" rel="stylesheet" media="all" href="../images/defaults.css">
<link type="text/css" rel="stylesheet" media="all" href="../images/system.css">
<link type="text/css" rel="stylesheet" media="all" href="../images/style.css">
<link type="text/css" rel="stylesheet" media="all" href="../css/custom-theme/jquery-ui-1.8.custom.css">

<script type="text/javascript">
	$(function() {
		     $("input:submit, input:button").button();
                     $.datepicker.setDefaults({changeMonth: true, changeYear: true});
	});

$('#test').live('submit', function(){
   $.blockUI({ message: 'Ваш запрос обрабатывается...' });
   $.ajax({url: 'check.php', data: $('#test').serialize(), 
           success: function(msg){
	      var scores = 0;
	      var scoresall = $('input[name=scoresall]').val();
	      
	      if (msg != 'notanyoneselected') {
	      var num = $('input[name=numtickets]').val();
	      var response = !(/[^,:{}[]0-9.-+Eaeflnr-u nrt]/.test(msg.replace(/"(.|[^"])*"/g, ''))) && eval('(' + msg + ')');
	      $.each(response, function(i,item){
	         if (item.score == item.count) {
	            $('#cell'+item.id).append("<IMG src=\"ok.gif\" alt=\"Ответ на вопрос верный!\">");
		    scores += scoresall/num;
		 } else {
	            $('#cell'+item.id).append("<IMG src=\"er.gif\" alt=\"Ответ на вопрос неверный!\">");
		 }
		 $.each(item.right, function(j,rig){
		    $('#cellans'+rig).css({'font-style': 'italic'});
		 });	         
              });

	      }
              $.unblockUI();
	      
	      $('#testsubmit').html('Вы получили '+scores.toFixed(0)+' из '+scoresall+' баллов.');
	   }
   });
   return false;
});

</script>
</head>
<body>

<?php
if (isset($_SESSION['uid']) && $_SESSION['uid'] > 0) {
   $_POST['subject'] = 8;
   $_POST['var'] = 3;
   $rval = $msl->getarray("SELECT name,comment from reg_subjects WHERE id='".$_POST['subject']."'"); 
   $rarr = $msl->getarray("SELECT id,text,type from reg_test_questions WHERE subject_id='".$_POST['subject']."' AND variant='".$_POST['var']."'");

   print "<BR><DIV style=\"border: 1px solid #d3d3d3; background-color: #ffffff; width: 700px; margin:0 auto;\"><CENTER><BR>";
   print "Вступительные испытания по дисциплине \"".$rval['name']."\"<BR>Вариант № ".$_POST['var'];
   if ($rval['comment'] != '') {
      print "<DIV style=\"border: 1px solid #d3d3d3; background-color: #ffffff; width: 90%; padding: 15px; text-align: left\">".latex($rval['comment'])."</DIV>";
   }
   print "<FORM type=POST id=\"test\">";
   print "<TABLE border=0 id=\"table-test\" style=\"width: 98%\"><TBODY style=\"border: none;\">";

   require('../latex/render.class.php');
   $render = new render();

   foreach ($rarr as $key => $r) {
      print "<TR><TD colspan = 2 id=\"cell".$r['id']."\">";
      print "<B>".($key+1).". ".$render->transform(latex($r['text']))."</B></TD></TR>";

      $rval = $msl->getarray("SELECT id, text from reg_test_answers WHERE question_id='".$r['id']."'",1);
      
      foreach ($rval as $key2 => $r2) {
         print "<TR class=\"".(($key2%2 != 0) ? "even" : "odd")."\">";
   	 print "<TD id=\"cellans".$r2['id']."\"><LABEL>";
   
         switch($r['type']) {
            case 1:
               print "<INPUT type=\"radio\" name=\"sel[".$r['id']."]\" value=\"".$r2['id']."\"> ".$render->transform($r2['text']);
               break; 
            case 2:
               print "<INPUT type=\"checkbox\" name=\"sel[".$r['id']."][]\" value=\"".$r2['id']."\"> ".$render->transform($r2['text']);
               break;
	    case 3:
	       print "Ответ: <INPUT type=\"text\" name=\"sel[".$r['id']."]\" value=\"\">";
	       break;
            default:
         } 
      
   print ".</LABEL></TD></TR>\n";
        // print " ".latex($r2['text']).".</TD></LABEL></TR>\n";
      }
   }

   print "<INPUT type=\"hidden\" name=\"numtickets\" value=\"".($key+1)."\">";
   print "<INPUT type=\"hidden\" name=\"scoresall\" value=\"100\">";
   print "<TR><TD colspan=2 style=\"text-align: center;\" id=\"testsubmit\"><INPUT type=\"submit\" value=\"Проверить\"></TD></TR>\n";
   print "</TABLE></FORM></DIV><BR>";
} else {
   print "false";
}




?>
