<?php

class FormViewHelper{
	
	function __construct(){
		
	}
	static function field($fieldConfig){
		$dir=dirname(__FILE__);
		ob_start();
		include $dir."\\fields\\".$fieldConfig['type'].".php";
		return ob_get_clean();
	}	
	
}

/*在界面 中创建字段
	field($data);//$data('fieldName','title','size','error'(错误),'correct'(正确),'prompt'(提示),'type'(字段类型),'style','validate');
*/
$field=array('fieldName'=>'fieldName','title'=>'title','size'=>'6','error'=>'error','correct'=>'correct','prompt'=>'prompt','type'=>'text','style'=>'','validate'=>'');
$a=FormViewHelper::field($field);
$a=FormViewHelper::field($field);
echo $a;
?>
