<?php
class dMssql {
    private $_link;

    private function _error($query) {
        print "Возникла ошибка при работе с базой данных. О ней сообщено администратору.<br>"; 
	//print mssql_error();
	print "<BR>".$query;
        exit();
    }

    private function _query($query) {
        $result = mssql_query($query, $this->_link);
        if (!$result) {
            $this->_error($query);
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
        $this->_link = mssql_connect ("perebros", "perebros", "126wrkg");
	if (!$this->_link) {
    	    die('Something went wrong while connecting to MSSQL');
	}
	mssql_select_db('perebros_sql', $this->_link);
	return true;
    }

    public function __destruct() {
        mssql_close($this->_link);
	return true;
    }

    public function getarray($query, $type=0) {
        $result = $this->_query($query);
   	switch (mssql_num_rows($result)) 
	{
        case 0:
            return 0;
      	    break;
        case 1:
            $row = mssql_fetch_assoc($result);
            if (!$type) {
	        return $row;
	    } else {
	        return array($row);
	    }
            break;
        default:
            $r = array();
            while ($row = mssql_fetch_assoc($result)) {
                $r[] = $row;
            }
            return $r;
        }    
    }

}
?>