<?php

	include_once('../view/viewHelper.class.php');
	class render{
		public $write_file,$render;
		function __construct($rend_file){
			$this->render=new renderfile($rend_file);
			$this->render->rend_file=$rend_file;
		}
		function assign($name,$value){
			$this->render->$name=$value;
		}
		function assignArray($fields_array,$values_array){
			//变量批量注册
		}
		function setWriteFile($write_file){
			$this->write_file=$write_file;
		}
		function setRendFile($rend_file){
			$this->render->rend_file=$rend_file;
		}
		function rendfile(){
			return $this->render->rendfile();
		}
		function writeFile(){
			if(!file_exists($this->write_file)){
				$fp = fopen($this->write_file, 'w');
				if ( !is_writable($this->write_file) ){
					die("文件:" .$this->write_file. "不可写，请检查！");
				}
				fclose($fp);
			}
			file_put_contents($this->write_file,$this->rendfile());
		}
	}
	class renderfile{
		public $rend_file;
		function __construct($rend_file){
			$this->rend_file=$rend_file;
		}
		function assign($name,$value){
			$this->$name=$value;
		}
		function rendfile(){
			ob_start();
			require($this->rend_file);
			return ob_get_clean();
		}
	}

	$x=new render("temp_test.php");
	$x->assign("name","my name is xxxx!");
//	$x->setWriteFile("c_test.html");
//	echo $x->rendfile();
//	$x->writeFile();
		function writeFile(){
			$write_file='a.html';
			if(!file_exists($write_file)){
				$fp = fopen($write_file, 'w');
				if ( !is_writable($write_file) ){
					die("文件:" .$write_file. "不可写，请检查！");
				}
				fclose($fp);
			}
			ob_start();
			var_export (array('aa'=>'dd','aa'=>'dd'));
			$a=ob_get_clean();
			file_put_contents($write_file, $a);
		}
		writeFile();

?>
