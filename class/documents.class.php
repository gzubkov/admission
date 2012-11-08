<?php
class Applicant
{
    var $_msl;
    var $_id;
    var $connum = 2; // количество договоров мы-студент

    var $surname;
    var $name;
    var $second_name;
    var $sex;

    var $catalog;
    var $profile;
    var $semestr;
    var $type;

    public function __construct(&$msl, $id) 
    {
        $this->_msl = $msl; 
	$this->_id  = $id;

	if (!$this->_checkSecurity()) {
	    exit(0);
	}

	$r = $this->_msl->getarray("SELECT surname,name,second_name,sex,type,catalog,profile,semestr FROM reg_applicant WHERE id = ".$this->_id." LIMIT 1;");
	$this->surname     = $r['surname'];
	$this->name        = $r['name'];
	$this->second_name = $r['second_name'];
	$this->sex         = $r['sex'];
	$this->type        = $r['type'];

	$this->catalog     = $r['catalog'];
	$this->profile     = $r['profile'];
	$this->semestr     = $r['semestr'];
        return true;
    }

    public function __destruct() 
    {
        return true;
    }

    private function _checkSecurity() 
    {
        global $CFG_salted;
        if ((isset($_SESSION['applicant_id'])) && ($_SESSION['applicant_id'] == $this->_id)) {
	    return true;
        } 
	if ($_SESSION['rights'] == "admin" && $_SESSION['md_rights'] = md5($CFG_salted.$_SESSION['rights'])) {
	    return true;
        }
        return false;
    }

    public function makeAddress($array) 
    {
        $str = $array['index'].", ".$array['regionname'].", ";
	if (($array['region'] != 77) && ($array['region'] != 78)) {
	    $str .= $array['city'].", ";
        }
	if ($array['street'] != '') {
	    $str .= $array['street'].", ";
        }
   	$str .= "дом ".$array['home'];
	if ($array['building'] != '') {
	    $str .= "/".$array['building'];
        }
   	if ($array['flat'] != '') {
	    $str .= ", ".$array['flat'];
        }
	return $str;
    }

    public function getShort() 
    {
        if ($this->second_name != "") {
   	    return $this->surname." ".substr($this->name,0,2).".".substr($this->second_name,0,2).".";
	} else {
	    return $this->surname." ".substr($this->name,0,2).".";
	}
    }

    public function getShortR() 
    {
        if ($this->second_name != "") {
   	    return substr($this->name,0,2).".".substr($this->second_name,0,2).". ".$this->surname;
	} else {
	    return substr($this->name,0,2).". ".$this->surname;
	}
    }

    public function inflection() {
    	switch ($this->sex) {
	case 'M':
	    return "ый";
	case 'F':
	    return "ая";
	default:
	    return "ый(ая)";
	}
    }

    public function getInfo() 
    {
        $arr = func_get_args();
	$query = "";
    	if (func_num_args() == 0) {
	    $query = "*";
        } else {
            foreach($arr as $v) {
	        if ($query != '') {
	            $query .= ",";
                }
	    	switch ($v) {
	    	case 'passport':
	            $query .= "doc_serie,doc_number,doc_date,doc_issued";
		    break;
		case 'email':
	            $query .= "`e-mail`";
	            break;
		default:
	            $query .= "`".$v."`";
	        }
	    }
	}
	return $this->_msl->getarray("SELECT ".$query." FROM reg_applicant WHERE id='".$this->_id."' LIMIT 1",0);
    }

    public function getAddress($type=0) 
    {
        if ($type > 0) {
	    $cond = " AND `type`=".$type;
	} else {
	    $cond = "";
	}
        return $this->_msl->getarray("SELECT a.*,b.name as regionname FROM reg_applicant_address a LEFT JOIN reg_rf_subject b ON a.region=b.id WHERE a.applicant_id='".$this->_id."'".$cond." ORDER BY type",1);
    }

    public function getRegAddress() 
    {
        $array = $this->getAddress(1);
	return $this->makeAddress($array[0]);
    }

