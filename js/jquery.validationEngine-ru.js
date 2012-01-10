

(function($) {
	$.fn.validationEngineLanguage = function() {};
	$.validationEngineLanguage = {
		newLang: function() {
			$.validationEngineLanguage.allRules = 	{"required":{    			// Add your regex rules here, you can take telephone as an example
						"regex":"none",
						"alertText":"* Данное поле обязательно",
						"alertTextCheckboxMultiple":"* Выберите опцию",
						"alertTextCheckboxe":"* Это обязательный пункт"},
					"length":{
						"regex":"none",
						"alertText":"* Длина поля от ",
						"alertText2":" до ",
						"alertText3": " символов"},
					"maxCheckbox":{
						"regex":"none",
						"alertText":"* Checks allowed Exceeded"},	
					"minCheckbox":{
						"regex":"none",
						"alertText":"* Please select ",
						"alertText2":" options"},	
					"confirm":{
						"regex":"none",
						"alertText":"* Your field is not matching"},		
					"telephone":{
						"regex":"/^[0-9\-\(\)\ ]+$/",
						"alertText":"* Invalid phone number"},	
					"all":{
						"regex":"/^[a-zA-Zа-яА-Я0-9\-\(\)\ ]+$/",
						"alertText":"* Недопустимы специальные символы"},	
					"email":{
						"regex":"/^[a-zA-Z0-9_\.\-]+\@([a-zA-Z0-9\-]+\.)+[a-zA-Z0-9]{2,4}$/",
						"alertText":"* Неправильный e-mail"},	
					"date":{
                         "regex":"/^[0-9]{2}\.\[0-9]{2}\.\[0-9]{4}$/",
                         "alertText":"* Дата должна быть в формате ДД-ММ-ГГГГ"},
					"ege":{
                         "regex":"/^[0-9]{2}\-\[0-9]{9}\-\[0-9]{2}$/",
                         "alertText":"* Неправильный формат документа ЕГЭ"},
					"kodp":{
                         "regex":"/^[0-9]{3}\-\[0-9]{3}$/",
                         "alertText":"* Неправильный формат кода подразделения"},
					"scores":{
					    "regex":"/^(100)$|^[0-9]{1,2}$/",
                         "alertText":"* Баллы указаны неверно"},
					"onlyNumber":{
						"regex":"/^[0-9]+$/",
						"alertText":"* Допускаются только цифры"},	
					"onlyRussians":{
						"regex":"/^[а-яА-Я\ ]+$/",
						"alertText":"* Допускаются только русские буквы"},	
					"onlyRussiansDotDash":{
						"regex":"/^[а-яА-Я\ \.\-]+$/",
						"alertText":"* Допускаются '.', '-' и русские буквы"},	
					"onlyNumberDash":{
						"regex":"/^[0-9\-]+$/",
						"alertText":"* Допускаются только цифры и -"},	
					"noSpecialCaracters":{
						"regex":"/^[0-9a-zA-Z]+$/",
						"alertText":"* Специальные символы запрещены"},	
					"ajaxUser":{
						"file":"validateUser.php",
						"extraData":"name=eric",
						"alertTextOk":"* This user is available",	
						"alertTextLoad":"* Loading, please wait",
						"alertText":"* This user is already taken"},	
					"ajaxName":{
						"file":"validateUser.php",
						"alertText":"* This name is already taken",
						"alertTextOk":"* This name is available",	
						"alertTextLoad":"* Loading, please wait"},		
					"onlyLetter":{
						"regex":"/^[a-zA-Z\ \']+$/",
						"alertText":"* Letters only"},
					"validateEgeScores":{
					        "nname":"validateEgeScores",
						"alertText":"* Баллы указаны неверно"},
					"validate2fields":{
    					"nname":"validate2fields",
    					"alertText":"You must have a firstname and a lastname"}	
					}	
					
		}
	}
})(jQuery);

$(document).ready(function() {	
	$.validationEngineLanguage.newLang()
});