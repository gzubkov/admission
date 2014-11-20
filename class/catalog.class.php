<?php
class Catalog
{
    var $msl;

    public function __construct($msl) 
    {
        $this->msl = $msl; 
        return true;
    }

    private function _getCatalogName($array, $string, $archivetext) 
    {
    	$catalogs = array();
        foreach ($array as $v) {
        	$replace = array("%abbr%" => $v['abbreviation'],
	                         "%abbr2%" => $v['abbreviation2'],
	                         "%name%" => $v['name'],
	                         "%shortname%" => mb_substr($v['name'],0,25),
	                         "%base%" => $v['short'],
		                     "%code%" => $v['code'],
		                     "%qualify%" => $v['qualify']);
        
            if ($v['code'] == 0) {
            	$replace['%code%'] = $v['spec_code'];
            }

            if ($v['basicsemestr'] == 1) {
        	    $replace['%basicsemestr%'] = "новый";
            } else {
        	    $replace['%basicsemestr%'] = "";
            }

            $catalogs[$v['id']] = strtr($string, $replace);
	        if (isset($v['archive']) === TRUE) {
	            if ($v['archive'] > 0 && 
                    $archivetext != "") {
	                $catalogs[$v['id']] .= " (".$archivetext.")"; 
	            }
	        }
	    }
	    return $catalogs;
    }

    public function getAvailableSpecialtiesByPgid($pgid, $string="%abbr% - %name% (%qualify%)", $archivetext=NULL) 
    {
        return $this->getAvailableByPgid($pgid, $string, $archivetext, 1,1,1); // проверить!
    }

    public function getAvailableByPgid($pgid, $string="%abbr% - %name% (%base%)", $archivetext=NULL, $viewable=1, $applicable=1, $groupcat=0) 
    {
        $catalogs = array();
	
	    $query = "SELECT g.abbreviation, g.abbreviation2, a.id, a.basicsemestr, c.name, d.short, c.code, c.qualify, c.spec_code FROM catalogs a 
                  LEFT JOIN price b ON a.id=b.catalog
                  LEFT JOIN specialties c ON a.specialty=c.id 
		          LEFT JOIN education_type d ON a.baseedu=d.id 
		          LEFT JOIN admission.`universities_departments` e ON c.department=e.id 
                  LEFT JOIN admission.`universities_faculties` f ON e.faculty=f.id 
		          LEFT JOIN admission.`universities` g ON f.university=g.id WHERE b.group='".$pgid."'";
        
        if ($archivetext == NULL) {
	        $query .= "AND a.archive = '0' "; //AND a.applicable = '1' ";
	        if ($applicable == 1) {
	            $query .= "AND a.applicable = '1' ";
	        }
	    }
	
	    if ($viewable == 1) {
	    	$query .= "AND a.viewable = '1' "; 
	    }

	    if ($groupcat == 1) {
	    	$query .= "GROUP BY a.specialty ";
	    }
	    $query .= "ORDER BY a.id ASC";
        $rval = $this->msl->getArray($query,1); 

	    return $this->_getCatalogName($rval, $string, $archivetext);
    }

    public function getAvailableByRegion($region, $string="%abbr% - %name% (%base%)", $archivetext=NULL, $viewable=1, $applicable=1) 
    {
        $pgid = $this->msl->getarray("SELECT pgid FROM partner_regions WHERE id='".$region."' LIMIT 1",0);
	    return $this->getAvailableByPgid($pgid['pgid'], $string, $archivetext, $viewable, $applicable);
    }

