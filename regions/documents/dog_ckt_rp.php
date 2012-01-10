<?php
// just require TCPDF instead of FPDF
require_once('../../../../modules/tcpdf/tcpdf.php');
require_once('../../../../modules/fpdi/fpdi.php');
require_once('../../../../modules/russian_date.php');
require_once('../../../../modules/mysql.php');
require_once('../../../conf.php');
require_once('../../class/catalog.class.php');
require_once('../../class/price.class.php');

class PDF extends FPDI {
    /**
     * "Remembers" the template id of the imported page
     */
    var $_tplIdx;
    
    /**
     * include a background template for every page
     */
    function Header() {
        if (is_null($this->_tplIdx)) {
            $this->setSourceFile('dog_ckt_rp.pdf');
            $this->_tplIdx = $this->importPage(1);
        }
    }
    
    function Footer() {}
}

if ($r['region'] != $_SESSION['joomlaregion'] && $_SESSION['rights'] != "admin") {
    exit(0);
}
if (!is_numeric($_REQUEST['applicant'])) {exit(0);}
$applicant_id = $_REQUEST['applicant'];

$msl = new dMysql();
$r = $msl->getarray("SELECT surname,name,second_name,regaddress,CONCAT(doc_serie,' ',doc_number,', ',doc_issued) as doc, doc_date, region, catalog FROM partner_applicant 
WHERE id = ".$applicant_id.";");

$rval = $msl->getarray("SELECT a.firm, a.longfirm, a.rsurname, a.rname, a.`rsecond_name`, a.`name_rp`, a.pgid, b.name as gpos, b.name_rp as gposrp, c.name_tp as orgdoc, a.dog_num, a.dog_date, a.inn, a.kpp, a.bank, a.legaladdress, a.rs, a.ks, a.bik 
                  FROM `partner_regions` a LEFT JOIN `partner_position` b ON a.gposition=b.id LEFT JOIN `partner_organizational_documents` c ON a.orgdoc=c.id WHERE a.id = ".$r['region'].";");


$cat = new Catalog();
$kval = $cat->getInfo($r['catalog']);
unset($cat);

$prc = new Price();
$cval = $prc->getPricePercentByPgid($rval['pgid'], $r['catalog'],1,1,0);
unset($prc);

// initiate PDF
$pdf = new PDF();
$pdf->SetMargins(PDF_MARGIN_LEFT, 40, 0);
$pdf->SetAutoPageBreak(true, 0);

// add a page
$pdf->AddPage();
$pdf->useTemplate($pdf->_tplIdx);

$arr = splitstring($rval['longfirm'], 89, 1); 
$pdf->SetFont("times", "", 12);
$pdf->Text(25, 66.8, $arr[0]);
$pdf->Text(15, 71,   $arr[1]);

$pdf->Text(82, 75,   $rval['gposrp']." ".$rval['name_rp']);
$pdf->Text(69, 79.3,   mb_convert_case($rval['orgdoc'], MB_CASE_TITLE, "UTF-8"));

$pdf->Text(174, 79.3,   $rval['dog_num']);
$pdf->Text(20, 83.3,   date('d.m.Y', strtotime($rval['dog_date'])));

$pdf->Text(45, 87.3, $r['surname']." ".$r['name']." ".$r['second_name']);

$pdf->Text(179, 109.6, $kval['term']);
$pdf->Text(46, 121.8, $kval['name']);


$pdf->addPage(); 
$pdf->useTemplate($pdf->importPage(2));

$pdf->addPage(); 
$pdf->useTemplate($pdf->importPage(3));

$pdf->Text(155, 23.8, $cval['price']);

$pdf->Text(90, 51.4, (100-$cval['percent']));
$pdf->Text(103, 51.4, num2words(100-$cval['percent']));

$pdf->Text(102.4, 60.7, $cval['percent']);
$pdf->Text(116, 60.7, num2words($cval['percent']));

$pdf->addPage(); 
$pdf->useTemplate($pdf->importPage(4));

$pdf->SetFont("times", "", 11);
$arr = splitstring($rval['inn']."/".$rval['kpp'].", ".$rval['firm'].".", 63, 1); 
$pdf->Text(82.2, 95.33, $arr[0]);
$pdf->Text(15,   99.33, $arr[1]);

$pdf->Text(60.6, 103.5, $rval['legaladdress'].".");

$arr = splitstring($rval['rs']." в ".$rval['bank'].", кор./счет ".$rval['ks'].", БИК ".$rval['bik'].".", 94, 1); 
$pdf->Text(36.2, 107.6, $arr[0]);
$pdf->Text(15,   112.0, $arr[1]);

$pdf->Text(58.8, 120.4, $r['surname']." ".$r['name']." ".$r['second_name'].".");
$pdf->Text(69.0, 124.6, $r['regaddress'].".");

$arr = splitstring($r['doc'].", ".date('d.m.Y', strtotime($r['doc_date'])).".", 96, 1); 
$pdf->Text(31.0, 128.6, $arr[0]);
$pdf->Text(15, 134.6, $arr[1]);

$pdf->Text(127, 151.2, mb_convert_case($rval['gpos'], MB_CASE_TITLE, "UTF-8"));
$pdf->Text(161.4, 160.6, substr($rval['rname'],0,2).".".substr($rval['rsecond_name'],0,2).". ".$rval['rsurname']);

$pdf->Text(58.8, 197.4, substr($r['name'],0,2).".".(($r['second_name'] != "") ? substr($r['second_name'],0,2).". " : "").$r['surname']);
unset($msl);
$pdf->Output('dogovor_pl_usl.pdf', 'D');
?>
