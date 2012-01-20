<?php
class dMysql {
    private $_link;

    private function _query($query) {
        $result = mysql_query($query, $this->_link);
        if (!$result) {
            print "Возникла ошибка при работе с базой данных. О ней сообщено администратору.<br>"; 
	    print mysql_error();
	    print "<BR>".$query;
            exit();
        }
        return $result; 
    }

    private function _getimplode($sep, $array) {
        $arr = array();
        foreach($array as $k=>$v) $arr[]= "`".$k."`='".mysql_real_escape_string($v)."'";
        return implode($sep, $arr);
    }

    private function _real_escape_array($array){
    	return array_map("mysql_real_escape_string",$array);
    } 

    public function __construct() {
        global $CFG_dbhost;
        global $CFG_dbname;
        global $CFG_dbuser;
        global $CFG_dbpass;
        if (!$this->_link = mysql_connect($CFG_dbhost,$CFG_dbuser,$CFG_dbpass)) {
      	    die("Ошибка соединения с базой данных: ".mysql_error()." пожалуйста сообщите об этом администратору!");
   	}
   	mysql_select_db($CFG_dbname,$this->_link);
   	$this->_query('SET NAMES utf8');
	return true;
    }

    public function __destruct() {
        mysql_close($this->_link);
	return true;
    }

    public function getarray($query, $type=0) {
        $result = $this->_query($query);
   	switch (mysql_num_rows($result)) 
	{
        case 0:
            return 0;
      	    break;
        case 1:
            $row = mysql_fetch_assoc($result);
            if (!$type) {
	        return $row;
	    } else {
	        return array($row);
	    }
            break;
        default:
            $r = array();
            while ($row = mysql_fetch_assoc($result)) {
                $r[] = $row;
            }
            return $r;
        }    
    }

    public function getArrayByField($query, $field) {
        $result = $this->getarray($query, 1);
	$r = array();
        
	foreach($result as $key=>$val) {
            $r[$key] = $val[$field];
        }
        return $r;
    }

    public function getArrayById($query, $field, $field2) {
        $result = $this->getarray($query, 1);
        $r = array();

        foreach($result as $key=>$val) {
            $r[$val[$field]] = $val[$field2];
        }
        return $r;
    }


    public function deleteArray($table, $cond) {
        if ($this->_query("DELETE FROM `".$table."` WHERE ".$this->_getimplode(" AND ",$cond).";")) return true;
   	else return false;
    }

    public function insertArray($table, $array, $duplicate=1) {
        $arr1 = array_keys($array);
        $arr2 = $this->_real_escape_array(array_values($array));

        $query = "INSERT INTO `".$table."` (`".implode("`, `",$arr1)."`) VALUES ('".implode("', '",$arr2)."')";

	if ($duplicate == 1) {
	    $query .= " ON DUPLICATE KEY UPDATE ".$this->_getimplode(", ",$array);
	}

        if ($this->_query($query)) {
	    $id = mysql_insert_id($this->_link);
	    if ($id == 0) return true;
   	    return $id;
	} else {
	    return false;
        }
    }

    public function updateArray($table, $array, $cond) {
        if ($this->_query("UPDATE `".$table."` SET ".$this->_getimplode(", ",$array)." WHERE ".$this->_getimplode("AND ",$cond))) return true;
   	else return false;
    }
}
?>