<?php
$lang_cn=array('myName'=>'名称','speach'=>'说话');
$lang_bn=array('myName'=>'name','speach'=>'speak');
$l_c='lang_cn';

function get_VarName(&$var, $scope=null){ 
    $scope = $scope==null? $GLOBALS : $scope; // 如果没有范围则在globals中找寻 
 
    // 因有可能有相同值的变量，因此先将当前变量的值保存到一个临时变量中。
    //然后，再对原变量赋唯一值，以便查找出变量的名称，找到名字后，将临时变量的值重新赋值到原变量 
    $tmp = $var; 
     
    $var = 'tmp_value_'.mt_rand(); 
    $name = array_search($var, $scope, true); // 根据值查找变量名称 
 
    $var = $tmp; 
    return $name; 
} 
 
$ab='myName';
$lang=$$l_c;
//print_r($$l_c);
//echo $lang[$ab];
//echo '$a'.get_variable_name($a);

class lang{
	var $language;
	function __construct($l){
		$this->language=$l;
	}
	function translate($str){
		return $this->language[$str];
	}
}

//$l=new lang($$l_c);
//echo $l->translate('myName');


class config{
	public static $config;
	function __construct(){
	
	}
	public static function getConfig($config=null){
		if(is_string($config))
			return require_once($config);
		return $config;
	}
	public static function setConfig($key,$value=''){
	
	
	}
}


class webLabel{//web 标签
	

}
class templets{

}

class IO{


}

$templets_config=array('<div>'=>'file');

class webLabelRendTool{
	public static $webLabelRendConfig=array('div');
	public static function list_rend($label,$dataList,$css=null){
		require($label);
	}
	public static function menus_rend(){
	
	}
	public static function rend(){
	
	
	}
}


function div($a){
	if(true){
		echo '<div>';
		echo $a;
		echo '<div>';
	}
}

$a='div';
$a($a);
//eval($a.'();');

//$a=array('aa','bb');

function table(){

}


function list_a($aa){
	$args=func_get_args();
	print_r($args);
}
list_a(array('aa','bb'),'cc','dd');


function myList(){
	$args=func_get_args();
	$args_num=count($args);
	$data=$args[$args_num-1];	
//	print_r($data);	
	$cStack=array();								//参数栈 用于存放 列表 标签 和 变量;
	for($i=0;$i<$args_num-1;$i++){
		array_push($cStack,$args[$i]);
	}
//	print_r($cStack);
	$temCStack=$cStack;
	
	ob_start();  
	foreach($data as $list){
		$keys=array_keys ($list);
		for($i=0;$i<$args_num-2;$i++){
			if(in_array($cStack[$i],$keys,true)){
				echo $list[$cStack[$i]];
			}else
				echo $cStack[$i];
		}
	
	}
	return ob_get_clean();
}


$data=array(array('title'=>'标题','contents'=>'内容'),array('title'=>'标题1','contents'=>'内容1'));

$title='title';
$contents='contents';

/******* 简单列表 输出函数  **********/
$text=myList('<li>','<h1>',$title,'</h1>','<span style="color:#ff0000">',$contents,'</span>','</li>',$data);

ob_get_clean();
echo $text;


function ifList($field,$value,$ifcStack,$samplecStack,$data){
	ob_start();  
	$line_i=0;
	foreach($data as $list){
		if($list[$field]==$value)
			$cStack=$ifcStack;
		else
			$cStack=$samplecStack;
			
		$keys=array_keys ($list);
		for($i=0;$i<count($cStack);$i++){
			if(in_array($cStack[$i],$keys,true)){
				echo $list[$cStack[$i]];
			}else
				echo $cStack[$i];
		}
		$line_i++;
	}
	return ob_get_clean();
}

function myList_if(){
	$args=func_get_args();
//	$args_num=count($args);
	$data=$args[2];	
	print_r($data);	

	$ifArray=$args[0];								//参数栈 用于存放 列表 标签 和 变量;
	$samplecStack=$args[1];
	
	$ifKeys=array_keys($ifArray);
	$ifValues=array_values($ifArray);
	
	$if_field=array_keys($ifValues[0]);
	$if_fieldStack=array_values($ifValues[0]);
	
	$if_fieldStack=$if_fieldStack[0];
	
	$if_field_value=$if_fieldStack[0];
	$if_stack=$if_fieldStack[1];
	
//	echo $ifKeys[0];
/*
	echo '<br>';
	echo '<br>';
	print_r($if_field[0]);echo "<br><br>";
	print_r($if_field_value);echo "<br><br>";
	print_r($if_stack);echo "<br><br>";
	echo "<br><br>";
	print_r($samplecStack);echo "<br><br>";
*/
	
	switch($ifKeys[0]){
		case 'if': return ifList($if_field[0],$if_field_value,$if_stack,$samplecStack,$data);
		  break;
		default:
		  echo "No number between 1 and 3";
	}
	
}

