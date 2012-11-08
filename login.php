<?php
require_once('../conf.php');
require_once('class/mysql.class.php');
$msl = new dMysql();

$_POST = $_REQUEST;
if (!isset($_POST['act'])) {
    $_POST['act'] = "";
}
switch($_POST['act']) 
{
case "exit":
    $_SESSION = array();
    print "ok\n";
    break;
   
case "goback":
    $_SESSION['step_num']--;
    print "ok\n";
    break;

default:
    $rval = $msl->getarray("SELECT id, step, doc_serie, doc_number, region, edu_base FROM `reg_applicant` WHERE `e-mail`='".$_POST['name']."'");
    if ($_POST['pass'] === $rval['doc_serie'].$rval['doc_number']) {
        $_SESSION['applicant_id'] = $rval['id'];
   	$_SESSION['step_num'] = $rval['step'];
	$_SESSION['region'] = $rval['region'];
	$_SESSION['edu_base'] = $rval['edu_base'];
   	print "ok\n";
    } else {
        print "error";
    }
}
?>
