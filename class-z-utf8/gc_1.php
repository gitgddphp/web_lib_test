<?php
//测试 类的 子类调用  目的:自动执行

class foo {
	public function foo(){
		echo get_class($this)."-->类被创建！<br />"	;
		
	}
}
class founctroy_foo{
	
	public $foo_son=array("foo");
	public function founctroy_foo(){
		echo "这是 foo 的工厂类 用友创建 foo类的子类！<br />";	
	}
    static public function test() {
//        var_dump(get_called_class());
    }



	final function run(){//禁止子类对该方法重写
//		echo "-->".get_class($this)."<--";
		$mt=get_class($this);
		new $mt();
	}
	
	final function getClass($className){//禁止子类对该方法重写
		$i=0;
		$Myclass=get_declared_classes();
		while($i<count($Myclass)){//获取 foo 类 的所有子类 并创建执行
			if(is_subclass_of($Myclass[$i],$this->foo_son[0])){//判断 是否为foo 类的子类
		//		echo $Myclass[$i];
//				$m=new $Myclass[$i];
//				$m->run();
				array_push($this->foo_son,$Myclass[$i]);  //获取到所有 foo 类的 子类的数组
			}
			$i++;
		}
		if($num=array_search ( $className,$this->foo_son)){
			return new $this->foo_son[$num];
		}else{
			return false;	
		}

		
	}
}



$aa=new founctroy_foo();
$aa->getClass("bar2");
$aa->getClass("bar3");
$aa->getClass("bar");








class bar extends foo {
	/*
	function run(){
		echo "bbb";	
	}
	*/

	public function show(){
		
	}
	public function info(){
	
	}
	public function handller(){
		
	}
}
class bar2 extends foo {
	
}
class bar3 extends foo{
	
}



?>
