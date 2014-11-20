<?php
class dMysql extends mysqli
{
    private function _error($query)
    {
        echo "Возникла ошибка при работе с базой данных. О ней сообщено администратору.<br>Приносим извинения за доставленные неудобства.<br><br>";
        file_put_contents ( '/www/admission.iitedu.ru/class/qqq.txt' , date('d.m.Y H:i').": ".$this->error."\n".$query."\n", FILE_APPEND | LOCK_EX);
        return false;
        //exit();
    }

    private function _query($query)
    {
        $result = $this->query($query);
        if ($result === false) {
            $this->_error($query);
        }
        return $result;
    }

    private function _getimplode($sep, $array)
    {
        $arr = array();
        foreach ($array as $k => $v) {
            $arr[]= "`".$k."`='".$this->escape_string($v)."'";
        }
        return implode($sep, $arr);
    }

    public function __construct($host = '', $user = '', $pass = '', $dbname = '' )
    {
        global $CFG_dbhost;
        global $CFG_dbname;
        global $CFG_dbuser;
        global $CFG_dbpass;
        
	    if (isset($CFG_dbhost) === true) {
            $host = $CFG_dbhost;
	        $user = $CFG_dbuser;
	        $pass = $CFG_dbpass;
            $dbname = $CFG_dbname;
	    }

        parent::__construct($host, $user, $pass, $dbname);

        if ($this->connect_error) {
            $this->_error("connect: ".$this->connect_errno .' ('.$this->connect_error.')');
        }

        if ($this->set_charset("utf8") === false) {
            $this->_error("Ошибка при загрузке набора символов utf8: %s\n".$this->error);
        }

        return true;
    }

    public function __destruct()
    {
        $this->close();
        return true;
    }

    public function getarray($query, $type = 0)
    {
        $result = $this->_query($query);

        if ($result === false) {
            return false;
        }

        switch ($result->num_rows) {
        case 0: 
            return 0;
            break;
        case 1:
            $row = $result->fetch_assoc();
            if ($type == 0) {
                return $row;
            } else {
                return array($row);
            }
            break;
        default:
            $r = array();
            while ($row = $result->fetch_assoc()) {
                $r[] = $row;
            }
            return $r;
        }
    }

    public function getArrayById($query, $field, $field2)
    {
        $result = $this->getarray($query, 1);
        $r = array();

        foreach ($result as $key => $val) {
            $r[$val[$field]] = $val[$field2];
        }
        return $r;
    }


    public function deleteArray($table, $cond)
    {
        if ($this->_query("DELETE FROM `".$table."` WHERE ".$this->_getimplode(" AND ", $cond).";")) {
            return true;
        }
        return false;
    }

    public function insertArray($table, $array, $duplicate = 1)
    {
        $arr1 = array_keys($array);
        $arr2 = array_map(array(&$this,'escape_string'), array_values($array));
        
        $query = "INSERT INTO `".$table."` (`".implode("`, `", $arr1)."`) VALUES ('".implode("', '", $arr2)."')";

        if ($duplicate == 1) {
            $query .= " ON DUPLICATE KEY UPDATE ".$this->_getimplode(", ", $array);
        }

        if ($this->_query($query)) {
            $id = $this->insert_id;
            if ($id == 0) {
                return true;
            }
            return $id;
        } 
        return false;
    }

    public function updateArray($table, $array, $cond)
    {
        if ($this->_query("UPDATE `".$table."` SET ".$this->_getimplode(", ", $array)." WHERE ".$this->_getimplode("AND ", $cond))) {
            return true;
        } 
        return false;
    }
}
