<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html dir="ltr" xml:lang="en" xmlns="http://www.w3.org/1999/xhtml" lang="en"><head>
<meta charset="utf-8">
<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="//oss.maxcdn.com/jquery.bootstrapvalidator/0.5.1/css/bootstrapValidator.min.css">
    
<!-- Include FontAwesome CSS if you want to use feedback icons provided by FontAwesome -->
    <link rel="stylesheet" href="//cdn.jsdelivr.net/fontawesome/4.1.0/css/font-awesome.min.css" />

    <!-- BootstrapValidator CSS -->
 <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Lato:300,400,700">
    <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Merriweather:400,700">
<script type="text/javascript" language="javascript" src="//code.jquery.com/jquery-1.11.1.min.js"></script>
<script type="text/javascript" src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery.bootstrapvalidator/0.5.0/js/bootstrapValidator.min.js"></script>

<script type="text/javascript" language="javascript" src="../js/jquery.mask.min.js"></script>

<style>
/*.form-control:focus { // при активном элементе выделить цветом!
    border-color: #fddbab;
    outline: 0; 
    -webkit-box-shadow: inset 0;
          box-shadow: inset 0;
    background-color: #fddbab;
}*/ 
.has-error .help-block {
    position:absolute;
    bottom: -25px;
    left: 120px;
    z-index: 20;    
    border: 1px #EEA236 solid;
    background-color: #f6cd94;
    line-height: 2em;
    padding: 1px 12px;
    border-radius: 4px 4px 4px 10px;
    -webkit-box-shadow: 7px 7px 5px 0px rgba(50, 50, 50, 0.75);
    -moz-box-shadow:    7px 7px 5px 0px rgba(50, 50, 50, 0.75);
    box-shadow:         7px 7px 5px 0px rgba(50, 50, 50, 0.75);
}
.difposhelpblock .has-feedback .form-control-feedback {
    top: 0px;
    right: 195px;
    display: none !important;
}
.difposhelpblock .has-error .help-block {
    position: absolute;
    left: 190px;
}
</style>

<script language="javascript">

$(function() {
        $('#registrationForm').bootstrapValidator({
        // To use feedback icons, ensure that you use Bootstrap v3.1.0 or later
        excluded: [':disabled', ':hidden', ':not(:visible)'],
        //container: 'tooltip',
        feedbackIcons: {
            valid: 'glyphicon glyphicon-ok',
            invalid: 'glyphicon glyphicon-remove',
            validating: 'glyphicon glyphicon-refresh'
        }
    });
})

.on('error.form.bv', function(e) {
            // $(e.target) --> The form instance
            // $(e.target).data('bootstrapValidator')
            //             --> The BootstrapValidator instance

            // Do something ...
            alert('qq');
        })

function hideAlt(elem2) {
    var elem = elem2.parent();
    var radio = elem.parent().parent().find('div'); 
    var input = elem.children().first();
    input.val('').prop('name', input.prop('name') +'_alt');
    radio.children().children().children().first().prop('checked', true);
    radio.show(); 
    elem.parent().hide();
}

function showAlt(elem2) {
    var elem = elem2.parent();
    var input = elem.parent().children().last().find('input');
    elem.hide();
    elem.siblings().last().show();
    input.prop('name', input.prop('name').replace('_alt', ''));
}
</script>
</head>
<body>
<br><br><br>
<form id="registrationForm" class="form-horizontal bv-form" method="post" novalidate="novalidate">

<?php
class FormFields
{
    function _getValidate($r) 
    {
        if (count($r) == 0 || 
            $r === 0) {
            return "";
        }

        $text = '';

        if ($r[0] == 'O') {
            $r = substr($r, 1);
        } else {
            $text .= " data-bv-notempty=\"true\" data-bv-notempty-message=\"Поле обязательно для заполнения\"";
        }

        switch ($r) {
            case 'A': // поле обязательно для заполнения, допускаются любые символы
                return $text;
            
            case 'D': // дата
                return $text." data-mask=\"00.00.0000\" data-bv-date=\"true\" data-bv-date-format=\"DD.MM.YYYY\" data-bv-date-message=\"Дата должна быть указана в формате ДД.ММ.ГГГГ\"";
        }

        $regexpArray = array('R' => array('^[а-яА-Я\-\ ]+$', 'Поле может содержать только русские буквы'),
                             'К' => array('^[а-яА-Я\ \.\-]+$$', 'Поле может содержать только русские буквы, тире и точку'),
                             'D' => array('^[0-9]{2}\.\[0-9]{2}\.\[0-9]{4}$', 'Дата должна быть указана в формате ДД.ММ.ГГГГ'),
                             'EG' => array('^[0-9]{2}\-\[0-9]{9}\-\[0-9]{2}$', 'Указан неверный формат документа ЕГЭ'),
                             'SC' => array('^[0-9]{1|2}|100$', 'Баллы должны быть указаны в диапазоне от 1 до 100'),
                             'N' => array('^[0-9]+$', 'Поле может содержать только цифры'),
                             'C' => array('^[0-9\-]+$', 'Поле может содержать только цифры и -'),
                             'KP' => array('^[0-9]{3}\-\[0-9]{3}$', 'Код подразделения указан в неправильном формате')
                            );

        return $text." data-bv-regexp=\"true\"
                data-bv-regexp-regexp=\"".$regexpArray[$r][0]."\"
                data-bv-regexp-message=\"".$regexpArray[$r][1]."\"";
    }

