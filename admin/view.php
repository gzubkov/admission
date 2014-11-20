<?php
require_once('../../conf.php');
require_once('../class/mysql.class.php');
require_once('../class/rights.class.php');

$rg = new Rights();
if ($rg->checkAdmin()) {
    $msl = new dMysql();
    $r = $msl->getarray("SELECT applicant,filename FROM `reg_applicant_edu_doc` WHERE `id`=".$_REQUEST['id'].";");
    unset($msl);

    $file = $CFG_uploaddir.$r['applicant']."/".$r['filename'];
    if (file_exists($file)) {
        $f = fopen($file,"rb");
    	if (!$f) exit(0);
    	header("Content-type: image/jpeg");
    	while(!feof($f)) echo fread($f,65000);
    	fclose($f);
    }
    exit();
} else {
    print "Данная функция Вам недоступна!";
}
unset($rg);
?>