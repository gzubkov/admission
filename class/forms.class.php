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
        echo "<form action=\"".$action."\" accept-charset=\"".$charset."\" method=\"".$method."\" id=\"".$fid."\" class=\"formular\">\n\n";
        $this->gl_width = $tdwidth;
        $this->action = $action;
        $this->id = $fid;
        $this->submit_name = $submit_name;
    }
   
    public function __destruct() 
    {
        echo "<tr><td align=\"left\" width=\"100%\" colspan=\"2\">\n";
        echo "<input type=\"submit\" class=\"submit\" value=\"".$this->submit_name."\" /></td></tr></form>\n";
        return true;
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
     
        if (count($r) == 0 || 
            $r === 0) {
            return "";
        }

        if ($r == 'A') {
            return "validate[required] ";
        }
        return "validate[required,custom[".$this->_getRequired($r)."]] ";
    }

    protected function _required($rs, $i = 0) 
    {
        $requiredText = '<span class="form-required" title="Данное поле обязательно для заполнения.">*</span>';
        $simpleText = '';
        if (is_array($rs) === true) {
            return $rs[$i] ? $requiredText : $simpleText; 
        } else { 
            return $rs ? $requiredText : $simpleText; 
        }
    }

    protected function _leftColumn($name, $rs = '')
    {
        print "<tr><td style=\"width: ".$this->gl_width."px;\">".$name;
        if ($rs && $rs[0] != 'O') {
            print "<span class=\"form-required\" title=\"Данное поле обязательно для заполнения.\">*</span>"; 
        }
        print "</td>\n";
        return true;
    }
 
    public function tdBox($type, $name, $qname='', $size=10, $maxlength=10, $r="", $val="" ) 
    {
        switch($type) {
            case "remark":
                print "<tr><td colspan=2 style=\"font-size: 7pt; line-height: 12px;\">".$name."</td></tr>";
                break;
            case "phone":
                $this->_leftColumn( $name, $r );
                print "<td>(<input maxlength=\"".$maxlength[0]."\" name=\"".$qname."_code\"  id=\"edit-".$qname."_code\" class=\"";
                if ($r) {
                    print "validate[required,custom[onlyNumber]]";
                }
                print "text-input\" style=\"width: ".$size[0]."px;\" type=\"text\" value=\"".(isset($val[0])?$val[0]:"")."\">) <input maxlength=\"".$maxlength[1]."\" name=\"".$qname."\"  id=\"edit-".$qname."\" class=\"";
                if ($r) {
                    print "validate[required,custom[onlyNumber]]";
                }
                print "text-input\" style=\"width: ".$size[1]."px;\" type=\"text\" value=\"".(isset($val[1])?$val[1]:"")."\">.</td>\n";
                break;
            case "static":
                $this->_leftColumn( $name, 0 );
                print "<td>".$qname.".</td>\n";
                break;
            default:          
                if (is_array($name) === true && 
                    is_array($qname) === true) {
                    $this->_leftColumn($name[0], $r[0]);
                    echo "<td>";
                    for ($i = 0; $i < count($qname); $i++) {
                        if ($i > 0) {
                            echo " ".$name[$i].$this->_required($r, $i);
                        }
                        echo " <input maxlength=\"".$maxlength[$i]."\" name=\"".$qname[$i]."\" id=\"edit-".$qname[$i]."\" style=\"width: ".$size[$i]."px;\" class=\"";
                        echo $this->_getValidate($r[$i]);
                        echo "text-input\" type=\"text\"";
                        if (isset($val[$i]) === true) {
                            echo " value='".$val[$i]."'";
                        }
                        echo " />";
                    }
                    echo ".</td>";
                } else {
                    $this->_leftColumn( $name, $r );
                    echo "<td><input maxlength=\"".$maxlength."\" name=\"".$qname."\"  id=\"edit-".$qname."\" class=\"";
                    echo $this->_getValidate($r);
                    echo "text-input\" style=\"width: ".$size."px;\" type=\"text\" value='".$val."' />.</td>\n";
                }
                echo "</tr>\n";
        } 
        return true;
    }
     
    public function tdRadio($name, $qname, $array, $sel = null, $r = 0) 
    {
        $this->_leftColumn($name, $r);
        print "<td>";

        print "<div id=\"".$qname."radio\">\n";
        $k = 0;

        foreach ($array as $key => $val) {
            if (($key == $sel && $sel != null ) || 
                ($sel == null && $k == 0 )) { 
                $checked = " checked"; 
                $k = 1; 
            } else { 
                $checked = ""; 
            }

            echo "<label id=\"".$qname."radio".$key."label\"><input type=\"radio\" id=\"".$qname."radio".$key."\" name=\"".$qname."\" value=\"".$key."\"".$checked.">\n";
            echo $val."</label>\n";
            
            if (strcmp($key, "other") === 0) {
                echo "<script language=\"javascript\">var name;";
                echo " \$(document).ready(function() {";
                echo "\$('#".$qname."radio".$key."label').click(function(){";

                if (strcmp($qname, 'citizenry') === 0) {
                    echo "setPassport();";
                }

                echo "\$('#".$qname."radio').hide();
                        name = \$('#".$qname."radio".$key."').attr('name');
                        \$('#".$qname."radio".$key."').attr('name',name+'null');
                        \$('#".$qname."text').attr('name',name);
                        \$('#".$qname."div').show(); }); ";
                echo "\$('#".$qname."radio_return').click(function(){";
            
                if (strcmp($qname, 'citizenry') === 0) {
                    echo "setPassport();";
                }

                echo "\$('#".$qname."radio".$key."').attr('name',name);
                      \$('#".$qname."radio".$key."').attr('checked','true');
                      \$('#".$qname."text').attr('name',name+'null');
                      \$('#".$qname."radio').show();
                      \$('#".$qname."div').hide(); }); ";
                echo "});</script>\n";

                $str = " <div style=\"display: none;\" id=\"".$qname."div\"><input type=\"text\" name=\"".$qname."null\" id=\"".$qname."text\"><input type=\"button\" style=\"font-size: 10px; width: 12px; height: 18px;\" value=\"X\" id=\"".$qname."radio_return\"></DIV>";
            }
        }

        echo "</div>";
        if (isset($str) === true) {
            echo $str;
        }
        echo "</td></tr>\n"; 
        return true;
    }

    public function tdSelect($name, $qname, $array, $sel = 0, $r = 0) 
    {
        $this->_leftColumn($name, $r);
        $k = 0;
        
        echo "<td><select name=\"".$qname."\" id=\"".$qname."\">";
        foreach ($array as $key => $val) {
            print "<option value=\"".$key."\"";
            if (($sel != null && $key == $sel) || 
                ($sel == null && $k == 0)) {
                echo " selected";
                $k = 1;
            }
            echo ">".$val."</option>\n"; 
        }
        echo "</select></td></tr>\n";
        return true;
    }

    public function tdDateBox($name, $qname, $startY, $endY, $r = 0, $size = 75, $set = 0, $val = 0) 
    {
        if ($size == 0) {
            $size = 75;
        }
        echo "<script type=\"text/javascript\">
                $(function(){ $('#".$qname."').datepicker({defaultDate: new Date(";
        if ($set == 0) {
            echo $set = round(($startY+$endY)/2);
        }

        echo $set.", 1 - 1, 1),
        yearRange: '".$startY.":".$endY."', maxDate: -1});
        });</script>"; 
        $this->_leftColumn( $name, $r );
        
        echo "<td><input id=\"".$qname."\" name=\"".$qname."\" type=\"text\" maxlength=\"10\" style=\"width: ".$size."px;\"";
        if ($val != 0) {
            echo " value=\"".$val."\"";
        }
        echo " />.</td></tr>\n";
        return true;   
    }
    
    public function hidden($name, $value) 
    {
        echo "<input type=\"hidden\" name=\"".$name."\" id=\"hidden".$name."\" value=\"".$value."\" />\n";
        return true;   
    }
}
