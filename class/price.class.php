<?php
class Price
{

    public $msl;
    public $mssql;

    private function _countFinalPrice($iprice, $purpose = 1, $count = 1, $region = 1)
    {
        $price = array();
        $pval = $this->msl->getarray("SELECT percent,region_percent,count,region FROM `receipt_purpose` WHERE `id` = '".$purpose."' LIMIT 1");

        // влияет ли количество на окончательную цену услуги
        if ($pval['count'] != 1) {
            $count = 1;
        }
        
        // существует ли доля региона в стоимости услуги
        if ($pval['region'] == 1 && $region == 1) {
            // какой региональный процент для услуги
            if ($pval['region_percent'] > 0) {
                // делим общую стоимость услуги пропорционально процентам
                $price[0] = $iprice['price'] * ($pval['percent'] - $pval['region_percent']) / 100 * $count;
                $price[1] = $iprice['price'] * $pval['region_percent'] / 100 * $count;
            } else {
                $price[0] = $iprice['price'] * $pval['percent'] / 100 * $count;
                $price[1] = round($iprice['price_rp'] * $pval['percent'] / 100 * $count); // необходимо убрать проценты!
                $price[0] = round($price[0] - $price[1]);
            }
        } else {
            $price[0] = ceil($iprice['price'] * $pval['percent'] / 100 * $count);
        }
        return $price;
    }

    public function __construct($msl, &$mssql = 0)
    {
        $this->msl = $msl;
        if (isset($mssql)) {
            $this->mssql = $mssql;
        }
        return true;
    }

    public function __destruct()
    {
        //unset($this->msl);
        return true;
    }

    public function getPricePercentByPgid($pgid, $catalog, $purpose = 1, $count = 1, $date = 0, $region = 1, $applicant = 0)
    {
        $query = "SELECT price,price_rp FROM admission.price WHERE `group`='".$pgid."' AND `catalog`='".$catalog."' AND `applicant`='".$applicant."'";
        if ($date > 0) {
            $query .= " AND `start` <= '".$date."' AND `end` >= '".$date."'";
        } elseif ($date == 0) {
            $query.= " ORDER BY `start` DESC";
        }
        $query .= " LIMIT 1";
        return $this->msl->getarray($query);
    }

    public function getPriceByPgid($pgid, $catalog, $purpose = 1, $count = 1, $date = 0, $region = 1, $applicant = 0)
    {
        $iprice = $this->getPricePercentByPgid($pgid, $catalog, $purpose, $count, $date, $region, $applicant);
        return $this->_countFinalPrice($iprice, $purpose, $count, $region);
    }

    public function getPriceByRegion($rgn, $catalog, $purpose = 1, $count = 1, $date = 0, $region = 1, $applicant = 0)
    {
        $reg = $this->msl->getarray("SELECT pgid FROM partner_regions WHERE id='".$rgn."' LIMIT 1");
        return $this->getPriceByPgid($reg['pgid'], $catalog, $purpose, $count, $date, $region, $applicant);
    }

// выбор из mssql базы данных сведения по студентам
    public function getPriceByStudent($id, $purpose = 2, $count = 1, $date = 0)
    {
        $cat = $this->mssql->getarray("SELECT semestr,catalog,semestr_end FROM dbo.student WHERE id='".$id."' ");

        $query = "SELECT * FROM dbo.student_price WHERE id='".$id."'";
        if ($date > 0) {
            $query .= " AND date_start <= '".$date."' AND date_end >= '".$date."'";
        }

        $price = $this->mssql->getarray($query, 0);
        if ($price == 0) {
            die('Цена на студента не сформирована!');
        }

        // Дипломный семестр
        if ($cat['semestr'] + 1 >= $cat['semestr_end']) {
            if ($purpose == 2) {
                return array($price['diplom_to_us'], $price['diplom_reg']);
            } else {
                return $this->_countFinalPrice(array('price'=>$price['price']), $purpose, $count);
            }
        }

//print_r($price);
        if ($purpose == 2) {
            return array($price['price'], $price['price_reg']);
        }

        $pval = $this->msl->getarray("SELECT percent,count FROM `receipt_purpose` WHERE `id` = '".$purpose."' LIMIT 1");
        $count = ($pval['count'] ? $count : 1);

        return array((($price['price']+$price['price_reg']) * $pval['percent']/100 * $count));
    }

    public function getDateByStudent($id)
    {
        return $this->mssql->getarray("SELECT date_start,date_end FROM dbo.student_price WHERE id='".$id."' ORDER BY date_end DESC");
    }

    // получить список сессий
    public function getSessions()
    {
        $sessions = array();
        $sessions_t = array(2 => 'зима', 6 => 'лето', 10 => 'осень');
        $tyear = date('Y');
        foreach ($sessions_t as $k => $v) {
            if (date('m') <= $k + 2) {
                $sessions[$tyear."-".sprintf("%02d", $k)."-01"] = $v." ".$tyear;
            } else {
                $sessions[($tyear+1)."-".sprintf("%02d", $k)."-01"] = $v." ".($tyear+1);
            }
        }

        ksort($sessions);
        return $sessions;

    }

/*
Возвращает массив с диапазонами зачисления студентов
*/

    public function getApplicantDate() 
    {
        $date = array();
        $date['0'] = 'до 31.08.2012';

        for ($i = 12; $i < date('y'); $i++) {
            $date['20'.$i.'-09-01'] = "с 01.09.20".$i." по 31.08.20".($i + 1);
        }

        $date['20'.$i.'-09-01'] = "с 01.09.20".$i;
        $date['2014-09-02'] = "с 02.09.2014 (обновленный тариф)";
        return $date;
    }

    public function newgetPrice($catalog, $region, $startApplicant='2014-09-02') 
    {
        $pval = $this->msl->getarray("SELECT price, price_university, price_rp FROM `price` 
                                      WHERE `catalog` = '".$catalog."' AND `group` = (SELECT pgid FROM partner_regions WHERE id='".$region."' LIMIT 1) AND `applicant` = '".$startApplicant."' LIMIT 1");
        return $pval;
    }

/*
Поступление денежных средств от студентов
*/
    public function studentPayments($id)
    {
        $query = "SELECT * FROM dbo.fin WHERE id='".$id."' ORDER BY `date` DESC";
        $payments = $this->mssql->getarray($query, 0);
        
        return $payments;
    }
} 
