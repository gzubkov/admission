<?php
class FabricApplicant
{
    public function __construct(&$f, $msl, $id)
    {
        $f = $this->_router($msl, $id);
    }

    private function _router($msl, $id)
    {
        if ($id[0] == 'r') {
            return new RegApplicant($msl, $id);
        }
        return new Applicant($msl, $id);
    }
}

class Applicant
{
    public $msl;
    public $_id;
    public $connum = 2; // количество договоров мы-студент
    public $tbl_prefix = "reg_";
    public $agent = 1;
    public $filePrefix = 'ckt';

    public $surname;
    public $name;
    public $second_name;
    public $region;

    public $catalog;
    public $profile;
    public $semestr;
    public $type;
    public $internet;

    public function __construct($msl, $id)
    {
        $this->msl = $msl;
        $this->_id  = $id;
        
        if ($this->_checkSecurity() === false) {
            exit(0);
        }

        $r = $this->msl->getarray("SELECT surname,name,second_name,type,catalog,profile,semestr,region,internet FROM ".$this->tbl_prefix."applicant WHERE id = ".$this->_id." LIMIT 1;");
        $this->surname     = $r['surname'];
        $this->name        = $r['name'];
        $this->second_name = $r['second_name'];
        $this->region      = $r['region'];
        $this->type        = $r['type'];

        $this->catalog     = $r['catalog'];
        $this->profile     = $r['profile'];
        $this->semestr     = $r['semestr'];
        $this->internet    = $r['internet'];
        return true;
    }

    public function __destruct()
    {
        return true;
    }

    private function _checkSecurity()
    {
        global $CFG_salted;
        if ((isset($_SESSION['applicant_id'])) &&
            ($_SESSION['applicant_id'] == $this->_id)) {
            return true;
        }

        if (isset($_SESSION['rights']) === false) {
            return false;
        }

        if ($_SESSION['rights'] == "admin" &&
            $_SESSION['md_rights'] = md5($CFG_salted.$_SESSION['rights'])) {
            return true;
        }
        return false;
    }

    public function makeAddress($array)
    {
        $str = "";
        if ($array['index'] != 0) {
            $str .= $array['index'].", ";
        }
        
        if ($array['country'] != 1) {
            $str .= $array['countryname'].", ";
        } else {
            $str .= $array['regionname'].", ";
        }

        if (($array['region'] != 77) && 
            ($array['region'] != 78)) {
            $str .= $array['city'].", ";
        } else if ($array['region'] == 77) {
            if (strcasecmp($array['city'], 'Москва') != 0 &&
                strcasecmp($array['city'], 'г. Москва') != 0 &&
                strcasecmp($array['city'], 'г.Москва') != 0 &&
                $array['city'] != '') {
                $str .= $array['city'].", ";
            }
        }
    
        if ($array['street'] != '') {
            $str .= $array['street'].", ";
        }
        
        if ($array['home'] != 0) {
            $str .= "д. ".$array['home'];
        }

        if ($array['building'] != '') {
            $str .= "/".$array['building'];
        }

        if ($array['flat'] != '' && 
            $array['flat'] != 0) {
            $str .= ", кв. ".$array['flat'];
        }
        return $str;
    }

/*
Является ли он старше 18 лет?
*/

    public function isAdult()
    {
        $r = $this->msl->getarray("SELECT birthday FROM ".$this->tbl_prefix."applicant WHERE id = ".$this->_id." LIMIT 1;");

        if ((time()-strtotime($r['birthday'])) < 567648000) {
            return false;
        } else {
            return true;
        }
    }

    public function getShort()
    {
        if ($this->second_name != "") {
            return $this->surname." ".substr($this->name, 0, 2).".".substr($this->second_name, 0, 2).".";
        } else {
            return $this->surname." ".substr($this->name, 0, 2).".";
        }
    }

    public function getShortR()
    {
        if ($this->second_name != "") {
            return substr($this->name, 0, 2).".".substr($this->second_name, 0, 2).". ".$this->surname;
        } else {
            return substr($this->name, 0, 2).". ".$this->surname;
        }
    }

