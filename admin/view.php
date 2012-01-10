<?php
require_once('../../conf.php');
require_once('../../../modules/mysql.php');
require_once('../class/rights.class.php');

$rg = new Rights();
if ($rg->checkAdmin()) {
    $msl = new dMysql();
    $r = getarray("SELECT applicant,filename FROM `reg_applicant_edu_doc` WHERE `id`=".$_REQUEST['id'].";");

    $f = fopen($CFG_uploaddir.$r['applicant']."/".$r['filename'],"rb");
    header("Content-type: image/jpeg");
    while(!feof($f)) echo fread($f,65000);
    fclose($f);
    exit();
} else {
    print "Данная функция Вам недоступна!";
}
unset($rg);
?>