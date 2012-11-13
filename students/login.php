<?php
require_once('../../conf.php');
        
$_POST = $_REQUEST;
if (!isset($_POST['act'])) {
    $_POST['act'] = "";
}

switch($_POST['act']) {
    case "exit":
        unset($_SESSION['student_id']);
        print "ok\n";
        break;
    default:
        require_once('../class/mssql.class.php');
	$mssql = new dMssql();
	if (isset($_POST['num']) && isset($_POST['pass'])) {
	    $rval = $mssql->getarray("SELECT doc_number FROM dbo.student WHERE `id`='".$_POST['num']."'");
      
	    if ($_POST['pass'] === $rval['doc_number']) {
                $_SESSION['student_id'] = $_POST['num'];
   	    	print "ok\n";
      	    } else {
                print "wrondpwd";
            }
	} else {
	    print "wrondpwd";
	}
	unset($msl);
}
?>
