<?php
/*
	装饰器
*/
	define("Decoration_Before",1);
	define("Decoration_Behind",2);
	define("Decoration_Between",0);
	
	class decoration{
		public $text,$decoration_type,$decoration_str,$decoration_dependent_str;//$decoration_str 为被装饰的 字符串   $decoration_dependent_str 装饰的内容（字符串或数组）
		//$decoration_type  为装饰方式(包围方式，之前，之后)  $text 为被装饰的数据源
		function __construct(){
			
		}
		function setDecoration_dependent_str($dependent_str){
			$this->decoration_dependent_str=$dependent_str;
		}
		function setDecoration_str($decoration_str){
			$this->decoration_str=$decoration_str;
		}
		function setData_source($text){
			$this->text=$text;
		}
		function setDecoration_type($decoration_type){
			$this->decoration_type=$decoration_type;
		}
		function run(){
			return str_replace($this->decoration_str,$this->decoration_dependent_str[0].$this->decoration_str.$this->decoration_dependent_str[1],$this->text);
		}
	}
	
//	$x=new decoration();
//	$x->setDecoration_dependent_str(array('<strong>','</strong>'));
//	$x->setDecoration_str('myname');
//	$x->setData_source('myname is xxx!myname!');
//    echo $x->run();
	

	
	class xDecoration{
		public $text,$decoration_type,$decoration_str,$decoration_dependent_str;//$decoration_str 为被装饰的 字符串   $decoration_dependent_str 装饰的内容（字符串或数组）
		//$decoration_type  为装饰方式(包围方式，之前，之后)  $text 为被装饰的数据源
		function __construct(){
			
		}
		
		function setDecoration_Config($arr){
			if(!$arr[0]){
				$this->decoration_dependent_str[]=$arr['decoration_dependent_str'];
				$this->decoration_str[]=$arr['decoration_str'];
				$this->decoration_type[]=$arr['decoration_type'];
			}else{
				foreach($arr as $xarr){
					$this->decoration_dependent_str[]=$xarr['decoration_dependent_str'];
					$this->decoration_str[]=$xarr['decoration_str'];
					$this->decoration_type[]=$xarr['decoration_type'];
				}
			}
		}
		
		function setData_source($text){
			$this->text=$text;
		}

		function run(){
		
			for($i=0;$i<count($this->decoration_dependent_str);$i++){
				switch($this->decoration_type[$i]){
					case 0:$this->text=str_replace($this->decoration_str[$i],$this->decoration_dependent_str[$i][0].$this->decoration_str[$i].$this->decoration_dependent_str[$i][1],$this->text);break;
					case 1:$this->text=str_replace($this->decoration_str[$i],$this->decoration_dependent_str[$i].$this->decoration_str[$i],$this->text);break;
					case 2:$this->text=str_replace($this->decoration_str[$i],$this->decoration_str[$i].$this->decoration_dependent_str[$i],$this->text);					
				}
			}
			return $this->text;
		}
	}
	/*
		array('aa'=>array('<strong>','</strong>'));
		array('aa'=>'cc');
		array('decoration_str'=>'aaa','decoration_dependent_str'=>array('<strong>','</strong>'),'decoration_type'=>Decoration_Between);
	*/
/*
	$mm1=array('decoration_str'=>'aaa','decoration_dependent_str'=>array('<strong>','</strong>'),'decoration_type'=>Decoration_Between);
	
	$mm2=array(array('decoration_str'=>'aaa','decoration_dependent_str'=>'II','decoration_type'=>Decoration_Before),array('decoration_str'=>'ccc','decoration_dependent_str'=>array('<strong>','</strong>'),'decoration_type'=>Decoration_Between));
	
	$x=new xDecoration();
	$x->setDecoration_Config($mm2);
	$x->setData_source('dfd aaa dfdf ccc dfsd');
    echo $x->run();
*/
?>