$data=array(array('title'=>'标题','contents'=>'内容','line_i'=>1),array('title'=>'标题1','contents'=>'内容1','line_i'=>2));

$title='title';
$contents='contents';

/********** 带逻辑处理的 列表函数 目前 只能实现 if 语句 ************/

$sample=array('<li>','<h1>',$title,'</h1>','<span style="color:#ff0000">',$contents,'</span>','</li>');

$handle=array('if'=>array('line_i'=>array(1,array('<li>','<h1>',$title,'</h1>','<span style="color:#ffff00">',$contents,'</span>','</li>'))));

$text=myList_if($handle,$sample,$data);
 echo $text;






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
}
$pro=new property();

/*
if($pro->addProperty('myName','gdd'))
	echo $pro->myName.'<br>';
else
	echo '属性已经存在!<br>';

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
*/	
class Mtest{
	public $a='';
	function __construct($a){
		$this->a=$a;
		echo "<br> create class Mtest Object!<br>";
	}
	function display(){
		echo "<br>".$this->a;
	}
	
	function __clone(){
		echo "<br>克隆了".__CLASS__."类:";
		print_r(get_class_methods($this));
		print_r($this);
	}
}


//$x1=ObjectManage::getClass('Mtest','BBBaa');	
//$x1->display();

//$x2=ObjectManage::getClass('Mtest','CCCaa');	
//$x2->display();

//$x3=ObjectManage::getClass('Mtest','BBBaa');	
//$x3->display();


/********** 实例管理器 用于 存放 系统创建的 类  列表 ************/

class ObjectManage{
	public static $ClassList='';         //   ['class1']['class']  ['class2']['class']  
	public function __construct(){
	
	
	}
	
	public static function getClass($className,$config=''){
		
		if(self::$ClassList!=''){
		
			$classNames=array_keys(self::$ClassList);
//			print_r($classNames);
			if(in_array($className,$classNames)){
				$myClass=self::hasConfigClass(self::$ClassList[$className],$config);
				return clone($myClass!=false ? $myClass:self::addConfigClass($className,$config));
			}else{
				return clone self::addConfigClass($className,$config);
			}
		}else
			return clone self::addConfigClass($className,$config);
	}
	
	public static function addConfigClass($className,$config=''){//添加一个 带 参数的 对象
		$myClass=new $className($config);
		if(isset($myClass)){
			$classArray=array('object'=>$myClass,'config'=>$config);
			self::$ClassList[$className][]=$classArray;
			return $myClass;
		}
		return false;
	}
	public static function hasConfigClass($classMap,$config=''){
		$i=0;
		foreach($classMap as $my){
			if($my['config']==$config)
				return $my['object'];
			$i++;
		}
		return false;	
	}
}

/***************     行为 类 （系统预定义行为接口）  和   用户自定义行为接口     **********
系统开始     标签位        
用户参数接收 标签位
视图解析标签     位
********************************************************************************************/
/*******  标签位植入 *

    实现标签下面挂载 执行函数;
    使用环境，当我们要在一个方法中进行切面编程时,在不通过修改该函数就能达到 
	插入切面的实现方式;
	用法:需要一个标签配置文件,形同:array('onBegin'=>array('abmy','abmy1')); 包含了 标签 和要挂载上去的函数;
    在使用时需要预先将标签 存放到 要实现切面编程的 函数中;
	并且在函数执行前创建好 标签调用类的实例;并将标签配置参数 加载好;


*/
class  MyLabelsManage{
	public $labels=array('onBegin'=>array('abmy','abmy1'));  
	function __construct($config=''){
		if($config!='') $this->labels=$config;
	}
	function getMyLabFun($label){
		if(is_array($this->labels[$label]))
			foreach($this->labels[$label] as $function)
				$function();
		else
			$this->labels[$label]();
	}
}
function abmy(){
	echo "<br>absdm!";
}
function abmy1(){
	echo "<br>absdm!111";
}


//ObjectManage::getClass('MyLabelsManage')->getMyLabFun('onBegin');

/*
class myFun2{
	function mn(){
		ObjectManage::getClass(myFun)->getMyLabFun(get_class($this),'begin');
		
		
		
		
		ObjectManage::getClass(myFun)->getMyLabFun(get_class($this),'end');
	}


}
*/



	
//$xn=new property();
//echo $xn->aa;
//$xn->aaa();
//echo $xn->aaa; 

/***************************
list('<div>',$title,'</div>','<div>',$contents,'</div>',$data);
<div>$title</div>
<div>$contents</div>
<div>$title</div>
<div>$contents</div>

<ul>

list('<li>',<span>,$title,'</span>','<div>',$contents,'</div>','</li>',$data);
  args[x] 


  
</ul>



***************************/




?>
