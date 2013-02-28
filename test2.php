<?php
interface Product 
{
    public function GetName();
}
class ProductA implements Product
{
    private $Name='ProductA';
    public $c = 2;
 
    public function GetName()
    {
        return $this->Name;
    }
}    
class ProductB implements Product
{
    private $Name='ProductB';
 public $c = 6;
 
    public function GetName()
    {
        return $this->Name;
    }
}

class Creator
{
    public function __construct($c, &$f)
    {
        $f = $this->_router($c);
    }

    private function _router($c)
    {
        if ($c == 1) return new ProductB();
	return new ProductA();
    }
}

new Creator(2, $type);

print $type->GetName();
print $type->c;

new Creator(1, $type);

print $type->GetName();
print $type->c;

?>