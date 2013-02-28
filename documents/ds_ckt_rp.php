<?php
// just require TCPDF instead of FPDF
require_once('../../../modules/tcpdf/tcpdf.php');
require_once('../../../modules/fpdi/fpdi.php');
require_once('../../../modules/russian_date.php');
require_once('../../conf.php');
require_once('../class/mysql.class.php');
require_once('../class/price.class.php');
require_once('../class/pdf.class.php');

$_REQUEST['applicant'] = substr($_REQUEST['applicant'], 1);
if (!is_numeric($_REQUEST['applicant'])) exit(0);
$msl = new dMysql();
$r = $msl->getarray("SELECT region,surname,name,second_name,catalog,pay FROM partner_applicant WHERE id = ".$_REQUEST['applicant'].";");

if ($r['region'] != $_SESSION['joomlaregion'] && $_SESSION['rights'] != "admin") {
    exit(0);
}
$rval = $msl->getarray("SELECT a.longfirm, a.rsurname, a.rname, a.`rsecond_name`, a.`name_rp`, a.pgid ,b.name as gpos,b.name_rp as gposrp, c.name_rp as orgdoc 
                  FROM `partner_regions` a LEFT JOIN `partner_position` b ON a.gposition=b.id LEFT JOIN `partner_organizational_documents` c ON a.orgdoc=c.id WHERE a.id = ".$r['region'].";");

// initiate PDF
$pdf = new PDF('pdf/ds_ckt_rp.pdf');

//$arr = splitstring($rval['longfirm'], 96, 1); 
$pdf->SetFont("times", "", 12);
$pdf->Text(25, 55.0, $arr[0]);
$pdf->Text(15, 59.2, $arr[1]);

$pdf->SetFont("times", "", 11);
//$arr = splitstring($rval['gposrp']." ".$rval['name_rp'], 56, 1); 
$pdf->Text(107, 63.2, $arr[0]);
$pdf->Text(15,  67.2, $arr[1]);

$pdf->Text(15, 71.4, mb_convert_case($rval['orgdoc'], MB_CASE_TITLE, "UTF-8"));

$pdf->Text(46, 75.4, $r['surname']." ".$r['name']." ".$r['second_name']);

if ($r['pay'] > 0) {
    $pdf->Text(162, 110.4, $r['pay']);

    $price = new Price($msl);
    $pay = $price->getPriceByPgid($rval['pgid'], $r['catalog'], 3, $r['pay'], 0, 1);
    unset($price);

    $pdf->Text(42, 143.2, $pay[0]+$pay[1]);
    $pdf->Text(52, 157.2, $pay[0]);
    $pdf->Text(63, 166.2, $pay[1]);
}

$pdf->SetFont("times", "", 11);
$pdf->Text(127.6, 224.4, mb_convert_case($rval['gpos'], MB_CASE_TITLE, "UTF-8"));
$pdf->Text(161.6, 233.4, substr($rval['rname'],0,2).".".(($rval['rsecond_name'] != "")?substr($rval['rsecond_name'],0,2).". ":" ").$rval['rsurname']);
$pdf->Text(59.4,  270,   substr($r['name'],0,2).".".(($r['second_name'] != "")?substr($r['second_name'],0,2).". ":" ").$r['surname']);

//$pdf->Text(126, 234, mb_strtolower(russian_date( mktime(), 'j        F' ), 'UTF-8'));
//$pdf->Text(164.6, 234, substr(date('y'),1));

unset($msl);
$pdf->Output('ds.pdf', 'D');
?>
