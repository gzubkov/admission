<?php
class Price
{
    var $msl;

    private function _countFinalPrice($iprice, $purpose=1, $count=1, $region=1) 
    {
    	$price = array();
	$pval = $this->msl->getarray("SELECT percent,region_percent,count,region FROM `receipt_purpose` WHERE `id` = '".$purpose."' LIMIT 1");
	$count = ($pval['count'] ? $count : 1);
	if ($pval['region'] == 1 && $region == 1) {
	    if ($pval['region_percent'] > 0) {
	        $price[0] = $iprice['price'] * ($pval['percent']-$pval['region_percent'])/100 * $count;
	        $price[1] = $iprice['price'] * $pval['region_percent']/100 * $count;
      	    } else {
      	        $price[0] = $iprice['price'] * $pval['percent']/100 * $count;
      	    	$price[1] = $price[0] * $iprice['percent']/100;
      	    	$price[0] = $price[0] - $price[1];
      	    }
	} else {
	    $price[0] = $iprice['price'] * $pval['percent']/100 * $count;
	}
	return $price;
    }    

    public function __construct(&$msl) 
    {
        $this->msl = $msl;
        return true;
    }

    public function __destruct() 
    {
        //unset($this->msl);
	return true;
    }

    public function getPriceByPgid($pgid, $catalog, $purpose=1, $count=1, $date=0, $region=1, $applicant=0) 
    {
        $query = "SELECT price,percent FROM admission.price_groups WHERE id='".$pgid."' AND catalog='".$catalog."' AND applicant='".$applicant."'";
	if ($date > 0) $query .= " AND `start` <= '".$date."' AND `end` >= '".$date."'";
	if ($date == 0) $query.= " ORDER BY `start` DESC";
	$query .= " LIMIT 1";
	$iprice = $this->msl->getarray($query);

	return $this->_countFinalPrice($iprice, $purpose, $count, $region);
    }  

    public function getPricePercentByPgid($pgid, $catalog, $purpose=1, $count=1, $date=0, $region=1) 
    {
        $query = "SELECT price,percent FROM admission.price_groups WHERE id='".$pgid."' AND catalog='".$catalog."' AND applicant=0";
	if ($date > 0) $query .= " AND `start` <= '".$date."' AND `end` >= '".$date."'";
	if ($date == 0) $query.= " ORDER BY `start` DESC";
	$query .= " LIMIT 1";
	return $this->msl->getarray($query);
    }  

    public function getPriceByRegion($rgn, $catalog, $purpose=1, $count=1, $date=0, $region=1, $applicant=0) 
    {
        $reg = $this->msl->getarray("SELECT pgid FROM partner_regions WHERE id='".$rgn."'");
        return $this->getPriceByPgid($reg['pgid'], $catalog, $purpose, $count, $date, $region, $applicant);
    }

    public function getPriceByStudent($id, $purpose=2, $count=1, $date=0) 
    {
        $cat = $this->msl->getarray("SELECT semestr,catalog FROM students_base.student WHERE id='".$id."' LIMIT 1");

        $profile = $this->msl->getarray("SELECT catalog FROM `admission`.`catalogs_profiles` WHERE base_id='".$cat['catalog']."' LIMIT 1");
	if ($profile != 0) {
	    $ds = $this->msl->getarray("SELECT term,termm,start_semestr FROM admission.catalogs WHERE id='".$profile['catalog']."' LIMIT 1");
	} else {
	    $ds = $this->msl->getarray("SELECT term,termm,start_semestr FROM admission.catalogs WHERE `base_id`='".$cat['catalog']."' LIMIT 1");	
	}

	$query = "SELECT price, percent, `diplom_to_us` FROM students_base.student_price WHERE id='".$id."'";
	if ($date > 0) {
	    $query .= " AND `date_start` <= '".$date."' AND `date_end` >= '".$date."'";
	}
	$query .= " LIMIT 1";
	    
	$price = $this->msl->getarray($query, 0);
	if ($price == 0) die('Цена на студента не сформирована!');
	    
	if ($cat['semestr'] + 2 >= 2*$ds['term']+$ds['termm']/6+$ds['start_semestr']) {
	    if ($purpose == 2) {
	        return array($price['diplom_to_us'], $price['price']*1.5-$price['diplom_to_us']);
	    } else {
	        $price = array('price'=>$price['price']);
	    }
	}
	
	return $this->_countFinalPrice($price, $purpose, $count);
    }

    public function getDateByStudent($id) 
    {
        return $this->msl->getarray("SELECT date_start,date_end FROM `students_base`.student_price WHERE id='".$id."' ORDER BY date_end DESC LIMIT 1");
    }

    // получить список сессий
    public function getSessions() 
    {
        $sessions = array();
        $sessions_t = array(2 => 'зима', 6=> 'лето', 10=> 'осень');
	$tyear = date('Y');
	foreach ($sessions_t as $k => $v) {
	    if (date('m') <= $k) {
                $sessions[$tyear."-".sprintf("%02d",$k)."-01"] = $v." ".$tyear;
    	    } else {
                $sessions[($tyear+1)."-".sprintf("%02d",$k)."-01"] = $v." ".($tyear+1);
    	    }  
        }
	ksort($sessions);
	return $sessions;
    }
}
?>
