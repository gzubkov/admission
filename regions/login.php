<?php
require_once('../../conf.php');
        
$_POST = $_REQUEST;
if (!isset($_POST['act'])) {
    $_POST['act'] = "";
}

switch($_POST['act']) 
{
case "exit":
    unset($_SESSION['joomlaregion']);
    print "ok\n";
    break;

case "openregion":
    $_SESSION['joomlaregion'] = $_REQUEST['region'];
    break;

default:
    require_once('../class/mysql.class.php');
    $msl = new dMysql();
    if (isset($_POST['login']) && isset($_POST['password'])) {
	$r = $msl->getarray("SELECT * FROM users WHERE `e-mail`='".$_POST['login']."'");

    	if (md5($CFG_salted.$_POST['password']) == $r['passwd']) {
            $_SESSION['joomlaregion'] = 3;
            print "ok";
        } else {
            print "wrondpwd";
        }
    } else {
	print "wrondpwd";
    }
    unset($msl);
}
?>
