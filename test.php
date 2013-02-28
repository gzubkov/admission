<?php
 
class A {
 
        public function f() {
                print "А: Вызываем метод f()<br />";
        }
 
        public function g($q) {
                print "А: Вызываем метод g()<br />.$q";
        }
}
class B {
public $qwe = 5;
 
        public function f() {
                print "B: Вызываем метод f()<br />";
        }
 
        public function g($q, $w, $e) {
                print "B: Вызываем метод g()<br />";
		print_r($q);
		print "sss";
		print_r($w);
		print_r($e);
		
        }
}
 
class C {
 
        private $_a;
 
        public function __construct($q) {
	if ($q == 1) {
                $this->_a = new A;
		
} else {
$this->_a = new B;
}
        }
function __get($name) {
    return $this->_a->$name;
  } 

  public function __call($methodName, $args) {
return $q = call_user_func_array( 'B::'.$methodName, $args); 
  }
      
        public function y() {
                print "C: вызываем метод y()<br />";
        }
}
 
$obj = new C(2);

$obj->f();
$obj->g(2,3,4);
$obj->y();
 
print $obj->qwe;

/*abstract class AbstractComponent {
    abstract public function operation();
}
 
class ConcreteComponent extends AbstractComponent {
    public function operation() {
        print "concrete 1";
    }
}
 
abstract class AbstractDecorator extends AbstractComponent {
    public $_component;
 
    public function __construct(AbstractComponent $component) {
        $this->_component = $component;
    }
 
    public function operation() {
        $this->_component->operation();
    }
}
 
class ConcreteDecorator extends AbstractDecorator {
    public function __construct($q) {
        if ($q == 1) {
print "www";
        parent::_component = new ConcreteComponent();
	}
    }
 
    public function operation() {
        print "decorator"; // ... расширенная функциональность ...       
        parent::operation();       
        // ... расширенная функциональность ...
    }
}
 
$decoratedComponent = new ConcreteDecorator(1);
 
$decoratedComponent->operation();
*/

/*class Class0 {

    public function __construct($q) {
print $q;
        if ($q == 1) {
	    return new Class1();
	    //return &$w;
	}
	 //$w = new Class2();
//    return new Class2();    
    }
    
}

class Class1 extends Class0 {
    public $c = '123';

    public function getinit() {
        print "qqq".$c;
	return true;
    }
}

class Class2 extends Class0 {
    public $c = '7654';

    public function getinit() {
        print "qqq".$c;
	return true;
    }
}

$d = new Class0(1);
$d->getinit();

//$b = new Class0(2);
//$b->getinit();
*/



?>