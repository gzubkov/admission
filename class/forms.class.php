<?php
class FormFields 
{
    protected $gl_width = 200;
    protected $action;
    protected $id;
    public $submitName;

    protected $blockFlag = 0; // флаг, отвечающий за окончание блока
   
    // Создание новой формы с табличной разметкой, где $tdwidth - ширина колонок, $border - ширина границы, $action - принимающий скрипт   
    public function __construct($action, $fid, $tdwidth, $border, $submitName="Отправить", $charset="UTF-8", $method="post")
    {
        echo "<form action=\"".$action."\" accept-charset=\"".$charset."\" method=\"".$method."\" id=\"".$fid."\" class=\"formular\">\n\n";
        $this->gl_width = $tdwidth;
        $this->action = $action;
        $this->id = $fid;
        $this->submitName = $submitName;
    }
   
    public function __destruct() 
    {
        $this->endBlock();
        echo "<tr><td align=\"left\" width=\"100%\" colspan=\"2\">\n";
        echo "<input type=\"submit\" class=\"submit\" value=\"".$this->submitName."\" /></td></tr></form>\n";
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
        if (count($r) == 0 || 
            $r === 0) {
            echo "";
        } else if ($r[0] == 'O') {
            echo "validate[optional,custom[".$this->_getRequired(substr($r,1))."]] ";
        } else if ($r == 'A') {
            echo "validate[required] ";
        } else {
            echo "validate[required,custom[".$this->_getRequired($r)."]] ";
        }
        return true;
    }

    protected function _required($rs, $i = 0) 
    {
        $requiredText = '<span class="form-required" title="Поле является обязательным для заполнения.">*</span>';

        if (is_array($rs) === true) {
            $rs = $rs[$i];
        }

        if ($rs == true &&
            $rs[0] != 'O') {
            echo $requiredText;
        }
        return $this;
    }

    protected function _beginRow($name, $rs = '')
    {
        if (is_array($name) === true) {
            $name = $name[0];
        }

        echo "<tr><td style=\"width: ".$this->gl_width."px;\">".$name;
        $this->_required($rs);
        echo "</td>\n<td>";
        return $this;
    }

    protected function _endRow($dotted = false)
    {
        if ($dotted === true) {
            echo ".";
        }
        echo "</td></tr>\n";
        return $this;
    }

    public function beginBlock($name = '')
    {
        $this->endBlock();

        if ($name !== '') {
            echo "<h3>".$name."</h3>";
        }
        echo "<div><table style=\"display: block;\"><tbody style=\"border: none;\">";

        $this->blockFlag = 1;
        return $this;
    }
 
    public function endBlock()
    {
        if ($this->blockFlag === 1) {
            echo "</tbody></table></div>";
        }
        $this->blockFlag = 0;
        return $this;
    }

    public function beginHiddenDiv($id)
    {
        $this->endBlock();

        echo "<div id=\"".$id."\" style=\"display:none;\">";
        return $this;
    }

    public function endHiddenDiv()
    {
        $this->endBlock();

        echo "</div>";
        return $this;
    }

    public function remark($text)
    {
        echo "<tr><td colspan=2 style=\"font-size: 7pt; line-height: 12px;\">".$text."</td></tr>";
        return $this;
    }

    public function common($text, $align=null, $remark=0)
    {
        echo "<tr><td colspan=2";
        if (is_null($align) !== false) {
            echo " align=\"".$align."\"";
        }
        if ($remark == 1) {
            echo " style=\"font-size: 7pt; line-height: 12px;\"";
        }
        echo ">".$text."</td></tr>";
        return $this;
    }

    public function text($name, $text)
    {
        $this->_beginRow($name);
        echo $text;
        $this->_endRow(true);
        return $this;
    }

    public function textInput($name, $qname='', $size=10, $maxlength=10, $r="", $val="")
    {
        $this->_beginRow($name, $r);

        if (is_array($name) === true &&
            is_array($qname) === true) {

            for ($i = 0; $i < count($qname); $i++) {
                if ($i > 0) {
                    echo " ".$name[$i];
                    $this->_required($r, $i);
                }
                echo " <input maxlength=\"".$maxlength[$i]."\" name=\"".$qname[$i]."\" id=\"edit-".$qname[$i]."\" style=\"width: ".$size[$i]."px;\" class=\"";
                $this->_getValidate($r[$i]);
                echo "text-input\" type=\"text\"";

                if (isset($val[$i]) === true) {
                    echo " value='".$val[$i]."'";
                }
                echo " />";
            }
        } else {
            echo "<input maxlength=\"".$maxlength."\" name=\"".$qname."\"  id=\"edit-".$qname."\" class=\"";
            $this->_getValidate($r);
            echo "text-input\" style=\"width: ".$size."px;\" type=\"text\" value='".$val."' />";
        }

        $this->_endRow(true);
        return $this;
    }

    public function phoneInput($name, $qname='', $size=10, $maxlength=10, $r="", $val="")
    {
        $this->_beginRow( $name, $r );
        echo "(";
        $qname = array($qname."_code", $qname);

        for ($i = 0; $i <= 1; $i++) {
            echo "<input maxlength=\"".$maxlength[$i]."\" name=\"".$qname[$i]."\"  id=\"edit-".$qname[$i]."\" class=\"";
            if ($r) {
                echo "validate[required,custom[onlyNumber]]";
            }
            echo " text-input\" style=\"width: ".$size[$i]."px;\" type=\"text\" value=\"".(isset($val[$i])?$val[$i]:"")."\">";

            if ($i == 0) {
                echo ") ";
            }
        }
        $this->_endRow(true);
        return $this;
    }

    public function radioInput($name, $qname, $array, $sel = null, $r = 0)
    {
        $this->_beginRow($name, $r);

        echo "<div id=\"".$qname."radio\">\n";
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
        $this->_endRow(false);

        return $this;
    }

    public function selectInput($name, $qname, $array, $sel = 0, $r = 0)
    {
        $this->_beginRow($name, $r);
        $k = 0;
        
        echo "<select name=\"".$qname."\" id=\"".$qname."\">";
        foreach ($array as $key => $val) {
            echo "<option value=\"".$key."\"";
            if (($sel != null && $key == $sel) || 
                ($sel == null && $k == 0)) {
                echo " selected";
                $k = 1;
            }
            echo ">".$val."</option>\n"; 
        }
        echo "</select>";

        $this->_endRow();
        return $this;
    }

    public function dateInput($name, $qname, $startY, $endY, $r = 0, $size = 75, $set = 0, $val = 0)
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
        
        $this->_beginRow( $name, $r );

        echo "<input id=\"".$qname."\" name=\"".$qname."\" type=\"text\" maxlength=\"10\" style=\"width: ".$size."px;\"";
        if ($val != 0) {
            echo " value=\"".$val."\"";
        }
        echo " />";
        $this->_endRow(true);
        return $this;
    }

    public function hidden($name, $value) 
    {
        echo "<input type=\"hidden\" name=\"".$name."\" id=\"hidden".$name."\" value=\"".$value."\" />\n";
        return $this;
    }
}
