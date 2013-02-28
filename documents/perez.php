<?php
require_once('../class/mysql.class.php');
require_once('../class/catalog.class.php');
require_once('../class/pdf.class.php');
require_once('../class/documents.class.php');
require_once('../../conf.php');

$msl = new dMysql();
new FabricApplicant($appl, $msl, $_REQUEST['applicant_id']);

$cat = new Catalog($msl);
$univ = $cat->getUniversityInfo($appl->catalog);
unset($cat);

$pdf = new PDF('pdf/perez.pdf');

$pdf->SetFont("times", "", 12);

$pdf->SetFillColor(255,255,255);
$pdf->Rect(100,10,100,60,'F');
$pdf->WriteHtmlCell(80,0, 110, 18, "Ректору<BR>
".$univ['type']." \"".$univ['name']."\" ".$univ['rsurname_rp']." ".substr($univ['rname_rp'],0,2).".".substr($univ['rsecond_name_rp'],0,2).".<BR> 
от ".$appl->surname." ".$appl->name." ".$appl->second_name,0,0,true);

//$pdf->splitText("Ректору ".$univ['type']." \"".$univ['name']."\" ".$univ['rsurname_rp']." ".substr($univ['rname_rp'],0,2).".".substr($univ['rsecond_name_rp'],0,2).". от ".$appl->surname." ".$appl->name." ".$appl->second_name, array(array(100, 30),array(100,40),array(100,50),array(100,60)), array(55,55,55), 0);

$pdf->Output('perez.pdf', 'D');
?>
