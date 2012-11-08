<?php
class Moodle
{
    var $_msl;

    public function __construct($msl) 
    {
        $this->_msl = $msl;   
    }

    public function createUser($name, $surname, $email, $password, $city, $idnumber='') 
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
	if ($array == 0) return 0;
	return $array['id'];
    }

    public function getGrades($id) 
    {
        return $this->_msl->getarray("SELECT b.id,a.grade,b.min,c.surname,c.name,c.second_name FROM education.`edu_quiz_grades` a 
	      		      LEFT JOIN admission.`reg_subjects` b ON a.quiz=b.mid 
			      LEFT JOIN admission.`reg_teachers` c ON b.`teacher_id`=c.id WHERE a.`userid`='".$id."' ORDER BY b.id;",1);
    }
}
?>
