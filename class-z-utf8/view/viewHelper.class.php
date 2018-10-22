<?php
class ViewHelper{

	/******* 简单列表 输出函数  **********/
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


//	$data=array(array('title'=>'标题','contents'=>'内容'),array('title'=>'标题1','contents'=>'内容1'));

//	$title='title';
//	$contents='contents';


//	$text=myList('<li>','<h1>',$title,'</h1>','<span style="color:#ff0000">',$contents,'</span>','</li>',$data);

//	ob_get_clean();
//	echo $text;


	//判断 语句 输出函数
	function ifList($field,$value,$ifcStack,$samplecStack,$data){
		ob_start();  
		$line_i=0;
		foreach($data as $list){
			if(in_array($list[$field],$value)){
				$my_i=array_search($list[$field],$value);
				$cStack=$ifcStack[$my_i];
			}else
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

	function formList($field,$value,$ifcStack,$samplecStack,$data){
		ob_start();  
		$line_i=0;
		foreach($data as $list){
			if(in_array($list[$field],$value)){
				$my_i=array_search($list[$field],$value);
				$cStack=$ifcStack[$my_i];
			}else
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
	//	print_r($data);	

		$ifArray=$args[0];								//参数栈 用于存放 列表 标签 和 变量;
		$samplecStack=$args[1];
		
		$ifKeys=array_keys($ifArray);
		$ifValues=array_values($ifArray);
		
		$if_field=array_keys($ifValues[0]);
		$if_fieldStack=array_values($ifValues[0]);
		
	//	print_r($if_fieldStack);
		
		$if_fieldStack=$if_fieldStack[0];
		
	//	print_r($if_fieldStack);
		
		$if_field_value=array_keys($if_fieldStack);
		$if_stack=array_values($if_fieldStack);
		
	//	print_r($if_stack);
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
			case 'if': return self::ifList($if_field[0],$if_field_value,$if_stack,$samplecStack,$data);
			  break;
			case 'form': return self::formList($if_field[0],$if_field_value,$if_stack,$samplecStack,$data);
			default:
			  echo "No number between 1 and 3";
		}
		
	}
	
	
	function myForm($data,$field){
//		echo "ccccccccc";
//		print_r($data);			
		foreach($data as $myArr){
			switch($myArr[$field]){
				case 'select': echo $myArr['prompt'].":<select name='".$myArr['name']."'>".self::myList('<option value=\'','value','\'>','','name','</option>',$myArr['value'])."</select>";break;
				default:echo $myArr['prompt'].":<input type='".$myArr['type']."' name='".$myArr['name']."' value='".$myArr['value']."' />";
			}
		}
	}

}

//$data=array(array('title'=>'标题','contents'=>'内容','line_i'=>1),array('title'=>'标题1','contents'=>'内容1','line_i'=>2),array('title'=>'标题1','contents'=>'内容1','line_i'=>3));

//$title='title';
//$contents='contents';

/********** 带逻辑处理的 列表函数 目前 只能实现 if 语句 ************/

//$sample=array('<li>','<h1>',$title,'</h1>','<span style="color:#ff0000">',$contents,'</span>','</li>');

//$handle=array('if'=>array('line_i'=>array(1=>array('<li>','<h1>',$title,'</h1>','<span style="color:#ffff00">',$contents,'</span>','</li>'),2=>array('<li>','<h1>',$title,'</h1>','<span style="color:#ff00ff">',$contents,'</span>','</li>'))));


//$text=ViewHelper::myList_if($handle,$sample,$data);
// echo $text;
 
 
// $data=array(array('type'=>'text','name'=>'a1','value'=>'a1'),array('type'=>'password','name'=>'a2'),array('type'=>'select','name'=>'a3','value'=>array('a1','a2')));

/********** 带逻辑处理的 列表函数 目前 只能实现 if 语句 ************/

//$sample=array('<li>','<input type=\'','type','\'',' name=\'','name','\'',' value=\'','value','\' />','</li>');

//$handle=array('form'=>array('type'=>array('password'=>array('<li>','<input type=\'','password','\'',' name=\'','name','\' />','</li>'),'select'=>array('<li>','<select name=\'','name','\'></select>','</li>'))));

//$text=ViewHelper::myList_if($handle,$sample,$data);
// echo $text;




/*
	$viewHelper=new ViewHelper();
	$data=array(array('title'=>'3'),array('title'=>'4'));

	$title='title';


	$text=$viewHelper->myList('<li>','<h1>',$title,'</h1>','</li>',$data);

	echo $text;
*/
?>
