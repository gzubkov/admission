<?php
class Catalog
{
    var $msl;

    public function __construct(&$msl) {
        $this->msl = $msl; 
        return true;
    }

    public function getAvailableByPgid($pgid, $string="%abbr% - %name% (%base%)", $archivetext=NULL, $viewable=1, $applicable=1) 
    {
        $catalogs = array();
	
	$query = "SELECT g.abbreviation, g.abbreviation2, a.id, a.basicsemestr, c.name, d.short, c.spec_code, c.qualify FROM catalogs a 
                  LEFT JOIN price_groups b ON a.id=b.catalog  
                  LEFT JOIN specialties c ON a.specialty=c.id 
		  LEFT JOIN education_type d ON a.baseedu=d.id 
		  LEFT JOIN admission.`universities_departments` e ON c.department=e.id 
                  LEFT JOIN admission.`universities_faculties` f ON e.faculty=f.id 
		  LEFT JOIN admission.`universities` g ON f.university=g.id WHERE b.id='".$pgid."' ";
        if ($archivetext == NULL) {
	    $query .= "AND a.archive = '0' "; //AND a.applicable = '1' ";
	    if ($applicable == 1) {
	        $query .= "AND a.applicable = '1' ";
	    }
	}
	if ($viewable == 1) $query .= "AND a.viewable = '1' "; 
	$query.= "ORDER BY a.id ASC";
        $rval = $this->msl->getArray($query,1); 

	foreach($rval as $v) {
	    $replace = array("%abbr%" => $v['abbreviation'],
	                     "%abbr2%" => $v['abbreviation2'],
	                     "%name%" => $v['name'],
	                     "%base%" => $v['short'],
			     "%code%" => $v['spec_code'],
			     "%qualify%" => $v['qualify'],
			     "%basicsemestr%" => ($v['basicsemestr'] == 1) ? "(новый)" : "");

	    $catalogs[$v['id']] = strtr($string, $replace);
	    if (isset($v['archive'])) {
	        if ($v['archive'] > 0 && $archivetext != "") {
	            $catalogs[$v['id']] .= " (".$archivetext.")"; 
	        }
	    }
	}
	return $catalogs;
    }

    public function getAvailableByRegion($region, $string="%abbr% - %name% (%base%)", $archivetext=NULL, $viewable=1, $applicable=1) 
    {
        $pgid = $this->msl->getarray("SELECT pgid FROM partner_regions WHERE id='".$region."' LIMIT 1",0);
	return $this->getAvailableByPgid($pgid['pgid'], $string, $archivetext, $viewable, $applicable);
    }

