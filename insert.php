<?php
require("../conf.php");
require("../../modules/mysql.php");
//Header("Content-Type: text/javascript; charset=utf-8");
$msl = new dMysql();
        
class Insertion
{
    public function __construct() {
        return 0;
    }
   
    public function __destruct() {
        return 0;
    }

    private function _makeDate($date) {
        return implode("-", array_reverse(explode(".",$date)));
    }
   
    private function _makeTitle($text) {
        return mb_convert_case($text, MB_CASE_TITLE, "UTF-8");
    }
   


   private function validate($exp, $exp_r = 0) {
      $pattern['email'] = '^[a-zA-Z0-9_\-.]+@[a-zA-Z0-9_\-]+\.[a-zA-Z0-9\-.]+$';
      $pattern['int1+'] = '[0-9]+';     // любое количество цифр (1 и более)
      $pattern['int1']  = '[0-9]{1}';   // только 1 цифра
      $pattern['int12'] = '[0-9]{1,2}'; // от 1 до 2 цифр
      $pattern['int2']  = '[0-9]{2}';   // только 2 цифры
      $pattern['int4']  = '[0-9]{4}';   // только 4 цифры
      $pattern['sex']   = '[M,F]{1}';   // пол М или Ж
      $pattern['rus']   = '[а-яА-Я]+';  // русские буквы строчные и прописные
      
    

//      preg_match($pattern_email, $email);

      return $exp;
   }
   
    public function nextStep() {
        global $msl;
        $_SESSION['step_num']++;
        $msl->updateArray('reg_applicant', array('step'=>$_SESSION['step_num']), array('id'=>$_SESSION['applicant_id']));
	return 1;	
    }

    public function createUser($uid, $request) {
        global $msl;
        $array = array('surname' => $this->_makeTitle($request[$uid.'surname']),
	               'name' => $this->_makeTitle($request[$uid.'name']),
		       'second_name' => $this->_makeTitle($request[$uid.'second_name']),
		       'e-mail' => $request[$uid.'e-mail'],
		       'ip' => sprintf('%u', ip2long($_SERVER['REMOTE_ADDR'])));
        return $msl->insertArray('reg_applicant', $array);
    }

    public function setSpecialty($uid, $request) {
        global $msl;
        $array = array('applicant_id' => $_SESSION['applicant_id'], 'catalog' => $request[$uid.'catalog'], 'profile' => $request[$uid.'profile'], 'internet' => $request[$uid.'internet'], 
                       'spo' => $request[$uid.'spo'], 'traditional_form' => $request[$uid.'traditional_form']);
	$id = $msl->insertArray('reg_request',$array);

        foreach($request[$uid.'ege'] as $val) {
            if ($val['scores'] > 0) {
                $msl->insertArray('reg_applicant_scores', array('request_id' => $id, 'subject' => $val['subject'], 'score' => $val['scores'], 'ege' => '1', 'document' => $val['document']));
            }
        }
        return $id;
    }
 
