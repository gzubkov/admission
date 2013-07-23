<?php
require_once('../../conf.php');
require_once('../class/mysql.class.php');
$msl = new dMysql();

include_once '../class/PHPExcel/IOFactory.php';
$objPHPExcel = PHPExcel_IOFactory::load('tarif.xls');
$objPHPExcel->setActiveSheetIndex(0);
    
$aSheet = $objPHPExcel->getActiveSheet();

print "<html><body><table border=1>";		

foreach ($aSheet->getRowIterator() as $row){
    $cellIterator = $row->getCellIterator();
    $item = array();

    foreach($cellIterator as $cell){
        array_push($item, $cell->getCalculatedValue());
	if ($item[0] != "МАМИ") {
	    $item = 0;
	    break;
	}
    }

    if ($item != 0 && sizeof($item) > 0) {
        if ($item[7] == "ОСО") $item[7] = "СО";
	$region = $msl->getarray("SELECT id FROM `price_groups_remark` WHERE `name` LIKE '%".$item[6]."%' LIMIT 1");

	if ($region['id'] == 0) print "<B>Нет такой региональной группы (".$item[6].")!</B><BR>\n";
	
	$catalog = $msl->getarray("SELECT id FROM `catalogs` WHERE `specialty`=(SELECT id FROM `specialties` WHERE `spec_code`='".$item[5]."') AND `baseedu`=(SELECT id FROM `education_type` WHERE `short`='".$item[7]."' LIMIT 1) AND `archive`=0");

	$price = $msl->getarray("SELECT * FROM `price_groups` WHERE `id`='".$region['id']."' AND `catalog`='".$catalog['id']."' AND `applicant`=0 AND `start`='2012-09-01'");

	if ($catalog['id'] != 0) {
	    print "<TR><TD>".$region['id']."</TD><TD>".$catalog['id']."</TD><TD>".$item[9]."</TD><TD>".$item[11]."</TD><TD>".($item[9]*$item[11]/100)."</TD></TR>";
	    
	    $array = array('id'=>$region['id'], 'catalog'=>$catalog['id'], 'price'=>$item[9], 'percent'=>$item[11], 'start'=>'2013-09-01', 'end'=>'2014-08-31', 'applicant'=>0);
//$msl->insertArray('price_groups', $array);
	} else {
	    print "Нет такого учебного плана: ".$item[4]." ".$item[7]."<BR>";
	}
    }
}
?>	
