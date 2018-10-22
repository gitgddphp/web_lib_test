<?php

/***
	适配器测试 参与类:  testAdapter 和 test
***/
	class testAdapter{
		public $test;
		public $config;
		function __construct($test,$config){
			$this->test=$test;
			$this->config=$config;
		}
		
		function test(){
			$methodName=$this->config[__FUNCTION__];
			$this->test->$methodName();
		}
		
		function run(){
			$methodName=$this->config[__FUNCTION__];
			$this->test->$methodName();
		}
	
	}
	
	class test{
	
		function aaa(){
			echo "aaaa!";
		}
		
		function runer(){
			echo "runner!";
		}
	}
	
	$a=new testAdapter(new test(),array('test'=>'aaa','run'=>'runer'));
	$a->test();

?>
