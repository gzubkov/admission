<?php
// just require TCPDF instead of FPDF
require_once('../../../modules/tcpdf/tcpdf.php');
require_once('../../../modules/fpdi/fpdi.php');
require_once('../../../modules/russian_date.php');
require_once('../../conf.php');
require_once('../class/mysql.class.php');
require_once('../class/catalog.class.php');
require_once('../class/price.class.php');
require_once('../class/pdf.class.php');

if ($r['region'] != $_SESSION['joomlaregion'] && $_SESSION['rights'] != "admin") {
    exit(0);
}
if (!is_numeric($_REQUEST['applicant'])) {exit(0);}
$applicant_id = $_REQUEST['applicant'];

$msl = new dMysql();
$r = $msl->getarray("SELECT surname,name,second_name,regaddress,CONCAT(doc_serie,' ',doc_number,', выдан ',doc_issued) as doc, doc_date, region, catalog FROM partner_applicant 
WHERE id = ".$applicant_id.";");

$addr = $msl->getarray("SELECT * FROM partner_applicant_address WHERE applicant_id = ".$applicant_id." AND `type`=1;");

$rval = $msl->getarray("SELECT a.firm, a.longfirm, a.rsurname, a.rname, a.`rsecond_name`, a.`name_rp`, a.pgid, b.name as gpos, b.name_rp as gposrp, c.name_tp as orgdoc, a.ckt_num, a.ckt_date, a.inn, a.kpp, a.bank, a.legaladdress, a.rs, a.ks, a.bik 
                  FROM `partner_regions` a LEFT JOIN `partner_position` b ON a.gposition=b.id LEFT JOIN `partner_organizational_documents` c ON a.orgdoc=c.id WHERE a.id = ".$r['region'].";");


$cat = new Catalog($msl);
$kval = $cat->getInfo($r['catalog']);
unset($cat);

$prc = new Price($msl);
$cval = $prc->getPricePercentByPgid($rval['pgid'], $r['catalog'],1,1,0);
unset($prc);

// initiate PDF
$pdf = new PDF('pdf/dog_ckt_rp.pdf');

$pdf->SetFont("times", "", 12);
$pdf->splitText($rval['longfirm'], array(array(25,79.2),array(15,   83.4)), 89, 1);

$pdf->Text(62, 87.4,   $rval['gposrp']." ".$rval['name_rp']);
$pdf->Text(78, 91.6,   mb_convert_case($rval['orgdoc'], MB_CASE_TITLE, "UTF-8"));

$pdf->Text(165.2, 91.6,   $rval['ckt_num']);
$pdf->Text(15.6, 95.6,   date('d.m.Y', strtotime($rval['ckt_date'])));

$pdf->Text(45, 101.8, $r['surname']." ".$r['name']." ".$r['second_name']);

$pdf->Text(143, 167.8, $kval['term']);
$pdf->Text(69, 141, $kval['name']);

$pdf->newPage(); 
$pdf->newPage(); 

$pdf->Text(155, 62.7, $cval['price']);

$pdf->newPage(); 

$pdf->SetFont("times", "", 11);
$pdf->splitText("ИНН ".$rval['inn'].", КПП ".$rval['kpp'].", ".$rval['firm'].".", array(array(62.2, 125.33),array(15,   129.33)), 63, 1);

$pdf->Text(24.8, 130, "Юридический адрес: ".$rval['legaladdress'].".");

$pdf->splitText("Р/счет ".$rval['rs']." в ".$rval['bank'].", кор./счет ".$rval['ks'].", БИК ".$rval['bik'].".", array(array(25, 134.6),array(15,   140.0)), 94, 1);

$pdf->Text(58.8, 150.4, $r['surname']." ".$r['name']." ".$r['second_name'].".");
$pdf->Text(24.8, 155.4, "Адрес регистрации: ".$addr['index'].", ".$addr['city'].", ".$addr['street'].", ".$addr['home'].", ".$addr['flat'].".");

$pdf->splitText("Паспорт: ".$r['doc'].", ".date('d.m.Y', strtotime($r['doc_date'])).".", array(array(25, 160.4),array(15,   165.0)), 96, 1);

$pdf->Text(127, 191.4, mb_convert_case($rval['gpos'], MB_CASE_TITLE, "UTF-8")); // 151.2
$pdf->Text(164.4, 200.8, substr($rval['rname'],0,2).".".substr($rval['rsecond_name'],0,2).". ".$rval['rsurname']);

$pdf->Text(65.4, 242, substr($r['name'],0,2).".".(($r['second_name'] != "") ? substr($r['second_name'],0,2).". " : "").$r['surname']);
unset($msl);
$pdf->Output('dogovor_pl_usl.pdf', 'D');
?>
