<?php
require_once('../../../modules/tcpdf/tcpdf.php');
require_once('../../../modules/fpdi/fpdi.php');
require_once('../../../modules/russian_date.php');

class PDF extends FPDI {
    function Header() {}
    function Footer() {}

    public function splitText($string, $coords, $size, $hang=1) {
        $arr = splitstring($string, $size, $hang); 
	for($i = 0; $i < sizeof($coords); $i++) {
	    if (isset($arr[$i])) $this->Text($coords[$i][0], $coords[$i][1], $arr[$i]);
	}
    }

    public function printInCells($text, $x, $y, $pitch) {
      	for ($i = 0; $i < strlen($text); $i++) $this->Text($x+$i*$pitch, $y, $text[$i]);
    }
}

?>