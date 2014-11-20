<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html class="js" dir="ltr" xml:lang="en" xmlns="http://www.w3.org/1999/xhtml" lang="en"><head>

<!--<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>-->
 
  
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Личный кабинет регионального партнера</title>

<link type="text/css" rel="stylesheet" media="all" href="../images/defaults.css">
<link type="text/css" rel="stylesheet" media="all" href="../images/system.css">
<link type="text/css" rel="stylesheet" media="all" href="../images/style.css">
<!--<link type="text/css" rel="stylesheet" media="all" href="../css/custom-theme/jquery-ui-1.8.custom.css">-->	
<link type="text/css" rel="stylesheet" media="all" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css">
<link type="text/css" rel="stylesheet" media="all" href="http://datatables.net/release-datatables/media/css/demo_table_jui.css">

<!-- Validation -->
<link rel="stylesheet" href="../css/validationEngine.jquery.css" type="text/css" media="screen" title="no title" charset="utf-8" />
<SCRIPT type="text/javascript" src="http://www.position-relative.net/creation/formValidator/js/jquery-1.6.min.js"></script>
<!--<SCRIPT type="text/javascript" src="http://code.jquery.com/jquery-1.10.2.min.js"></script>-->
<!-- jQuery UI -->
<SCRIPT type="text/javascript" src="//code.jquery.com/ui/1.10.4/jquery-ui.js"></script>

<SCRIPT type="text/javascript" src="../js/jquery.ui.datepicker-ru.js"></script>
<SCRIPT type="text/javascript" src="../js/jquery.form.js"></SCRIPT>
<SCRIPT type="text/javascript" src="http://malsup.com/jquery/block/jquery.blockUI.1.33.js"></SCRIPT>
<SCRIPT type="text/javascript" src="http://cdn.datatables.net/1.10.0/js/jquery.dataTables.js"></SCRIPT>

<!-- Script validation -->
<script type="text/javascript" src="http://www.position-relative.net/creation/formValidator/js/jquery.validationEngine.js"></script>
<script type="text/javascript" src="../js/jquery.validationEngine-ru.js"></script>

<!--<script type="text/javascript" src="http://www.position-relative.net/creation/formValidator/js/languages/jquery.validationEngine-en.js"></script>-->
<script type="text/javascript" src="../js/jquery.alerts.js"></script>

<script>
		jQuery(document).ready(function(){
			// binds form submission and fields to the validation engine
			jQuery("#formID").validationEngine('attach');
		});
	</script>
</head>
<body>
<form id="formID" class="formular" method="post" action="">
		<fieldset>
			<legend>
				Phone
			</legend>
			<label>
				+103-304-340-4300-043
				<br/>
				+1 305 768 23 34 ext 23
				<br/>
				+1 (305) 768-2334 extension 703
				<br/>
				+1 (305) 768-2334 x703
				<br/>
				04312 / 777 777
				<br/>
				01-47.34/32 56
				<br/>
				(01865)  123456
				<br/>
				<span>Phone : (optional)</span>
				<input value="+1 305 768 23 34 ext 23 BUG" class="validate[custom[phone]] text-input" type="text" name="telephone" id="telephone" />
			</label>
		</fieldset>
		<fieldset>
			<legend>
				URL
			</legend>
			<label>
				URL begin with http:// https:// or ftp://
				<br/>
				<span>Enter a URL : </span>
				<input value="http://" class="validate[required,custom[url]] text-input" type="text" name="url" id="url" />
			</label>
		</fieldset>
		<fieldset>
			<legend>
				Email
			</legend>
			<label>
				<span>Email address : </span>
				<input value="forced_error" class="validate[required,custom[email]] text-input" type="text" name="email" id="email" />
			</label>
		</fieldset>
		<fieldset>
			<legend>
				IP Address
			</legend>
			<label>
				<span>IP: </span>
				<input value="192.168.3." class="validate[required,custom[ipv4]] text-input" type="text" name="ip" id="ip" />
			</label>
		</fieldset>
		<fieldset>
			<legend>
				Date
			</legend>
			<label>
				ISO 8601 dates only YYYY-mm-dd
				<br/>
				<span>Date: </span>
				<input value="201-12-01" class="validate[required,custom[date]] text-input" type="text" name="date" id="date" />
			</label>
		</fieldset>
		<fieldset>
			<legend>
				Number
			</legend>
			<label>
				a signed floating number, ie: -3849.354, 38.00, 38, .77
				<br/>
				<span>Number: </span>
				<input value="-33.87a" class="validate[required,custom[number]] text-input" type="text" name="number" id="number" />
			</label>
		</fieldset>
		<fieldset>
			<legend>
				Integer
			</legend>
			<label>
				an signed integer: ie +34, 34 or -1
				<br/>
				<span>Number: </span>
				<input value="10.1" class="validate[required,custom[integer]] text-input" type="text" name="integer" id="integer" />
			</label>
		</fieldset>
		<fieldset>
			<legend>
				onlyLetterNumber
			</legend>
			<label>
				<span>only [0-9a-zA-Z]</span>
				<input value="too many spaces obviously" class="validate[required,custom[onlyLetterNumber]] text-input" type="text" name="special" id="special" />
			</label>
		</fieldset>
		<fieldset>
			<legend>
				Only Numbers (char)
			</legend>
			<label>
				<span>only [0-9] and space</span>
				<input value="10.1" class="validate[required,custom[onlyNumberSp]] text-input" type="text" name="onlynumber" id="onlynumber" />
			</label>
		</fieldset>
		<fieldset>
			<legend>
				OnlyLetter
			</legend>
			<label>
				<span>only ascii letters, space and '</span>
				<input value="this is an invalid char '.'" class="validate[required,custom[onlyLetterSp]] text-input" type="text" name="onlyascii" id="onlyascii" />
			</label>
		</fieldset><input class="submit" type="submit" value="Validate &amp; Send the form!"/><hr/>
	</form>
<a href="#" onclick="alert('is the form valid? '+jQuery('#formID').validationEngine('validate'))">Evaluate form</a>
</body></html>
