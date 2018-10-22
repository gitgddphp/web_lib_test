<?php
/********  参数设置类 *******/

class property{
	
   function __construct(){
   }
   function addProperty($name,$value){
		if(isset($this->$name))
			return false;
		else
			$this->$name=$value;
		return true;
		
   }
   function setProperty($name,$value){
		if(isset($this->$name))
			$this->$name=$value;
		else
			return false;
		return true;
   
   }
   function deletProperty($name){
		if(isset($this->$name))
			unset($this->$name);
		else
			return false;
		return true;
   }
   function setConfig($dataSource,$type){//属性文件设置工具  $dataSource为配置 数据（可以是 数组 可以 是文件路径）    $type 为 配置文件格式(包括 xml  php )
		
		
   
   }
   
   function createConfig($dataSource,$type){//setConfig（获取配置信息）  相反 创建配置信息 
   
   
   
   }
   
}

$pro=new property();


if($pro->addProperty('myName','gdd'))
	echo $pro->myName.'<br>';
else
	echo '属性已经存在!<br>';
echo $pro->myName;
if($pro->addProperty('myName','gdd'))
	echo $pro->myName;
else
	echo '属性已经存在!设置属性失败!<br>';

if($pro->deletProperty('myName'))
	echo '属性删除成功!<br>';
else
	echo '属性不存在!无法删除!<br>';
if($pro->deletProperty('myName'))
	echo '属性删除成功!<br>';
else
	echo '属性不存在!无法删除!<br>';
/*
$newfunc = create_function('$a,$b','$c=$a+$b; return $c;');
echo $newfunc;
$pro->addProperty('newfunc',$newfunc);
echo $pro->newfunc;
echo "New anonymous function: $newfunc\n\r";
//echo $pro->$pro->newfunc(2, M_E) . "\n";
/******** 继承测试 ******/

//一种新的 设计  子类 的创建 由父类 决定 （就像 基因一样  父类将基因遗传给 子类 决定子类 的创建）
/*
class aa1{
	final function __construct(){
		echo "<br>aaaa--".get_class($this);
//		call_user_func_array(array($foo, "bar"), array("three", "four"));
		$numargs = func_num_args();
		$args=func_get_args();
		echo "<br>arg_num:".$numargs;
		
		call_user_func_array(array($this,'init'),$args);
		
		echo "<br>ccccccccccccccccccccccccccc";
	}
	function init(){}
}

class aa2 extends aa1{
	function init($aa=null){
		echo "<br>aaa2!".$aa;
	}
}
new aa2("aac");

new aa1();
*/

?>