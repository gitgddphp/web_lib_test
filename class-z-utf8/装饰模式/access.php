<?php
	class A{//基础类
	
		
	}
	class B extends A{//A类物件 
	
	
	}
	
	class C extends A{//A 类物品 装饰器
		private $_B;
		function __construct(){
		
		}
		function __get($name){//装饰后具有之前的一些熟悉,在此处调用属性__被装饰后也可能增加一些属性或者覆盖
			
		
		}
		
		function __call($method){//装饰后仍然具有装饰前的功能 //装饰后可覆盖 之前的一些方法
		
		
		}
	}
	
	
?>