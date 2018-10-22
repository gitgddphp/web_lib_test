<?php
 include_once("../render/render.class.php");
 class form{
	//用于创建 表单  和 处理表单 提交信息  包括数据 验证 和表单 样式包装
	public $InputArray;
	function __construct(){
	
	}
	
	function setValidate(){
	
	}
	
	function check(){
	
	}
	
	function setActionUrl(){
	
	}
	
	function setActionMethod(){
	
	}
	
	function setformId(){
		//查找  session  中 是否存在 id  存在就不写入 session
		
	}
	
	function addInput($type,$name,$id,$prompt,$value){
		$this->InputArray[]=array('type'=>$type,'name'=>$name,'id'=>$id,'prompt'=>$prompt,'value'=>$value);
	}
	
	function addInputs(){
	
	}
	
	function getData(){
		//服务器 接收 数据  读取表单Id  进行 对应表单 数据验证  数据过滤  最后返回处理后的数据
		//和 验证结果
	
	}
	
	function display(){
		$x=new render("temp_test.php");
		$x->assign("title","cccccc!");
		$x->assign("data",$this->InputArray);
//		$x->setWriteFile("c_test.html");
		echo $x->rendfile();
	}
	
	function useJs(){
	
	}
	
	function useCss(){
	
	}
	
 }
 
 $x=new form();
 $x->addInput('text','aa','aa','name','value');      //参数 描述： addInput(表单输入类型,名称（获取 调用时的名称）,id,提示信息（表单前的提示信息）,数据（可以是数组 或者 单个值 随表单类型 而定）)
 $x->addInput('select','aa','aa','select',array(array('name'=>'选择项一','value'=>'xc'),array('name'=>'选择项二','value'=>'xc')));
 $x->display();
 //print_r($x->InputArray);
 
?>
