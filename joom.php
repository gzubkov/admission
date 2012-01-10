<?php
include('../conf.php');
include('../../modules/mysql.php');

$msl = new dMysql();
$r = $msl->getarray("SELECT `id` FROM `partner_regions` WHERE `jid`='".$_REQUEST['a']."'");
unset($msl);

if ($r != 0) {
    $_SESSION['joomlaregion'] = $r['id'];
    switch ($_REQUEST['type']) {
    case "receipt":
        header('Location: http://admission.iitedu.ru/receipt/');
        break;
    case "verify":
        header('Location: http://admission.iitedu.ru/regions/card.php');
        break;
    case "profile":
        header('Location: http://admission.iitedu.ru/regions/index.php');
	break;
    default:
        break;
    }
} else {
    exit(0);
}
?>