<?php
class Moodle
{
    private $_msl;

    public function __construct($msl)
    {
        $this->_msl = $msl;
    }

    public function createUser($name, $surname, $email, $password, $city, $idnumber = '')
    {
        $user = array("auth" => "manual",
                      "confirmed" => "1",
                      "mnethostid" => "1",
                      "username" => $email,
                      "password" => $password,
                      "firstname" => $name,
                      "lastname" => $surname,
                      "email" => $email,
                      "lang" => "ru_utf8",
                      "city" => $city,
                      "country" => "RU",
                      "idnumber" => $idnumber);

        return $this->_msl->insertArray("education`.`edu_user", $user, 0);
    }

    public function assignTest($id)
    {
        $userEnrolment = array("enrolid" => "59",
                               "status"  => "0",
                               "userid"  => $id,
                               "timestart" => time(),
                               "timeend" => "0",
                               "modifierid" => "309",
                               "timecreated" => time(),
                               "timemodified" => time());
        $result1 = $this->_msl->insertArray("education`.`edu_user_enrolments", $userEnrolment, 0);

        // INSERT INTO edu_role_assignments (roleid,contextid,userid,component,itemid,timemodified,modifierid,sortorder) VALUES('5','2380','368','','0','1440491249','309','0')
        $userAssignment = array("roleid" => "5",
							"contextid" => "2380",
							"userid" => $id,
							"component" => "",
							"itemid" => "0",
							"timemodified" => time(),
							"modifierid" => "309",
							"sortorder" => "0");
        $result2 = $this->_msl->insertArray("education`.`edu_role_assignments", $userAssignment, 0);
        // INSERT INTO edu_user_enrolments (enrolid,status,userid,timestart,timeend,modifierid,timecreated,timemodified) VALUES('59','0','367','1439931600','0','2','1439973265','1439973265')
        return ($result1 && $result2);
    }

    public function searchUser($email)
    {
        $array = $this->_msl->getarray("SELECT id FROM `education`.`edu_user` WHERE `email`='".$email."'");
        if ($array == 0) {
            return 0;
        }
        return $array['id'];
    }

    public function isAssigned($id)
    {
        $query = $this->_msl->getArray("SELECT true FROM education.`edu_user_enrolments` WHERE enrolid=59 AND userid='".$id."' LIMIT 1");
        if ($query == 0) {
            return false;
        }
        return true;
    }

    public function getGrades($id, $mid, $mid_old = -1)
    {
        $grade = $this->_msl->getarray("SELECT a.grade FROM education.`edu_quiz_grades` a WHERE a.`userid`='".$id."' AND a.`quiz`='".$mid."'");

        if ($grade == 0) {
            if ($mid_old > 0 && 
                $mid != $mid_old) {
                return $this->getGrades($id, $mid_old);
            } else {
                return -1;
            }
        }
        return round($grade['grade']);
    }
}
