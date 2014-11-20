<?php
require_once('../../modules/tcpdf/tcpdf.php');
require_once('../../modules/fpdi/fpdi.php');

class PDF extends FPDI
{
    public $pagenum = 1;

    public function Header()
    {
        return true;
    }
  
    public function Footer()
    {
        return true;
    }
    
    public function __construct($template = '')
    {
        parent::__construct();

        $this->SetMargins(PDF_MARGIN_LEFT, 40, 0);
        $this->SetAutoPageBreak(true, 0);

        if ($template != '') {
            $this->setSourceFile($template);
            $this->newPage();
        }
        return true;
    }

    public function splitstring($string, $pos, $hang = 1)
    {
        mb_internal_encoding("UTF-8");

        if (!is_array($pos)) {
            $pos = array($pos);
        }
        if (mb_strlen($string) <= $pos[0]) {
            return array($string);
        }

        $arr = array();
        $s = 0;
        
        for ($i = 0; $i < count($pos); $i++) {
            if ($hang == 0) {
                $position = $pos[$i];
            } else {
                $position = mb_strrpos(mb_substr($string, 0, $pos[$i]), ' ') + 1;
            }
            $arr[$i] = mb_substr($string, $s, $position);
            $s += $position; // +1
        }
        $arr[$i++] = mb_substr($string, $s);
        return $arr;
    }

    public function splitText($string, $coords, $size, $hang = 1)
    {
        $arr = $this->splitstring($string, $size, $hang);
        for ($i = 0; $i < sizeof($coords); $i++) {
            if (isset($arr[$i])) {
                $this->Text($coords[$i][0], $coords[$i][1], $arr[$i]);
            }
        }
    }

    public function printInCells($text, $x, $y, $pitch)
    {
        for ($i = 0; $i < strlen($text); $i++) {
            $this->Text($x+$i*$pitch, $y, $text[$i]);
        }
    }

    public function cross($x, $y, $d = 4.2)
    {
        $this->Line($x, $y, $x+$d, $y+$d, array('width' => 0.3));
        $this->Line($x, $y+$d, $x+$d, $y, array('width' => 0.3));
    }

    public function newPage()
    {
        $this->AddPage();
        $this->useTemplate($this->importPage($this->pagenum++));
    }

    public function printCenter($x, $y, $text, $border = 0, $width = 180, $height = 10)
    {         
        $this->WriteHtmlCell($width, $height, $x, $y, $text, $border, 0, false, true, 'C');
//$this->Text($x, $y, $text, false, false, true, 0, 0, 'C');
    }
}
