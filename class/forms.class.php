<?php
class FormFields 
{
    protected $gl_width = 200;
    protected $action;
    protected $id;
    protected $submit_name;
   
    // Создание новой формы с табличной разметкой, где $tdwidth - ширина колонок, $border - ширина границы, $action - принимающий скрипт   
    public function __construct($action, $fid, $tdwidth, $border, $submit_name="Отправить", $charset="UTF-8", $method="post") 
    {
        print "<FORM action=\"".$action."\" accept-charset=\"".$charset."\" method=\"".$method."\" id=\"".$fid."\" class=\"formular\">\n\n";
        $this->gl_width = $tdwidth;
	$this->action = $action;
	$this->id = $fid;
	$this->submit_name = $submit_name;
    }
   
    public function __destruct() 
    {
        print "<TR><TD align=\"left\" width=\"100%\" colspan=\"2\">\n";
	print "<INPUT type=\"submit\" class=\"submit\" value=\"".$this->submit_name."\">";
	print "</TD></TR></FORM>\n";
    }

    protected function _getRequired($r) 
    {
        switch ($r) {
	case 'A':
	    return 'all';
	case "EG":
	    return 'ege';
	case "SC":
	    return 'scores';
	case 'R':
	    return 'onlyRussians';
	case 'K':
	    return 'onlyRussiansDotDash';
	case 'N':
	    return 'onlyNumber';
	case 'E':
	    return 'email';
	case 'D':
	    return 'date';
	case 'C':
	    return 'onlyNumberDash';
	default:
	    return $r;
	}
    }
      
    protected function _getValidate($r) 
    {
        if ($r[0] == 'O') {
   	    return "validate[optional,custom[".$this->_getRequired(substr($r,1))."]] ";
	}
	 
	if (count($r) == 0 || $r === 0) return "";
        if ($r == 'A') return "validate[required] ";
      	return "validate[required,custom[".$this->_getRequired($r)."]] ";
    }

    protected function _required( $rs, $i=0 ) 
    {
        $requiredText = '<span class="form-required" title="Данное поле обязательно для заполнения.">*</span>';
        $simpleText = '';
        if ( is_array($rs) ) { return $rs[$i] ? $requiredText : $simpleText; }
        else { return $rs ? $requiredText : $simpleText; }
    }

    protected function _leftColumn($name, $rs = "")
    {
    	print "<TR><TD style=\"width: ".$this->gl_width."px;\">".$name;
        if ($rs && $rs[0] != 'O') {
            print "<SPAN class=\"form-required\" title=\"Данное поле обязательно для заполнения.\">*</SPAN>"; 
	}
	print "</TD>\n";
        return 0;
    }
 
    public function tdBox( $type, $name, $qname='', $size=10, $maxlength=10, $r="", $val="" ) 
    {
        switch($type) {
            case "remark":
                print "<TR><TD colspan=2 style=\"font-size: 7pt; line-height: 12px;\">".$name."</TD></TR>";
                break;
            case "phone":
	        $this->_leftColumn( $name, $r );
                print "<TD>(<INPUT maxlength=\"".$maxlength[0]."\" name=\"".$qname."_code\"  id=\"edit-".$qname."_code\" class=\"";
	        if ($r) print "validate[required,custom[onlyNumber]]";
                print "text-input\" style=\"width: ".$size[0]."px;\" type=\"text\" value=\"".$val[0]."\">) <INPUT maxlength=\"".$maxlength[1]."\" name=\"".$qname."\"  id=\"edit-".$qname."\" class=\"";
                if ($r) print "validate[required,custom[onlyNumber]]";
                print "text-input\" style=\"width: ".$size[1]."px;\" type=\"text\" value=\"".$val[1]."\">.</TD>\n";
                break;
            case "static":
	        $this->_leftColumn( $name, 0 );
                print "<TD>".$qname.".</TD>\n";
                break;
            default: 
          
         
         if (is_array( $name ) && is_array( $qname )){
            $this->_leftColumn( $name[0], $r[0] );
            print "<TD>";
            for ($i = 0; $i < count($qname); $i++){
		    if ($i > 0) {
		    print " ".$name[$i].$this->_required( $r, $i );
		    }
	            print " <INPUT maxlength=\"".$maxlength[$i]."\" name=\"".$qname[$i]."\" id=\"edit-".$qname[$i]."\" style=\"width: ".$size[$i]."px;\" class=\"";
		    print $this->_getValidate($r[$i]);
		    print "text-input\" type=\"text\" value='".$val[$i]."'>";
	    }
            print ".</TD>";
         } 
         if (!is_array( $name ) && !is_array( $qname )){
	    $this->_leftColumn( $name, $r );
            print "<TD><INPUT maxlength=\"".$maxlength."\" name=\"".$qname."\"  id=\"edit-".$qname."\" class=\"";
	    print $this->_getValidate($r);
            print "text-input\" style=\"width: ".$size."px;\" type=\"text\" value='".$val."'>.</TD>\n";
         }
	 print "</TR>\n";
        } 
        return true;
    }
     
