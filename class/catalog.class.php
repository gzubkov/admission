<?php
class Catalog
{
    public function __construct() {
        return true;
    }

    public function getAvailableByPgid($pgid, $string="%abbr% - %name% (%base%)", $archivetext=NULL) {
        $catalogs = array();
	
	$query = "SELECT g.abbreviation, a.id, a.basicsemestr, c.name, d.short, c.spec_code, c.qualify FROM catalogs a 
                  LEFT JOIN price_groups b ON a.id=b.catalog  
                  LEFT JOIN specialties c ON a.specialty=c.id 
		  LEFT JOIN education_type d ON a.baseedu=d.id 
		  LEFT JOIN admission.`universities_departments` e ON c.department=e.id 
                  LEFT JOIN admission.`universities_faculties` f ON e.faculty=f.id 
		  LEFT JOIN admission.`universities` g ON f.university=g.id WHERE b.id='".$pgid."' ";
        if ($archivetext == NULL) $query .= "AND a.archive = '0' AND a.applicable = '1' ";
	$query.= "ORDER BY a.id ASC";
        $rval = getArray($query,1); 

	foreach($rval as $v) {
	    $replace = array("%abbr%" => $v['abbreviation'],
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

    public function getAvailableByRegion($region, $string="%abbr% - %name% (%base%)", $archivetext=NULL) {
        $pgid = getarray("SELECT pgid FROM partner_regions WHERE id='".$region."' LIMIT 1",0);
	return $this->getAvailableByPgid($pgid['pgid'], $string, $archivetext);
    }

    public function getInfo($catalog, $profile=0) {
        $info = getarray("SELECT a.id, a.name, a.spec_code, a.qualify, a.shortname, b.term, b.termm 
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
	if ($info['termm'] == '6') {
	    $info['term'] += 0.5;
	}
	if ($profile != 0) {
	    $prof = getarray("SELECT name FROM `admission`.`specialties_profiles` WHERE id='".$profile."' LIMIT 1;");
	    $info['profile'] = $prof['name'];
	}
	return $info;
    }

    public function getSpecialtiesByPgid($pgid) {
        $catalogs = array();

	$query = "SELECT a.id, CONCAT(c.spec_code,' ',c.name,' (',c.qualify,')') as specialty FROM catalogs a 
                  LEFT JOIN price_groups b ON a.id=b.catalog AND b.id='".$pgid."' 
                  LEFT JOIN specialties c ON a.specialty=c.id GROUP BY a.specialty
		  ORDER BY a.id ASC";
        return getArrayById($query,'id','specialty'); 
    }

    public function getSubCatalogsByRegion($region, $catalog, $archive=0) {
        $pgid = getarray("SELECT pgid FROM partner_regions WHERE id='".$region."' LIMIT 1",0);
	$specialty = getarray("SELECT specialty FROM catalogs WHERE id='".$catalog."' LIMIT 1", 0);
	
	$query = "SELECT a.id, b.short FROM catalogs a 
	          LEFT JOIN education_type b ON a.baseedu=b.id 
		  LEFT JOIN price_groups c ON a.id=c.catalog AND c.id='".$pgid."' 
		  WHERE a.specialty='".$specialty['specialty']."'";
        if ($archive == 0) {
	    $query .= " AND a.archive=0";
	}
	return getArrayById($query,'id','short'); 
    }

    public function getUniversityInfo($catalog, $base=0) {
        return getarray("SELECT f.* FROM admission.catalogs a 
                  LEFT JOIN admission.specialties b ON a.specialty=b.id 
                  LEFT JOIN admission.`universities_departments` c ON b.department=c.id 
                  LEFT JOIN admission.`universities_faculties` d ON c.faculty=d.id 
		  LEFT JOIN admission.`universities` f ON d.university=f.id 		  
                  WHERE a.".(($base == 0)?"id":"base_id")."='".$catalog."' LIMIT 1", 0);
    }   

    public function getAvailableSpecialtiesByPgid($pgid, $string="%abbr% - %name% (%qualify%)", $archivetext=NULL) {
        $catalogs = array();
	
	$query = "SELECT g.abbreviation, a.id, c.name, d.short, c.spec_code, c.qualify FROM catalogs a 
                  LEFT JOIN price_groups b ON a.id=b.catalog  
                  LEFT JOIN specialties c ON a.specialty=c.id 
		  LEFT JOIN education_type d ON a.baseedu=d.id 
		  LEFT JOIN admission.`universities_departments` e ON c.department=e.id 
                  LEFT JOIN admission.`universities_faculties` f ON e.faculty=f.id 
		  LEFT JOIN admission.`universities` g ON f.university=g.id WHERE b.id='".$pgid."' ";
        if ($archivetext == NULL) $query .= "AND a.archive = '0' AND a.applicable = '1' ";
	$query.= "GROUP BY a.specialty ORDER BY c.qualify ASC, a.id DESC";
        $rval = getArray($query,1); 

	foreach($rval as $v) {
	    $replace = array("%abbr%" => $v['abbreviation'],
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
        $profile = getArray("SELECT id, name FROM `specialties_profiles` WHERE `specialty` = '".$specialty."'");
	return $profile;
    }

}
?>
