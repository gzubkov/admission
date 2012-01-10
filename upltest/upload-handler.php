<?php

$file = fopen('ex2.txt', a);
foreach($_REQUEST as $k=>$v) fputs($file, $k."=".$v."\n");
foreach($_FILES['myfile'] as $k=>$v) fputs($file, $k."=".$v."\n");

fclose($file);

echo "success";
//$uploaddir = '/var/www/uploads/';
//$uploadfile = $uploaddir . basename($_FILES['userfile']['name']);
if (0) {
if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
  echo "success";
} else {
  // WARNING! DO NOT USE "FALSE" STRING AS A RESPONSE!
  // Otherwise onSubmit event will not be fired
  echo "error";
}
}
?>
