<?php
require_once('../../conf.php');
require_once('../class/mysql.class.php');

$arr = array();
$msl = new dMysql();

if (is_array($_REQUEST['sel'])) {
   foreach ($_REQUEST['sel'] as $key => $val) {
      $cval = $msl->getArrayByField("SELECT `id` from reg_test_answers WHERE `question_id`='".$key."' AND `right`=1 ORDER by id ASC","id");

      if (!is_array($val) && count($cval) == 1) {
         $score = (int) in_array($val, $cval);
      } else {
         $score = 0;
         foreach($val as $vl) $score += (int) in_array($vl, $cval);
      }
      $arr[] = array('id'=>$key,'count'=>count($cval),'score'=>$score,'right'=>$cval);
   }
   echo json_encode($arr);
} else echo "notanyoneselected";
?>
