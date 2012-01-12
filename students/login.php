<?php
require_once('../../conf.php');
        
$_POST = $_REQUEST;
if (!isset($_POST['act'])) {$_POST['act'] = "";}
switch($_POST['act']) 
{
    case "exit":
        $_SESSION = array();
        print "ok\n";
        break;

    default:
        require_once('../../../modules/mysql.php');

	$rval = getarray("SELECT doc_number FROM `students_base`.student WHERE `id`='".$_POST['num']."'");
      
        if ($_POST['pass'] === $rval['doc_number']) {
            $_SESSION['student_id'] = $_POST['num'];
   	    print "ok\n";
      	} else {
            print "wrondpwd";
        }
}
?>
