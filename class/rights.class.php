<?php
class Rights 
{
    public function checkAdmin() {
        global $CFG_salted;
        return ($_SESSION['rights'] == "admin" && $_SESSION['md_rights'] == md5($CFG_salted.$_SESSION['rights']));
    }   

    public function checkRegion($id, $action) {
        if ($this->checkAdmin()) return true;
        return ($_SESSION['joomlaregion'] == $id);
    }

    public function checkApplicant($id, $action) {
        if ($this->checkAdmin()) return true;
        return ($_SESSION['applicant_id'] == $id);
    }

    public function printError() {
        print "Вам не хватает прав для использования данной функции.";
	return 0;
    }
}
?>