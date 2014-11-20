<?php
require_once('../../conf.php');
require_once('../class/mysql.class.php');
require_once('../class/catalog.class.php');
require_once('../class/pdf.class.php');
require_once('../class/documents.class.php');

$msl = new dMysql();
$appl = new Applicant($msl, $_REQUEST['applicant']);

$r = $appl->getInfo();

$pdf = new PDF();
$pdf->SetMargins(PDF_MARGIN_LEFT, 40, 10);
$pdf->SetAutoPageBreak(true, 0);
$pdf->AddPage();

$pdf->SetFont("times", "B", 13);
$pdf->Text(190.5, 7.8, $_REQUEST['applicant'].($r['internet']?"И":""));

$pdf->SetFont("times", "B", 14);
$pdf->SetXY(0, 7.8);
$pdf->MultiCell(0, 0, 'РЕШЕНИЕ', 0, 'C', 0, 1, '', '', true);
$pdf->SetFont("times", "", 12);
$pdf->SetXY(0, 12);
$pdf->MultiCell(0, 0, 'о возможности зачисления абитуриента', 0, 'C', 0, 0, '', '', true);

$pdf->SetFont("times", "", 14);

$pdf->WriteHtmlCell(0,0, 13, 20, "ФИО: <B>".$appl->surname." ".$appl->name." ".$appl->second_name."</B>.");

$pdf->SetFont("times", "", 12);
$pdf->Text(14.5, 31, "Дата рождения: ".date('d.m.Y', strtotime($r['birthday'])));
$pdf->Text(14.5, 37, "Гражданство: ".$r['citizenry']);
$pdf->Text(14.5, 43, "Мобильный телефон: +7 (".$r['mobile_code'].") ".$r['mobile']);
$pdf->Text(14.5, 49, "e-mail: ".$r['e-mail']);

$pdf->WriteHtmlCell(0,0, 13.5, 50, "Адрес регистрации: ".$appl->getRegAddress());

$pdf->Line(12, 68, 200, 68, array('width' => 0.4));

$cat = new Catalog($msl);
$rval = $cat->getInfo($appl->catalog, $appl->profile);
unset($cat);

if (isset($rval['profile'])) {
    $rval['profile'] = " (профиль: ".$rval['profile'].")";
} else {
    $rval['profile'] = "";
}

$pdf->SetFont("times", "", 14);
$pdf->WriteHtmlCell(0,0, 13.5, 68, "Образовательная программа: <B>".$rval['code']." ".$rval['name']."</B>".$rval['profile']);

$pdf->Line(12, 90, 200, 90, array('width' => 0.4));

$pdf->SetFont("times", "", 14);
//$pdf->Text(14.5, 80, "Рекомендовать к зачислению:");
/*
$pdf->WriteHtmlCell(0,0, 13.5, 80, "
на __ семестр __ курса на базе      СО;     СПО выбранной образовательной программы с __ досдачами (из них __ платных);<BR>
на __ семестр __ курса на базе      СО;     СПО ___________________________________ ______________________________________________________________________________________________________________________ с __ досдачами (из них __ платных).");
*/

$pdf->WriteHtmlCell(0,0, 13.5, 90, "Рекомендовать к зачислению на 1 семестр 1 курса на базе _________________________ 
    ___________________________________________________________________________ 
    ___________________________________________________________________________ 
    __________________________________________________________________________.");

/*
$pdf->Rect(81, 82, 4, 4, 'D');
$pdf->Rect(95.4, 82, 4, 4, 'D');

$pdf->Rect(81, 94.5, 4, 4, 'D');
$pdf->Rect(95.4, 94.5, 4, 4, 'D');
*/
$pdf->Rect(14.6, 123.5, 4, 4, 'D');
$pdf->Text(20.2, 127, "назначить вступительные испытания;");

$pdf->Rect(14.6, 129.5, 4, 4, 'D');
$pdf->Text(20.2, 133, "без вступительных испытаний;");

$y = 133;

// баллы ЕГЭ
$rval = $appl->getEge();

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
