<?php
require_once('../conf.php');
require_once('../../modules/mysql.php');

$msl = new dMysql();
$_POST = $_REQUEST;
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

case "revoke":
    $query = "DELETE `reg_request` FROM `reg_request` WHERE `reg_request`.id=".$_POST['id']."";
    if ($msl->deleteArray('reg_request',array('id'=>$_POST['id']))) {
        print "ok\n";
    } else {
        print "couldn't use query";
    }
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