    function leftColumn($name)
    {
        echo "<div class=\"form-group has-feedback\"><label class=\"col-lg-3 control-label\">".$name."</label><div class=\"col-lg-5\">";
    }

    function rightColumn()
    {
        echo "</div></div>";
    }

    function tdBox($type, $colname, $name = '', $size = 10, $maxlength = 10, $r = 0, $placeholder = '', $val = '')
    {
        $this->leftColumn($colname);

        switch ($type) {
            case 'date':
            case 'number':
            case 'text':
            default:
                echo "<input class=\"form-control\" name=\"".$name."\" type=\"".$type."\" placeholder=\"".$placeholder."\" value=\"".$val."\"";
                echo $this->_getValidate($r).">";            
        }

        $this->rightColumn();
    }
}

$form = new FormFields();

$form->tdBox('text', 'Имя', 'name', 20, 20, 'R');
$form->tdBox('date', 'Дата', 'date', 20, 20, 'D');

 ?>
   
                                        <div class="form-group has-feedback">
                                            <label class="col-lg-3 control-label">Username</label>
                                            <div class="col-lg-5">
                                                <input data-bv-field="username" class="form-control" name="username2" type="text" placeholder="First name"
                data-bv-notempty="true"
                
                data-bv-regexp="true"
                data-bv-regexp-regexp="^[a-zA-Z0-9_\.]+$"
                data-bv-regexp-message="The username can only consist of alphabetical, number, dot and underscore">
                                            </div>
                                        </div>

                                        <div class="form-group has-feedback">
                                            <label class="col-lg-3 control-label">Email address</label>
                                            <div class="col-lg-5">
                                                <input name="mail" type="text" class="form-control" 
                data-bv-emailaddress="true"
                data-bv-emailaddress-message="The input is not a valid email address"></div>
                                        </div>

                                        <div class="form-group has-feedback">
                                            <label class="col-lg-3 control-label">Password</label>
                                            <div class="col-lg-5">
                                                <input data-bv-field="password" class="form-control" name="password" type="password"></div>
                                        </div>

                                        <div class="form-group has-feedback">
                                            <label class="col-lg-3 control-label">Gender</label>
                                            <div class="col-lg-5">
                                                <div class="radio">
                                                    <label>
                                                        <input data-bv-field="gender" name="gender" value="male" type="radio"> Male
                                                    </label>
                                                </div>
                                                <div class="radio">
                                                    <label>
                                                        <input data-bv-field="gender" name="gender" value="female" type="radio"> Female
                                                    </label>
                                                </div>
                                                <div class="radio">
                                                    <label>
                                                        <input data-bv-field="gender" name="gender" value="other" type="radio"> Other
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group has-feedback">
                                            <label class="col-lg-3 control-label">Date of birth</label>
                                            <div class="col-lg-5">
                                                <input data-bv-field="birthday" class="form-control" name="birthday" placeholder="YYYY/MM/DD" type="text">
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <div class="col-lg-9 col-lg-offset-3">
                                                <!-- Do NOT use name="submit" or id="submit" for the Submit button -->
                                                <button type="submit" class="btn btn-default">Sign up</button>
                                            </div>
                                        </div>
                                    
</form>

<?php
print_r($_REQUEST);
?>
<!--<div class="container"> -->
<!--  <form role="form" action="" method="POST" id="registrationForm" class="form-horizontal bv-form">
-->

<?php

$array = array('Российская федерация' => 'Российская федерация', 'other' => 'другое');
$name = 'citizenry';
//$sel = 'Российская федерация';

function printRadio($text, $array, $name, $sel = null)
{
    $k = 0;

    if ($sel == null) {
        $sel = reset($array);
        $k = 2;
    } else {
        if (in_array($sel, $array) === false) {
            $k = 1;
        } else {
            $k = 2;
        }
    }

    echo "<div><div class=\"form-group\"";
    if ($k == 1) {
        echo " style=\"display: none;\"";
    }
    echo ">";

    foreach ($array as $key => $value) {
        if (strcmp($key, 'other') !== 0) {
            echo "<div class=\"radio\"><label class=\"radio-inline control-label\">
                  <input type=\"radio\" name=\"".$name."\" value=\"".$key."\"";
            if ($k == 2 && 
                $key == $sel) {
                echo " checked=\"\"";
            }
            echo ">\n".$value."</label></div>\n";
        }
    }

    if (strcmp($key, 'other') === 0) {
        echo "<div class=\"radio\" onclick=\"showAlt($(this)); return false;\">
              <label class=\"radio-inline control-label\"><input type=\"radio\" id=\"other\" name=\"".$name."\" value=\"other\">\n".$value."</label></div>
              </div>";
        
        if ($k == 1) {
            echo "<div><div class=\"input-group\"><input type=\"text\" class=\"form-control\" name=\"".$name."\" value=\"".$sel."\"";
        } else {
            echo "<div style=\"display: none;\"><div class=\"input-group\"><input type=\"text\" class=\"form-control\" name=\"".$name."_alt\" value=\"\"";
        }
        echo "><span class=\"input-group-addon\" onclick=\"hideAlt($(this)); return false;\">x</span></div></div>";
    } else {
        echo "</div>";
    }
    echo "</div>";

}

//printRadio('', $array, $name);
?>

<br>



 
</div>

</body>
</html>