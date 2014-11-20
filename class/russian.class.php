<?php
function russianDate($format, $timestamp) 
{
   $translation = array(
      "am" => "дп", "pm" => "пп",
      "AM" => "ДП", "PM" => "ПП",
      "Monday" => "Понедельник", "Mon" => "Пн",
      "Tuesday" => "Вторник",    "Tue" => "Вт",
      "Wednesday" => "Среда",    "Wed" => "Ср",
      "Thursday" => "Четверг",   "Thu" => "Чт",
      "Friday" => "Пятница",     "Fri" => "Пт",
      "Saturday" => "Суббота",   "Sat" => "Сб",
      "Sunday" => "Воскресенье", "Sun" => "Вс",
      "January" => "Января",     "Jan" => "Янв",
      "February" => "Февраля",   "Feb" => "Фев",
      "March" => "Марта",        "Mar" => "Мар",
      "April" => "Апреля",       "Apr" => "Апр",
      "May" => "Мая",            "May" => "Мая",
      "June" => "Июня",          "Jun" => "Июн",
      "July" => "Июля",          "Jul" => "Июл",
      "August" => "Августа",     "Aug" => "Авг",
      "September" => "Сентября", "Sep" => "Сен",
      "October" => "Октября",    "Oct" => "Окт",
      "November" => "Ноября",    "Nov" => "Ноя",
      "December" => "Декабря",   "Dec" => "Дек",
      "st" => "ое", "nd" => "ое", "rd" => "е", "th" => "ое",
      );
   return strtr(date($format, $timestamp), $translation);
}

function num2words($int) 
{
    $words = array("", "один", "два", "три", "четыре", "пять", "шесть", "семь", "восемь", "девять", "десять",
              "одиннадцать", "двенадцать", "тринадцать", "четыраднацать", "пятнадцать", "шестнадцать", "семнадцать", "восемнадцать", "девятнадцать");
    $words_dec = array("", "десять", "двадцать", "тридцать", "сорок", "пятьдесят", "шестьдесят", "семьдесят", "восемьдесят", "девяносто");

    if ($int < 20) {
        return $words[$int];
    } else {
        return $words_dec[floor($int/10)]." ".$words[$int % 10]; 
    }
}

function getTerm($array) 
{
    if (is_array($array) === FALSE) {
        $array = array($array);
    }

    if ($array[0] == 1) {
        $string = $array[0]." год";
    } else if ($array[0] > 1 && $array[0] < 5) {
        $string = $array[0]." года";
    } else {
        $string = $array[0]." лет";
    }

    if (isset($array[1]) === TRUE && $array[1] > 0) {
        if ($array[1] == 1) {
            $string .= " ".$array[1]." месяц";
        } else if ($array[1] > 1 && $array[1] < 5) {
            $string .= " ".$array[1]." месяца";
        } else {
            $string .= " ".$array[1]." месяцев";
        }
    }
    return $string;
}

/**
 * Возвращает сумму прописью
 * @author gzubkov
 * @uses morph(...)
 */
function num2str($num) 
{
    $nul = 'ноль';
    $ten = array(
        array('','один','два','три','четыре','пять','шесть','семь', 'восемь','девять'),
        array('','одна','две','три','четыре','пять','шесть','семь', 'восемь','девять'),
    );
    $a20 = array('десять','одиннадцать','двенадцать','тринадцать','четырнадцать' ,'пятнадцать','шестнадцать','семнадцать','восемнадцать','девятнадцать');
    $tens = array(2 => 'двадцать','тридцать','сорок','пятьдесят','шестьдесят','семьдесят' ,'восемьдесят','девяносто');
    $hundred = array('','сто','двести','триста','четыреста','пятьсот','шестьсот', 'семьсот','восемьсот','девятьсот');
    $unit = array( // Units
        array('копейка' ,'копейки' ,'копеек',    1),
        array('рубль'   ,'рубля'   ,'рублей'    ,0),
        array('тысяча'  ,'тысячи'  ,'тысяч'     ,1),
        array('миллион' ,'миллиона','миллионов' ,0),
        array('миллиард','милиарда','миллиардов',0),
    );
    //
    list($rub, $kop) = explode('.',sprintf("%015.2f", floatval($num)));
    $out = array();
    if (intval($rub) > 0) {
        foreach(str_split($rub,3) as $uk => $v) { // by 3 symbols
            if (!intval($v)) continue;
            $uk = sizeof($unit) - $uk - 1; // unit key
            $gender = $unit[$uk][3];
            list($i1, $i2, $i3) = array_map('intval', str_split($v,1));
            // mega-logic
            $out[] = $hundred[$i1]; # 1xx-9xx
            if ($i2 > 1) {
                $out[] = $tens[$i2].' '.$ten[$gender][$i3]; # 20-99
            } else {
                $out[] = $i2 > 0 ? $a20[$i3] : $ten[$gender][$i3]; # 10-19 | 1-9
            }
            // units without rub & kop
            if ($uk > 1) {
                $out[] = morph($v,$unit[$uk][0],$unit[$uk][1],$unit[$uk][2]);
            }
        } //foreach
    }
    else $out[] = $nul;
    return trim(preg_replace('/ {2,}/', ' ', join(' ',$out)));
}

/**
 * Склоняем словоформу
 * @ author gzubkov
 */
function morph($n, $f1, $f2, $f5) {
    $n = abs(intval($n)) % 100;
    if ($n > 10 && 
        $n < 20) {
        return $f5;
    }

    $n = $n % 10;
    if ($n > 1 && 
        $n < 5) {
        return $f2;
    }
    
    if ($n == 1) {
        return $f1;
    }
    return $f5;
}

/**
 * Склоняем слова (модуль morpher.ru)
 * @ author gzubkov
 */
function inflect($text, $padeg)
{
    if (strcmp($text, 'Ооржак') === 0) {
        return $text;
    }

    $credentials = array('Username'=>'gzubkov', 
                         'Password'=>'qwer123');
    
    $header = new SOAPHeader('http://morpher.ru/', 
                             'Credentials', $credentials);        
    
    $url = 'http://morpher.ru/WebService.asmx?WSDL';

    $client = new SoapClient($url); 
    $client->__setSoapHeaders($header);
    $params = array('parameters' => array('s' => $text));
    $result = (array) $client->__soapCall('GetXml', $params); 

    $singular = (array) $result['GetXmlResult']; 
    return $singular[$padeg];
}