    public function getInfo($catalog, $profile=0) 
    {
        $info = $this->msl->getarray("SELECT a.id, a.name, a.spec_code, a.qualify, a.shortname, b.term, b.termm 
                          FROM `admission`.`specialties` a LEFT JOIN catalogs b ON a.id=b.specialty
                          WHERE b.id='".$catalog."'");
	switch($info['spec_code'][strlen($info['spec_code'])-1]) {
            case 2:
	        $info['type'] = "направление подготовки";
	        $info['typen'] = 2;
	        break;

	    default:
	        $info['type'] = "специальность";
	        $info['typen'] = 1;
	        break;
	}

	$info['termtext'] = $info['term'];
	if ($info['term'] < 5) {
	    $info['termtext'] .= " года";
	} else {
	    $info['termtext'] .= " лет";
	}
	if ($info['termm'] > 0) {
	    $info['termtext'] .= " ".$info['termm']." мес";
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
        $profile = $this->msl->getarray("SELECT a.catalog, b.name FROM `admission`.`catalogs_profiles` a LEFT JOIN `admission`.`specialties_profiles` b ON a.profile=b.id WHERE a.base_id='".$base_id."'");

	if ($profile['catalog'] != 0) {
	    $spec = $this->msl->getarray("SELECT f.abbreviation, b.name FROM admission.catalogs a 
                  LEFT JOIN admission.specialties b ON a.specialty=b.id 
                  LEFT JOIN admission.`universities_departments` c ON b.department=c.id 
                  LEFT JOIN admission.`universities_faculties` d ON c.faculty=d.id 
		  LEFT JOIN admission.`universities` f ON d.university=f.id 		  
                  WHERE a.id='".$profile['catalog']."'");

	    $spec['profile'] = $profile['name'];
	} else {
            $spec = $this->msl->getarray("SELECT f.abbreviation, b.name FROM admission.catalogs a 
                  LEFT JOIN admission.specialties b ON a.specialty=b.id 
                  LEFT JOIN admission.`universities_departments` c ON b.department=c.id 
                  LEFT JOIN admission.`universities_faculties` d ON c.faculty=d.id 
		  LEFT JOIN admission.`universities` f ON d.university=f.id 		  
                  WHERE a.base_id='".$base_id."'");
        }
	return $spec;
    }

    public function getSpecialtiesByPgid($pgid) 
    {
        $catalogs = array();

	$query = "SELECT a.id, CONCAT(c.spec_code,' ',c.name,' (',c.qualify,')') as specialty FROM catalogs a 
                  LEFT JOIN price_groups b ON a.id=b.catalog AND b.id='".$pgid."' 
                  LEFT JOIN specialties c ON a.specialty=c.id GROUP BY a.specialty
		  ORDER BY a.id ASC";
        return $this->msl->getArrayById($query,'id','specialty'); 
    }

    public function getSubCatalogsByRegion($region, $catalog, $archive=0, $applicable=0) 
    {
        $pgid = $this->msl->getarray("SELECT pgid FROM partner_regions WHERE id='".$region."' LIMIT 1",0);
	$specialty = $this->msl->getarray("SELECT specialty FROM catalogs WHERE id='".$catalog."' LIMIT 1", 0);
	
	$query = "SELECT a.id, b.short FROM catalogs a 
	          LEFT JOIN education_type b ON a.baseedu=b.id 
		  LEFT JOIN price_groups c ON a.id=c.catalog AND c.id='".$pgid."' 
		  WHERE a.specialty='".$specialty['specialty']."'";
        if ($archive == 0) {
	    $query .= " AND a.archive=0";
	}
        if ($applicable == 1) {
            $query .= " AND a.applicable=1";
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

    public function getAvailableSpecialtiesByPgid($pgid, $string="%abbr% - %name% (%qualify%)", $archivetext=NULL) 
    {
        $catalogs = array();
	
	$query = "SELECT g.abbreviation,g.abbreviation2, a.id, c.name, d.short, c.spec_code, c.qualify FROM catalogs a 
                  LEFT JOIN price_groups b ON a.id=b.catalog  
                  LEFT JOIN specialties c ON a.specialty=c.id 
		  LEFT JOIN education_type d ON a.baseedu=d.id 
		  LEFT JOIN admission.`universities_departments` e ON c.department=e.id 
                  LEFT JOIN admission.`universities_faculties` f ON e.faculty=f.id 
		  LEFT JOIN admission.`universities` g ON f.university=g.id WHERE b.id='".$pgid."' ";
        if ($archivetext == NULL) $query .= "AND a.archive = '0' AND a.applicable = '1' ";
	$query.= "GROUP BY a.specialty ORDER BY c.qualify ASC, a.id DESC";
        $rval = $this->msl->getArray($query,1); 

	foreach($rval as $v) {
	    $replace = array("%abbr%" => $v['abbreviation'],
	                     "%abbr2%" => $v['abbreviation2'],
	                     "%name%" => $v['name'],
	                     "%shortname%" => mb_substr($v['name'],0,25),
	                     "%base%" => $v['short'],
			     "%code%" => $v['spec_code'],
			     "%qualify%" => $v['qualify']);

	    $catalogs[$v['id']] = strtr($string, $replace);
	    if (isset($v['archive']) && $v['archive'] > 0 && $archivetext != "") {
	        $catalogs[$v['id']] .= " (".$archivetext.")"; 
	    }
	}
	return $catalogs;
    }

    public function getProfiles($specialty) {
        $profile = $this->msl->getArray("SELECT id, name FROM `specialties_profiles` WHERE `specialty` = '".$specialty."'");
	return $profile;
    }

    public function getAllProfiles($internet=1) {
        $profile = $this->msl->getArrayById("SELECT id, name FROM `specialties_profiles` WHERE internet=".$internet.";", 'id','name');
	return $profile;
    }

}