    public function getInfo($catalog, $profile = 0, $semestr = 1) 
    {
        $info = $this->msl->getarray("SELECT a.id, a.name, a.spec_code, a.qualify, a.shortname, a.code, b.term, b.termm 
                          FROM `admission`.`specialties` a 
                          LEFT JOIN catalogs b ON a.id=b.specialty
                          WHERE b.id='".$catalog."'");

        // Переход от старых к новым кодам, если новый код не указан, то разбираем старый.

        if ($info['code'] == NULL) {
	        switch($info['spec_code'][strlen($info['spec_code'])-1]) {
                case 2:
	                $info['type'] = "направление подготовки";
	                $info['normterm'] = 4;
	                break;
                default:
	                $info['type'] = "специальность";
	                $info['normterm'] = 5;
	        }

	        $info['code'] = $info['spec_code'];
        } else {
            switch($info['code'][4]) {
                case 3:
                default:
                    $info['type'] = "направление подготовки";
	                $info['normterm'] = 4;
            }
        }

        // Срок обучения (массив(лет, месяцев)).

        $info['indterm'] = array($info['term'], $info['termm']);
        if ($info['indterm'][1] == NULL) {
            $info['indterm'][1] = 0;
        }

        // Максимальный семестр обучения
        $info['maxsemestr'] = $info['indterm'][0]*2 + $info['indterm'][1];

        if ($semestr > 1) {
            // вычисляем индивидуальный срок обучения при 2 и выше семестре
            $semestr--;
            if ($semestr%2 == 1) {
                if ($info['indterm'][1] < 6) {
                    $info['indterm'][0]--;
                    $info['indterm'][1] += 12;
                }
                $info['indterm'][1] -= 6;
                $semestr--;
            }
            $info['indterm'][0] -= ($semestr/2);
        }

	    $info['termtext'] = $info['term'];
	    if ($info['term'] < 5) {
	        $info['termtext'] .= " года";
	    } else {
	        $info['termtext'] .= " лет";
	    }
	    if ($info['termm'] > 0) {
	        $info['termtext'] .= " ".$info['termm']." месяцев";
	    }

	    if ($info['termm'] == '6') {
	        $info['term'] += 0.5;
	    }
	
        if ($profile != 0) {
	        $prof = $this->msl->getarray("SELECT name FROM `admission`.`specialties_profiles` WHERE id='".$profile."' LIMIT 1;");
	        $info['profile'] = $prof['name'];
	    }
	    return $info;
    }

    public function getBaseInfo($base_id) 
    {
    	$spec = array();

        $profile = $this->msl->getarray("SELECT a.catalog, b.name FROM `admission`.`catalogs_profiles` a 
        	LEFT JOIN `admission`.`specialties_profiles` b ON a.profile=b.id WHERE a.base_id='".$base_id."' LIMIT 1");

	    if ($profile['catalog'] != 0) {
	        $cond = "a.id='".$profile['catalog']."'";
// необходимо проверить правильность возврата наименования профиля!
	        $spec['profile'] = $profile['name'];
	    } else {
            $cond = "a.`base_id`='".$base_id."'";
	    }
        $spec = $this->msl->getarray("SELECT f.abbreviation, b.name FROM admission.catalogs a 
                                      LEFT JOIN admission.specialties b ON a.specialty=b.id 
                                      LEFT JOIN admission.`universities_departments` c ON b.department=c.id 
                                      LEFT JOIN admission.`universities_faculties` d ON c.faculty=d.id 
		                              LEFT JOIN admission.`universities` f ON d.university=f.id 		  
                                      WHERE ".$cond." LIMIT 1");
	    return $spec;
    }

// получить список форм обучения для выбранной специальности (СО, СПО, ВПО...)
    public function getSubCatalogsByRegion($region, $catalog, $archive=0, $applicable=0) 
    {
        $query = "SELECT a.id, b.short FROM catalogs a 
	              LEFT JOIN education_type b ON a.baseedu=b.id 
		          LEFT JOIN price c ON a.id=c.catalog
                  LEFT JOIN partner_regions d ON c.group=d.id and d.id='".$region."' 
		          WHERE a.specialty=(SELECT specialty FROM catalogs WHERE id='".$catalog."' LIMIT 1)";
        
        if ($archive == 0) {
	        $query .= " AND a.archive=0";
	        if ($applicable == 1) {
                $query .= " AND a.applicable=1";
	        }
	    } 
	    return $this->msl->getArrayById($query,'id','short'); 
    }

    public function getUniversityInfo($catalog, $base=0) 
    {
        return $this->msl->getarray("SELECT f.* FROM admission.catalogs a 
                  LEFT JOIN admission.specialties b ON a.specialty=b.id 
                  LEFT JOIN admission.`universities_departments` c ON b.department=c.id 
                  LEFT JOIN admission.`universities_faculties` d ON c.faculty=d.id 
		          LEFT JOIN admission.`universities` f ON d.university=f.id 		  
                  WHERE a.".(($base == 0)?"id":"base_id")."='".$catalog."' LIMIT 1", 0);
    }   

    public function getAllProfiles($internet = 1, $applicable = 1) {
        $query = "SELECT b.id, b.name FROM `catalogs_profiles` a LEFT JOIN `specialties_profiles` b ON a.profile=b.id WHERE 1";
        if ($internet == 1) {
            $query .= " AND a.internet=1";
        }
        if ($applicable == 1) {
            $query .= " AND a.applicable=1";
        }
        $profile = $this->msl->getArrayById($query, 'id','name');
	    return $profile;
    }

    public function getSubjects($catalog)
    {
        return $this->msl->getArray("SELECT a.subject as id, c.name as subject, c.min, c.mid, c.mid_old, d.surname,d.name,d.second_name FROM `reg_ege_minscores` a 
                                     LEFT JOIN `catalogs` b ON a.specialty=b.specialty
                                     LEFT JOIN `reg_subjects` c ON a.subject=c.id
                                     LEFT JOIN `teachers` d ON c.teacher=d.id
                                     WHERE b.id = '".$catalog."'");
    }
}
