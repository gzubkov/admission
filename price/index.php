<?php
require_once('../../conf.php');
require_once('../class/mysql.class.php');
$msl = new dMysql();

/*
* Дата начала приемной кампании и номер листа
*/
$applicantSheet = 8;
$applicantStart = '2014-09-02';
$tarifStart = '2014-09-01';

$tarifEnd = date('Y-m-d', strtotime("+ 1 year - 1 day", strtotime($tarifStart)));

include_once '../class/PHPExcel/IOFactory.php';
$objPHPExcel = PHPExcel_IOFactory::load('tarif.xls');
//print_r($objPHPExcel->getSheetNames());
$objPHPExcel->setActiveSheetIndex($applicantSheet);

$aSheet = $objPHPExcel->getActiveSheet();

echo "<html><body>Перенос тарифного плана в базу с листа ".$applicantSheet." (дата поступления ".$applicantStart.").<br>";		

foreach ($aSheet->getRowIterator() as $row) {
    $cellIterator = $row->getCellIterator();
    $item = array();

    foreach ($cellIterator as $cell) {
        array_push($item, $cell->getCalculatedValue());
        
        if (strcmp($item[0], "МАМИ") !== 0) {
            $item = 0;
            break;
        }   
    }

    if ($item != 0 && sizeof($item) > 0) {
        if (strcmp($item[7], "ОСО") === 0) {
            $item[7] = "СО";
        }
    
        if (strcmp($item[4], "Техносферная безопасность") === 0) {
            $item[5] = '20.03.01';
        }

        $region = $msl->getarray("SELECT id FROM `price_groups` WHERE `name` = '".$item[6]."' LIMIT 1");

//echo $item[6]." - ".$region['id']."<br>";

        if ($region['id'] == 0) {
            print "<B>Нет такой региональной группы (".$item[6].")!</B><BR>\n";
            continue;
        }

        // Заглушка на перенумерацию специальностей и направлений
        if (strlen($item[5]) > 8) {
            $condition = "`spec_code`='".$item[5]."'";
        } else {
            $condition = "`code`='".$item[5]."'";
        }

	    $catalog = $msl->getarray("SELECT id FROM `catalogs` WHERE `specialty`=(SELECT id FROM `specialties` WHERE ".$condition." LIMIT 1) 
                                                             AND `baseedu`=(SELECT id FROM `education_type` WHERE `short` LIKE '%".$item[7]."%' LIMIT 1)",1);
        foreach ($catalog as $cat) {
	        if ($cat['id'] != 0) {
	        	$price = $msl->getarray("SELECT * FROM `price` WHERE `group`='".$region['id']."' AND `catalog`='".$cat['id']."' AND `applicant`='".$applicantStart."' AND `start`='".$tarifStart."' LIMIT 1");
                
                if ($price != 0) {
                    if ($price['price'] != $item[9] || 
                        $price['price_university'] != $item[14] || 
                        $price['price_rp'] != $item[10]) {
                        echo "<b>Несовпадение цены для плана ".$item[4]." ".$item[7]." (".$cat['id']." - ".$item[6]." - ".$region['id'].")</b> Было: ".$price['price']." - стало: ".$item[9]."<br>";
                    }
                } else {
	                $array = array('group' => $region['id'], 'catalog' => $cat['id'], 
                                   'price' => $item[9], 'price_university' => $item[14], 'price_rp' => $item[10], 
                                   'start' => $tarifStart, 'end' => $tarifEnd, 'applicant' => $applicantStart);
                    print_r($array);
                    echo "<br>";
                    $msl->insertArray('price', $array);
                }
	        } else {
	            print "Нет такого учебного плана: ".$item[4]." ".$item[7]." (".$item[5].")<BR>";
	        }
        }
    }
}