    public function getEduDoc() 
    {
        return $this->_msl->getarray("SELECT edu_doc, serie, number, institution, specialty, date, copy FROM reg_applicant_edu_doc WHERE applicant='".$this->_id."' AND `primary`='1' LIMIT 1",0);
    }

    public function getRups() 
    {
        return $this->_msl->getarray("SELECT rups, pay FROM reg_institution_additional WHERE applicant_id='".$this->_id."' LIMIT 1",0);
    }

    public function getEge() 
    {
        return $this->_msl->getarray("SELECT name, score, document FROM reg_applicant_scores LEFT JOIN reg_subjects ON reg_applicant_scores.subject = reg_subjects.id WHERE `applicant_id` = ".$this->_id." AND `ege` = 1 ORDER BY subject ASC", 1);
    }

    public function printDocs($prefix="", $remarks=0) 
    {
        $rvalx = $this->getInfo('birthday');

        if ($remarks == 1) print "<tr><td colspan=2 style=\"font-color:red;\">Обратите внимание! Заявление абитуриента и договоры двухсторонние</td></tr>";
        if ($this->semestr == 1) { 
	    print "<tr><td><A href=\"".$prefix."documents/anketa3.php?applicant=".$this->_id."\">Заявление абитуриента</A></td></tr>\n";
	} else {
	    print "<tr><td><A href=\"".$prefix."documents/anketa2.php?applicant=".$this->_id."\">Заявление абитуриента</A></td></tr>\n";
	    print "<tr><td><A href=\"".$prefix."documents/perez.php?applicant_id=".$this->_id."\">Заявление о перезачете дисциплин</A></td></tr>\n";
	    
	    $ival = $this->getRups();
            if ($ival['pay'] > 0) {
		print "<tr><td><A href=\"".$prefix."documents/ds_ckt.php?applicant_id=".$this->_id."\">Дополнительное соглашение</A>";
		if ($remarks == 1) print " (2 экземпляра)";
		print "</td></tr>\n";
		print "<tr><td><A href=\"".$prefix."receipt/kvit.php?purpose=3&applicant_id=".$this->_id."\">Квитанция для оплаты досдач</A></td></tr>\n";
	    }
	}
//	print "<tr><td><A href=\"".$prefix."documents/opd.php?applicant_id=".$this->_id."\">Анкета-согласие на обработку персональных данных</A></td></tr>\n";
	print "<tr><td><A href=\"".$prefix."documents/dog_ckt.php?applicant_id=".$this->_id."\">Договор на оказание платных образовательных услуг</A>"; 
	if ($remarks == 1) print " (3 экземпляра)";
	print "</td></tr>\n";
	print "<tr><td><A href=\"".$prefix."documents/dog_ckt_s.php?applicant_id=".$this->_id."\">Договор об организации обучения гражданина на платной основе</A>";
	if ($remarks == 1) print " (2 экземпляра)";
	print "</td></tr>\n";
	/* print "<tr><td><A href=\"".$prefix."documents/diplom.php?applicant_id=".$this->_id."\">Заявление на возврат оригинала документа об образовании</A>";
	if ($remarks == 1) print " (даты не ставить)";
	print "</td></tr>\n";
	*/
	print "<tr><td><A href=\"".$prefix."documents/opis.php?applicant_id=".$this->_id."\">Опись документов личного дела</A></td></tr>\n";
	print "<tr><td><A href=\"".$prefix."documents/ekz_list.php?applicant=".$this->_id."\">Экзаменационный лист</A> (только для проходивших вступительные испытания)</td></tr>\n";
	print "<tr><td><A href=\"".$prefix."receipt/kvit.php?applicant_id=".$this->_id."\">Квитанция на оплату обучения</A></td></tr>\n";
	
        if ((time()-strtotime($rvalx['birthday']))<567648000) {
	    print "<TR><TD><A href=\"".$prefix."documents/pdf/dop_net_18.pdf\" target=\"_blank\">Дополнение к договору</A> (2 экземпляра, если нет 18 лет)</TD></TR>\n";	
	}
    }
}
?>