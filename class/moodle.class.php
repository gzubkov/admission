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
        $user = array("roleid" => "5",
                      "userid" => $id,
                      "contextid" => "2380",
                      "timestart" => time());

        return $this->_msl->insertArray("education`.`edu_role_assignments", $user, 0);
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
        $query = $this->_msl->getArray("SELECT true FROM education.`edu_role_assignments` WHERE contextid=2380 AND roleid=5 AND userid='".$id."' LIMIT 1");
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