    public function updateFields($uid, $request) {
        global $msl;
      
        if ($request[$uid.'regaddressashome'] == 1) {
            $rval = $msl->getarray("SELECT name FROM reg_rf_subject WHERE id=".$request[$uid.'homeaddress-region']);
         
      	    $request[$uid.'regaddress'] = $request[$uid.'homeaddress-index'].", ".$rval['name'].", ".$request[$uid.'homeaddress-city'].", ";"";
	    if ($request[$uid.'homeaddress-street'] != '') $request[$uid.'regaddress'] .= $request[$uid.'homeaddress-street'].", ";
   	    $request[$uid.'regaddress'] .= "дом ".$request[$uid.'homeaddress-home'];
	    if ($request[$uid.'homeaddress-building'] != '') $request[$uid.'regaddress'] .= "/".$request[$uid.'homeaddress-building'];
   	    if ($request[$uid.'homeaddress-flat'] != '') $request[$uid.'regaddress'] .= ", ".$request[$uid.'homeaddress-flat'];
        } else {
	    $region = $msl->getarray("SELECT name FROM `admission`.`reg_regions` WHERE id='".$request[$uid.'regaddress-region']."' LIMIT 1;");
            $request[$uid.'regaddress'] = $request[$uid.'regaddress-index'].", ".$region['name'].", ".$request[$uid.'regaddress-city'].", ";
	    if ($request[$uid.'regaddress-street'] != '') $request[$uid.'regaddress'] .= $request[$uid.'regaddress-street'].", ";
   	    $request[$uid.'regaddress'] .= "дом ".$request[$uid.'regaddress-home'];
	    if ($request[$uid.'homeaddress-building'] != '') $request[$uid.'regaddress'] .= "/".$request[$uid.'regaddress-building'];
   	    if ($request[$uid.'regaddress-flat'] != '') $request[$uid.'regaddress'] .= ", ".$request[$uid.'regaddress-flat'];
        }


        $array = array('birthday' => $this->_makeDate($request[$uid.'birthday']), 'sex' => $request[$uid.'sex'], 'citizenry' => $request[$uid.'citizenry'], 'doc_type' => $request[$uid.'doc_type'], 'doc_serie' => $request[$uid.'doc_serie'], 'doc_number' => $request[$uid.'doc_number'], 'doc_issued' => $request[$uid.'doc_issued'], 'doc_date' => $this->_makeDate($request[$uid.'doc_date']), 'doc_code' => $request[$uid.'doc_code'], 'language' => $request[$uid.'language'], 'highedu' => $request[$uid.'highedu'], 'region' => $request[$uid.'region'], 'edu_base' => $request[$uid.'edu_base'], 'birthplace' => $request[$uid.'birthplace'], 'homeaddress-index' => $request[$uid.'homeaddress-index'], 'homeaddress-region' => $request[$uid.'homeaddress-region'], 'homeaddress-city' => $request[$uid.'homeaddress-city'], 'homeaddress-street' => $request[$uid.'homeaddress-street'], 'homeaddress-home' => $request[$uid.'homeaddress-home'], 'homeaddress-building' => $request[$uid.'homeaddress-building'], 'homeaddress-flat' => $request[$uid.'homeaddress-flat'], 'regaddress' => $request[$uid.'regaddress'], 'homephone_code' => $request[$uid.'homephone_code'], 'homephone' => $request[$uid.'homephone'], 'mobile_code' => $request[$uid.'mobile_code'], 'mobile' => $request[$uid.'mobile']);

        $msl->updateArray('reg_applicant', $array, array('id' => $_SESSION['applicant_id']));

        $_SESSION['edu_base'] = $request[$uid.'edu_base'];
        $_SESSION['region'] = $request[$uid.'region'];
        return 1;	 
    }
}


$ins = new Insertion();

switch($_SESSION['step_num']) {
   case 0:
      $rval = $msl->getarray("SELECT count(id) as cnt FROM reg_applicant WHERE `e-mail` = '".$_POST['e-mail']."'");      
      if ($rval['cnt'] > 0) {
         print "emailused";
	 break;
      }

      $_SESSION['applicant_id'] = $ins->createUser("", $_POST);
      if ($_SESSION['applicant_id'] > 0) {
         $ins->nextStep();
	 print "ok";
      }
      break;
   case 1:
      if ($ins->updateFields("", $_POST) > 0) {
         $ins->nextStep();

         $to = "Георгий Зубков <gzubkov@ins-iit.ru>, Владимир Смирнов <smirnov@ins-iit.ru>, Инякина Ирина <inyakina@ins-iit.ru>";
   	 $subject = "ЭПК - Новый абитуриент";

      	 $rval = $msl->getarray("SELECT surname,name,second_name,`e-mail`,homephone_code,homephone,mobile_code,mobile FROM reg_applicant WHERE id = ".$_SESSION['applicant_id']);
      	 $message = "
<html>
    <head><title>ЭПК - Новый абитуриент</title></head>
    <body>
        <p>Добрый день,</p>
	<p>в электронную приемную комиссию поступила информация и документы от нового абитуриента: ".$rval['surname']." ".$rval['name']." ".$rval['second_name']." (<A href=\"mailto:".$rval['e-mail']."\">".$rval['e-mail']."</A>, +7 (".$rval['homephone_code'].") ".$rval['homephone'].", +7 (".$rval['mobile_code'].") ".$rval['mobile'].")</p>
    </body>
</html>";

         $headers  = "Content-type: text/html; charset=utf-8 \r\n";
      	 $headers .= "From: Электронная приемная комиссия <iit@ins-iit.ru>\r\n";

      	 mail($to, $subject, $message, $headers); 

      	 print "ok";
      }
      break;
   case 3:
      
      $ins->nextStep();
      print "ok";
      break;
   case 2:
      if ($ins->setSpecialty("", $_POST) > 0) {
         if ($_POST['act'] != "add") {
            $ins->nextStep();
	 }
	 print "ok";
      } 
      break;
   default: 
      print "error";
}

unset($ins);
?>
