<?php
// just require TCPDF instead of FPDF
require_once('../../../modules/tcpdf/tcpdf.php');
require_once('../../../modules/fpdi/fpdi.php');
require_once('../../../modules/russian_date.php');
require_once('../../../modules/mysql.php');
require_once('../../conf.php');
require_once('../class/catalog.class.php');


class PDF extends FPDI {
    function Header() {}    
    function Footer() {}
}

if (!is_numeric($_REQUEST['request'])) exit(0);
$request_id = $_REQUEST['request'];
$msl = new dMysql();

$req = $msl->getarray("SELECT * FROM reg_request WHERE id='".$request_id."' LIMIT 1");

$applicant_id = $req['applicant_id'];

//if ($_SESSION['applicant_id'] != $applicant_id && $_SESSION['rights'] != "admin") exit(0);

// --- Базовый запрос (сведения об абитуриенте, регион, цена (руб., коп.)) --- //
$r = $msl->getarray(
"SELECT reg_applicant.*
FROM reg_applicant 
WHERE reg_applicant.id = ".$applicant_id." LIMIT 1;");

// initiate PDF
$pdf = new PDF();
$pdf->SetMargins(PDF_MARGIN_LEFT, 40, 10);
$pdf->SetAutoPageBreak(true, 0);

$pdf->AddPage();

$pdf->SetFont("times", "B", 13);
$pdf->Text(190.5, 7.8, $request_id.($req['internet']?"И":""));

$pdf->SetFont("times", "B", 14);
$pdf->SetXY(0, 7.8);
$pdf->MultiCell(0, 0, 'РЕШЕНИЕ', 0, 'C', 0, 1, '', '', true);
$pdf->SetFont("times", "", 12);
$pdf->SetXY(0, 12);
$pdf->MultiCell(0, 0, 'о возможности зачисления абитуриента', 0, 'C', 0, 0, '', '', true);

$pdf->SetFont("times", "", 14);
//$pdf->Text(14.5, 25, "ФИО: <B>".$r['surname']." ".$r['name']."</B> ".$r['second_name']);

$pdf->WriteHtmlCell(0,0, 13, 20, "ФИО: <B>".$r['surname']." ".$r['name']." ".$r['second_name']."</B>.");

$pdf->SetFont("times", "", 12);
$pdf->Text(14.5, 31, "Дата рождения: ".date('d.m.Y', strtotime($r['birthday'])));
$pdf->Text(14.5, 37, "Гражданство: ".$r['citizenry']);
$pdf->Text(14.5, 43, "Мобильный телефон: +7 (".$r['mobile_code'].") ".$r['mobile']);
$pdf->Text(14.5, 49, "e-mail: ".$r['e-mail']);
$pdf->Text(14.5, 55, "Место регистрации: ".$r['regaddress']);

$pdf->Line(12, 58, 200, 58, array('width' => 0.4));

$cat = new Catalog();
$rval = $cat->getInfo($req['catalog'], $req['profile']);
unset($cat);

if ($rval['profile'] != '') {
    $rval['profile'] = " (профиль: ".$rval['profile'].")";
}

$pdf->SetFont("times", "", 14);
$pdf->WriteHtmlCell(0,0, 13.5, 58, "Образовательная программа: <B>".$rval['spec_code']." ".$rval['name']."</B>".$rval['profile']);

$pdf->Line(12, 72, 200, 72, array('width' => 0.4));

$pdf->SetFont("times", "", 14);
$pdf->Text(14.5, 80, "Рекомендовать к зачислению:");
$pdf->WriteHtmlCell(0,0, 13.5, 80, "
на __ семестр __ курса на базе      СО;     СПО выбранной образовательной программы с __ досдачами (из них __ платных);<BR>
на __ семестр __ курса на базе      СО;     СПО ___________________________________ ______________________________________________________________________________________________________________________ с __ досдачами (из них __ платных).");

$pdf->Rect(81, 82, 4, 4, 'D');
$pdf->Rect(95.4, 82, 4, 4, 'D');

$pdf->Rect(81, 94.5, 4, 4, 'D');
$pdf->Rect(95.4, 94.5, 4, 4, 'D');

$pdf->Rect(14.6, 113.5, 4, 4, 'D');
$pdf->Text(20.2, 117, "назначить вступительные испытания;");

$pdf->Rect(14.6, 119.5, 4, 4, 'D');
$pdf->Text(20.2, 123, "без вступительных испытаний;");

$y = 123;

// баллы ЕГЭ
$rval = $msl->getarray("SELECT name, score FROM reg_applicant_scores LEFT JOIN reg_subjects ON reg_applicant_scores.subject = reg_subjects.id WHERE `request_id` = ".$request_id." AND `ege` = 1 ORDER BY subject ASC", 1);

if ($rval != 0) {
    $a = array();  
    foreach ($rval as $v) {
        $a[] = $v['name']." - ".$v['score'];
    }
    $txt = implode(', ', $a);
    $pdf->Rect(14.6, $y+2.2, 4, 4, 'D'); // 129-3.5
    $pdf->WriteHtmlCell(0,0, 19.2, $y, "засчитать результаты ЕГЭ (".$txt.").");

    $y+= 12;
}

$pdf->WriteHtmlCell(0,0, 13.5, $y, "Для рассмотрения возможности зачисления требуется предоставление абитуриентом: ________________________________________________________________________________________________________________________________________________________________________________________________________________________________.");

$y+= 24;

$pdf->Rect(14.6, $y+2.2, 4, 4, 'D'); 
$pdf->WriteHtmlCell(0,0, 19.2, $y, "отказать в зачислении по причине ___________________________________________ ________________________________________________________________________.");

$y+= 14;
$pdf->WriteHtmlCell(120,0, 13.5, $y, "Сформировано и передано на рассмотрение <B>".date('d.m.Y')."</B>.", 0, 0);
$y+= 10;
$pdf->WriteHtmlCell(120,0, 13.5, $y, "Начальник управления образовательных программ", 0, 0);
$pdf->WriteHtmlCell(0,0,140,$y, "Виноградова И.А.",0,0,0,1,'R');
$y+= 7;
$pdf->WriteHtmlCell(0,0,75,$y, "\"___\" _____________ 201_ г.",0,0,0,1,'C');
$y+= 10;
$pdf->WriteHtmlCell(120,0, 13.5, $y, "Начальник коммерческого управления", 0, 0);
$pdf->WriteHtmlCell(0,0,140,$y, "Сащенко Ю.В.",0,0,0,1,'R');
$y+= 7;
$pdf->WriteHtmlCell(0,0,75,$y, "\"___\" _____________ 201_ г.",0,0,0,1,'C');
$pdf->Output('des.pdf', 'D');
?>