    public function tdRadio( $name, $qname, $array, $sel=null, $r=0 ) 
    {
        $this->_leftColumn( $name, $r );
        print "<TD>";

        print "<div id=\"".$qname."radio\">\n";
	$k = 0;

        foreach ($array as $key=>$val) {
            if (( $key == $sel && $sel != null ) || ($sel == null && $k == 0 )) { $checked = " checked"; $k = 1; } else { $checked = ""; }
            print "<label id=\"".$qname."radio".$key."label\"><input type=\"radio\" id=\"".$qname."radio".$key."\" name=\"".$qname."\" value=\"".$key."\"".$checked.">\n";
	    print $val."</label>\n";
            
	    if ($key === "other" ) {
	       print "<script language=\"javascript\">";
	       print "var name;";
	       print " \$(document).ready(function() {";
	       print "\$('#".$qname."radio".$key."label').click(function(){
	       	            \$('#".$qname."radio').hide();
name = \$('#".$qname."radio".$key."').attr('name');
\$('#".$qname."radio".$key."').attr('name',name+'null');
\$('#".$qname."text').attr('name',name);
			    \$('#".$qname."div').show(); }); ";
	       print "\$('#".$qname."radio_return').click(function(){
\$('#".$qname."radio".$key."').attr('name',name);
\$('#".$qname."text').attr('name',name+'null');
	       	            \$('#".$qname."radio').show();
			    \$('#".$qname."div').hide(); }); ";
	       print "});</script>\n";

               $str = " <DIV style=\"display: none;\" id=\"".$qname."div\"><INPUT type=\"text\" name=\"".$qname."null\" id=\"".$qname."text\"><INPUT type=\"button\" value=\"X\" id=\"".$qname."radio_return\"></DIV>";
            } 
        }

        print "</DIV>".$str."</TD></TR>\n"; 
	return 0;
      }

    public function tdSelect( $name, $qname, $array, $sel=0, $r=0 ) 
    {
        $this->_leftColumn( $name, $r );
	$k = 0;
        print "<TD><SELECT name=\"".$qname."\" id=\"".$qname."\">";
        foreach ($array as $key => $val) {
            print "<OPTION value=\"".$key."\"";
	    if (($sel != null && $key == $sel) || ($sel == null && $k == 0)) {
	        print " selected";
		$k = 1;
	    }
	    print ">".$val."</OPTION>\n"; 
	}
        print "</SELECT></TD></TR>\n";
	return 0;
    }

    public function tdDateBox( $name, $qname, $startY, $endY, $r=0, $size=75, $set=0, $val=0 ) 
    {
        if ($size == 0) $size=75;
        print "<SCRIPT type=\"text/javascript\">
	       $(function(){ $('#".$qname."').datepicker({defaultDate: new Date(";
	if ($set == 0) {
	    print round(($startY+$endY)/2);
        } else {
	    print $set;
	}
	print ", 1 - 1, 1),
		yearRange: '".$startY.":".$endY."'});
		});
	      </SCRIPT>"; 
        $this->_leftColumn( $name, $r );
        print "<TD><INPUT id=\"".$qname."\" name=\"".$qname."\" type=\"text\" maxlength=\"10\" style=\"width: ".$size."px;\"";
	if ($val != 0) print " value=\"".$val."\"";
	print ">.</TD></TR>\n";
	return 0;	
    }
    
    public function hidden( $name, $value ) 
    {
	print "<INPUT type=\"hidden\" name=\"".$name."\" id=\"hidden".$name."\" value=\"".$value."\">\n";
	return 0;	
    }
}
?>