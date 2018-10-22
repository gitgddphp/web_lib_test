<?php
class  MyLabelsManage{
	public static $labels;  
	public static function getMyLabFun($label,$args=null){
		if(isset(self::$labels[$label])){
			if(is_array(self::$labels[$label])){
				foreach(self::$labels[$label] as $function){
					if(function_exists($function)){ call_user_func_array($function,$args);}
					else{echo "<p style=\"color:ff0000; background:#eee\"><font style=\"font-weight:bold;\">error:</font>has not found function $function!<p>";}
				}
			}else
				if(function_exists(self::$labels[$label])) call_user_func_array(self::$labels[$label],$args);
				else{echo "<p style=\"color:ff0000; background:#eee\"><font style=\"font-weight:bold;\">error:</font>has not found function $function!<p>";}
		}else{
			echo "<p style=\"color:#ff0000; background:#eee\"><font style=\"font-weight:bold;\">error:</font>has not found tag $label!<p>";
		}
	}
}
function onBegin(){
	echo "<br>函数 执行开始!";
}
function onEnd(){
	echo "<br>函数执行结束!";
}



class aop_test{

	function __construct(){
		MyLabelsManage::getMyLabFun(__CLASS__.'>'.__FUNCTION__.'>onBegin');
		
		MyLabelsManage::getMyLabFun(__CLASS__.'>'.__FUNCTION__.'>onEnd');
	}
}
MyLabelsManage::$labels=array('aop_test>__construct>onBegin'=>'onBegin','aop_test>__construct>onEnd'=>'onEnd');//配置
//print_r(MyLabelsManage::$labels);

//MyLabelsManage::getMyLabFun('onBegin');
new aop_test;


/**********    使用php 自带的回调函数   **************/

function onBegina($message=null){
	echo "<br>$message a!";
}
function onEnda($message=null){
	echo "<br>$message a!";
}

class aop_test2{

	function __construct(){
		MyLabelsManage::getMyLabFun(__CLASS__.'>'.__FUNCTION__.'>onBegin','start');
		
		MyLabelsManage::getMyLabFun(__CLASS__.'>'.__FUNCTION__.'>onEnd','end');
	}
}

MyLabelsManage::$labels=array('aop_test2>__construct>onBegin'=>array('onBegina'),'aop_test2>__construct>onEnd'=>array('onEnda'));//配置
//print_r(MyLabelsManage::$labels);

//MyLabelsManage::getMyLabFun('onBegin');
new aop_test2;

// Call the foobar() function with 2 arguments


// Call the $foo->bar() method with 2 arguments
//$foo = new foo;
//call_user_func_array(array($foo, "bar"), array("three", "four"));

?>
