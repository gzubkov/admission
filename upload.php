<?php
require_once '../conf.php';
require_once 'class/mysql.class.php';

$msl = new dMysql();

if ($_SESSION['applicant_id'] != $_POST['applicant_id'] || 
    $_POST['applicant_hash'] != md5($CFG_salted.$_SESSION['applicant_id'])) {
    echo "error";
    exit(0);
} 

if (file_exists($CFG_uploaddir.$_POST['applicant_id']) === false) {
    mkdir($CFG_uploaddir.$_POST['applicant_id']);
}
$uploadfile = $CFG_uploaddir.$_POST['applicant_id'].'/'.basename($_FILES['userfile']['name']);

if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile) === true) {
    $array = array('applicant' => $_POST['applicant_id'],
                   'edu_doc' => $_POST['doc_id'],
                   'serie' => $_POST['doc_serie'],
                   'number' => $_POST['doc_number'],
                   'institution' => $_POST['doc_institution'],
                   'date' => implode("-", array_reverse(explode(".",$_POST['doc_date']))),
                   'specialty' => $_POST['doc_specialty'],
                   'filename' => basename($_FILES['userfile']['name']));

    if ($msl->insertArray('reg_applicant_edu_doc', $array)) {
        echo "success";
    } else {
        echo "error";
    }
} else {
    echo "error";
}
