<?php

function check_url($url) {
    $c = curl_init();
    curl_setopt($c, CURLOPT_URL, $url);
    curl_setopt($c, CURLOPT_HEADER, 1); // читать заголовок
    curl_setopt($c, CURLOPT_NOBODY, 1); // читать ТОЛЬКО заголовок без тела
    curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($c, CURLOPT_FRESH_CONNECT, 1); // не использовать cache
    if (!curl_exec($c)) return false;

    $httpcode = curl_getinfo($c, CURLINFO_HTTP_CODE);
    return ($httpcode < 400);
}

function latex($text, $case=0) {
   switch($case) {
      case 1:
         $url = "http://www.mathtran.org/cgi-bin/mathtran?D=1;tex=";
         break;
      case 2:
         $url = "http://www.forkosh.dreamhost.com/mimetex.cgi?";
         break;
      case 3:
         $url = "http://chart.apis.google.com/chart?cht=tx&chl=";
      case 0:
      default:
         $url = "http://www.codecogs.com/eq.latex?";
   }
   if (!check_url($url)) latex($text, ($case++%4));
   $pattern = "#\\$\\$(.*?)\\$\\$#si"; 
   $replace = "<IMG src=\"".$url."\\1\" align=\"middle\">";
   return preg_replace($pattern, $replace, $text);
}

?>