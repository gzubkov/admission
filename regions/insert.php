<?php
//require("../../conf.php");
//require_once('../class/mysql.class.php');
//Header("Content-Type: text/javascript; charset=utf-8");

class Insertion
{
    public $mslk;

    public function __construct() {
        $this->mslk = new dMysql();
        return 0;
    }
   
    public function __destruct() {
        return 0;
    }

    private function _makeDate($date) 
    {
        return implode("-", array_reverse(explode(".",$date)));
    }
   
    private function _makeTitle($text) 
    {
        return mb_convert_case($text, MB_CASE_TITLE, "UTF-8");
    }
   
    public function newApplicant($uid, $request) 
    {
        $array = $request;
	$array['surname']     = $this->_makeTitle($array['surname']);
	$array['name']        = $this->_makeTitle($array['name']);
	$array['second_name'] = $this->_makeTitle($array['second_name']);
	$array['birthday']    = $this->_makeDate($array['birthday']);
	$array['doc_date']    = $this->_makeDate($array['doc_date']);
	$array['ip']          = sprintf('%u', ip2long($_SERVER['REMOTE_ADDR'])); 

	$arr  = $array['homeaddress'];
	$arr2 = $array['regaddress'];
	$arr3 = $array['ege'];

	unset($array['homeaddress']);	
	unset($array['regaddress']);	
	unset($array['citizenrynull']);
	unset($array['ege']);
	unset($array['act']);

	$applicant_id = $this->mslk->insertArray('partner_applicant', $array);
		     
	$arr['applicant_id'] = $applicant_id;
	$arr['type']  = 2;
        $arr2['applicant_id'] = $applicant_id;
	$arr2['type'] = 1;
        $this->mslk->insertArray('partner_applicant_address', $arr2);
    	$this->mslk->insertArray('partner_applicant_address', $arr);
	
	if ($array['traditional_form'] == 0) {
	    $this->insertEge($uid, $arr3, $applicant_id);
	}
    	return $applicant_id;
    }

   
    public function updateApplicant($uid, $request) 
    {
        $array = $request;
	$array['birthday']    = $this->_makeDate($array['birthday']);
	$array['doc_date']    = $this->_makeDate($array['doc_date']);
	$array['edu_date']    = $this->_makeDate($array['edu_date']);
	
	$arr  = $array['homeaddress'];
	$arr2 = $array['regaddress'];
	$arr3 = $array['ege'];

	unset($array['homeaddress']);	
	unset($array['regaddress']);	
	unset($array['citizenrynull']);
	
	unset($array['id']);
	unset($array['ege']);
	unset($array['act']);
	unset($array['region']);
	
	$this->mslk->updateArray('partner_applicant_address', $arr2, array('applicant_id'=>$request['id']));
    	$this->mslk->updateArray('partner_applicant_address', $arr, array('applicant_id'=>$request['id']));
	
	if ($array['traditional_form'] == 0) {
	    $this->insertEge($uid, $arr3, $applicant_id);
	}
    	
	$array['create_date'] = time();
	return $this->mslk->updateArray('partner_applicant', $array, array('id'=>$request['id']));
    }


    public function insertEge($uid, $request, $id) 
    {
        $this->mslk->deleteArray('partner_applicant_scores', array('applicant_id' => $id));
        foreach ($request as $val) {
	    if ($val['score'] > 0) {
	        $val['applicant_id'] = $id;
	    	$val['ege'] = 1;

	    	$this->mslk->insertArray('partner_applicant_scores', $val);
            }
	}
	return true;
    }
}
?>

