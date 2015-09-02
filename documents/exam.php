<?php
require_once('../class/mysql.class.php');
require_once('../class/pdf.class.php');
require_once('../class/documents.class.php');
require_once('../../conf.php');

$msl = new dMysql();
$applicant_id = $_REQUEST['applicant'];

new FabricApplicant($appl, $msl, $applicant_id);

$r = $appl->getInfo();

$arr = $msl->getarrayById("SELECT id, subject FROM `specialties_subjects` a LEFT JOIN `catalogs` b ON a.specialty=b.specialty WHERE b.id = '".$r['catalog']."' LIMIT 0, 10", 'subject', 'subject');

$pdf = new PDF();
$pdf->SetFont("times", "", 12);

$set = array();    
foreach ($arr as $a) {
    // *** TEMP ***
    if ($a == 8) {
        $a = 4; // меняем информатику на физику - временно (ЮВ обещал доделать билеты)
    }
    // *** TEMP ***

    $rval = $msl->getarray("SELECT ticket FROM `partner_exam` WHERE `applicant_id`='".$r['id']."' AND `subject`='".$a."' LIMIT 1;");
    if ($rval['ticket'] > 0) {
        $q = $rval['ticket'];
    } else {
        $q = rand(1, 5);
	    $msl->insertArray('partner_exam', array('applicant_id' => $r['id'], 'subject' => $a, 'ticket' => $q));
    }

    $filename = 'exam/'.$a.'/'.$q.'.pdf';

    if (file_exists($filename)) {
        $pdf->setSourceFile($filename);
    	$pdf->AddPage();
    	$pdf->useTemplate($pdf->importPage(1));
    	$pdf->Text(85, 78, $r['surname']." ".$r['name']." ".$r['second_name']);

    	$pdf->AddPage();
    	$pdf->useTemplate($pdf->importPage(2));
    }
}

$pdf->Output('exam.pdf', 'D');
?>
