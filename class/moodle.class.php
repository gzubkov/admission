<?php
class Moodle extends dMysql
{
    public function createUser($name, $surname, $email, $password, $city) {
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
		      "country" => "RU");

        return $this->insertArray("education`.`edu_user", $user, 0);	
    }

    public function assignTest($id) {
        $user = array("roleid" => "5",
	      	      "userid" => $id,
		      "contextid" => "2380",
		      "timestart" => time());

        return $this->insertArray("education`.`edu_role_assignments", $user, 0);	
    }
}
?>