    public function inflection()
    {
        $info = $this->getInfo('sex');
        switch ($info['sex']) {
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
        if (func_num_args() == 0) {
            $query = "*";
        } else {
            $arr = func_get_args();
            $query = "";

            foreach ($arr as $v) {
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
        return $this->msl->getarray("SELECT ".$query." FROM ".$this->tbl_prefix."applicant WHERE id='".$this->_id."' LIMIT 1", 0);
    }

    public function getAddress($type = 0)
    {
        if ($type > 0) {
            $cond = " AND `type`=".$type;
        } else {
            $cond = "";
        }
        return $this->msl->getarray("SELECT a.*,b.name as regionname, c.name as countryname FROM ".$this->tbl_prefix."applicant_address a 
                                     LEFT JOIN reg_rf_subject b ON a.region=b.id
                                     LEFT JOIN country c ON a.country=c.id 
                                     WHERE a.applicant_id='".$this->_id."'".$cond." ORDER BY type", 1);
    }

    public function getRegAddress()
    {
        $array = $this->getAddress(1);
        return $this->makeAddress($array[0]);
    }

    public function getEduDoc()
    {
        return $this->msl->getarray("SELECT edu_doc, serie, number, institution, specialty, date, copy FROM reg_applicant_edu_doc WHERE applicant='".$this->_id."' AND `primary`='1' LIMIT 1", 0);
    }

    public function getRups()
    {
        return $this->msl->getarray("SELECT rups, pay FROM reg_institution_additional WHERE applicant_id='".$this->_id."' LIMIT 1", 0);
    }

    public function getEge()
    {
        return $this->msl->getarray("SELECT b.name, a.score, a.year, b.min FROM reg_applicant_scores a LEFT JOIN reg_subjects b ON a.subject = b.id WHERE `applicant_id` = ".$this->_id." AND `ege` = 1 ORDER BY subject ASC", 1);
    }

    public function getPhoneCode()
    {
        return $this->msl->getArray("SELECT b.phone_code FROM `reg_applicant_address` a LEFT JOIN `country` b ON a.country=b.id WHERE a.`applicant_id`='".$this->_id."' ORDER BY a.type ASC LIMIT 1");
    }

    public function printDocs($prefix = "", $remarks = 0)
    {
        $rvalx = $this->getInfo('birthday');

        if ($remarks == 1) {
            print "<tr><td colspan=2 style=\"font-color:red;\">Обратите внимание! Заявление абитуриента и договоры двухсторонние</td></tr>";
        }

        if ($this->semestr == 1) {
            print "<tr><td><A href=\"".$prefix."documents/anketa.php?applicant=".$this->_id."\">Заявление абитуриента</A></td></tr>\n";
        } else {
            echo "<tr><td><A href=\"".$prefix."documents/reinstatement.php?applicant=".$this->_id."\">Заявление о восстановлении</A></td></tr>\n";

            $ival = $this->getRups();
            if ($ival['pay'] > 0) {
                print "<tr><td><A href=\"".$prefix."documents/ds_ckt.php?applicant=".$this->_id."\">Дополнительное соглашение</A>";
                if ($remarks == 1) {
                    print " (2 экземпляра)";
                }
                print "</td></tr>\n";
                print "<tr><td><A href=\"".$prefix."receipt/kvit.php?purpose=3&applicant_id=".$this->_id."\">Квитанция для оплаты досдач</A></td></tr>\n";
            }
        }
        print "<tr><td>Вы прошли вступительные испытания. Ваши документы находятся на обработке. Будет сообщено дополнительно.</td></tr>";
/*
        print "<tr><td><A href=\"".$prefix."documents/dog_mami.php?applicant=".$this->_id."\">Договор на оказание платных образовательных услуг</A>";
        if ($remarks == 1) {
            print " (3 экземпляра)";
        }
        print "</td></tr><tr><td><A href=\"".$prefix."documents/dog_ckt_s.php?applicant=".$this->_id."\">Договор об организации обучения гражданина на платной основе</A>";
        if ($remarks == 1) {
            print " (2 экземпляра)";
        }
        print "</td></tr>\n";
        print "<tr><td><A href=\"".$prefix."documents/opis.php?applicant_id=".$this->_id."\">Опись документов личного дела</A></td></tr>\n";

        // If applicant hasn't ege
        $egeArray = $this->getEge();
        if (is_array($egeArray) === false || 
            sizeof($egeArray) < 3) {
            echo "<tr><td><A href=\"".$prefix."documents/ekz_list.php?applicant=".$this->_id."\">Экзаменационный лист</a></td></tr>\n";
        }
        print "<tr><td><A href=\"".$prefix."receipt/kvit.php?applicant_id=".$this->_id."\">Квитанция на оплату обучения</a></td></tr>\n";
*/
        // If applicant's age is less than 18
        if ($this->isAdult() === false) {
            echo "<TR><TD><A href=\"".$prefix."documents/pdf/dop_net_18.pdf\" target=\"_blank\">Дополнение к договору</a> (2 экземпляра, если нет 18 лет)</TD></TR>\n";
        }
    }
}

class RegApplicant extends Applicant
{
    public $msl;
    public $_id;
    public $connum = 3; // количество договоров мы-студент
    public $tbl_prefix = "partner_";

    public $agent = 1;
    public $filePrefix = 'ckt';

    public function __construct($msl, $id)
    {
        $this->msl = $msl;
        $this->_id = substr($id, 1);

        if ($this->_checkSecurity() === false) {
            exit(0);
        }

        $r = $this->msl->getarray("SELECT surname,name,second_name,type,catalog,profile,semestr,region,agent FROM ".$this->tbl_prefix."applicant WHERE id = ".$this->_id." LIMIT 1;");
        $this->surname     = $r['surname'];
        $this->name        = $r['name'];
        $this->second_name = $r['second_name'];
        $this->type        = $r['type'];

        $this->catalog     = $r['catalog'];
        $this->profile     = $r['profile'];
        $this->semestr     = $r['semestr'];

        $this->region      = $r['region'];
        
        if ($this->region <= 5) {
            $this->connum = 2;
        }

        $this->agent = $r['agent'];
        if ($r['agent'] == 2) {
            $this->filePrefix = 'iit';
        } 
        return true;
    }

    public function __destruct()
    {
        return true;
    }

    private function _checkSecurity()
    {
        return true;
    }

    public function getEge()
    {
        return $this->msl->getarray("SELECT name, score, year FROM partner_applicant_scores a 
                                     LEFT JOIN reg_subjects ON a.subject = reg_subjects.id 
                                     WHERE `applicant_id` = ".$this->_id." AND `ege` = 1 ORDER BY subject ASC", 1);
    }

    public function getEduDoc()
    {
        return $this->msl->getarray("SELECT edu_doc, edu_serie as serie, edu_number as number, edu_institution as institution, 
                                            edu_city as city, edu_specialty as specialty, edu_date as date FROM partner_applicant 
                                     WHERE id='".$this->_id."' LIMIT 1", 0);
    }
    
    public function getRegion()
    {
        $rval = $this->msl->getarray("SELECT a.*, b.name as gpos, b.name_rp as gposrp, c.name_tp as orgdoc FROM `partner_regions` a 
                                      LEFT JOIN `partner_position` b ON a.gposition=b.id 
                                      LEFT JOIN `partner_organizational_documents` c ON a.orgdoc=c.id 
                                      WHERE a.id = ".$this->region.";");
        return $rval;
    }

    public function getRups()
    {
        return $this->msl->getarray("SELECT pay FROM partner_applicant WHERE id='".$this->_id."' LIMIT 1", 0);
    }

    public function printDocs($prefix = "../", $remarks = 1)
    {
        $rvalx = $this->getInfo('birthday', 'pay');

        if ($remarks == 1) {
            echo "<tr><td colspan=2 style=\"font-color:red;\"><b>Обратите внимание! Заявление абитуриента и договоры двухсторонние</b></td></tr>";
        }

        if ($this->semestr == 1) {
            echo "<tr><td><A href=\"".$prefix."documents/anketa.php?applicant=r".$this->_id."\">Заявление абитуриента</A></td></tr>\n";
        } else {
            echo "<tr><td><A href=\"".$prefix."documents/reinstatement.php?applicant=r".$this->_id."\">Заявление о восстановлении</A></td></tr>\n";

            if ($rvalx['pay'] > 0) {
                if ($this->connum == 2) {
                    echo "<tr><td><a href=\"".$prefix."documents/ds_ckt.php?applicant=r".$this->_id."\">Дополнительное соглашение</a>";
                } else {
                    echo "<tr><td><A href=\"".$prefix."documents/ds_ckt_rp.php?applicant=r".$this->_id."\">Дополнительное соглашение</A>";
                }

                if ($remarks == 1) {
                    echo " (".$this->connum." экземпляра)";
                }
                echo "</td></tr><tr><td><A href=\"".$prefix."receipt/kvit.php?purpose=3&applicant_id=r".$this->_id."\">Квитанция для оплаты досдач</A></td></tr>\n";
            }
        }

        echo "<tr><td><a href=\"".$prefix."documents/dog_mami.php?applicant=r".$this->_id."\">Договор на оказание платных образовательных услуг</a>";
        if ($remarks == 1) {
            echo " (3 экземпляра)";
        }
        echo "</td></tr>\n<tr><td>";

        if ($this->connum == 2) {
            echo "<a href=\"".$prefix."documents/dog_ckt_s.php?applicant=r".$this->_id."\">";
        } else {
            echo "<a href=\"".$prefix."documents/dog_rp.php?applicant=r".$this->_id."\">";
        }
        echo "Договор об организации обучения гражданина на платной основе</A>";

        if ($remarks == 1) {
            echo " (".$this->connum." экземпляра)";
        }
        echo "</td></tr>\n";

        echo "<tr><td><A href=\"".$prefix."documents/opis.php?applicant_id=r".$this->_id."\">Опись документов личного дела</A></td></tr>\n";
        echo "<tr><td><A href=\"".$prefix."documents/ekz_list.php?applicant=r".$this->_id."\">Экзаменационный лист</A> (только для проходивших вступительные испытания)</td></tr>\n";
        echo "<tr><td><A href=\"".$prefix."documents/exam.php?applicant=r".$this->_id."\">Листы тестирования</A> (только для проходивших вступительные испытания)</td></tr>\n";
        echo "<tr><td><A href=\"".$prefix."receipt/kvit.php?applicant_id=r".$this->_id."\">Квитанция на оплату обучения</A></td></tr>\n";
    
        if ($this->isAdult() === false) {
            echo "<tr><td><a href=\"".$prefix."documents/pdf/dop_net_18.pdf\" target=\"_blank\">Дополнение к договору</a> (2 экземпляра, если нет 18 лет)</td></tr>\n";
        }
    }

    public function setNum($num)
    {
        $this->msl->updateArray('partner_applicant', array('num' => $num), array('id' => $this->_id));
    }
}
