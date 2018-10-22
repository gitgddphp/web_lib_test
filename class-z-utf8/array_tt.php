<?php
//测试 子类调用  目的:自动执行

//  require 'aa.php' ;
//  class m{}
class foo {
    static public function test() {
//        var_dump(get_called_class());
    }
	final function run(){//禁止子类对该方法重写
		echo "aaaa";	
	}
	final function getClass($className){//禁止子类对该方法重写
		echo "aaaa";	
	}
}

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
class bar3 extends bar{
	
}



$Myclass=get_declared_classes();   // 获取当前加载的 所有类 的数组
//print_r($Myclass);
$i=0;
//echo $Myclass[$i];



$foo_son=array();


while($i<count($Myclass)){//获取 foo 类 的所有子类 并创建执行
	if(is_subclass_of($Myclass[$i],'foo')){//判断 是否为foo 类的子类
		echo $Myclass[$i];
		$m=new $Myclass[$i];
		$m->run();
		array_push($foo_son,$Myclass[$i]);  //获取到所有 foo 类的 子类的数组
	}
	$i++;
}

print_r($foo_son);


echo $i;
echo is_subclass_of ( 'foo' , 'bar' )==true ? 0:1;


//echo get_class(new bar);

?>
