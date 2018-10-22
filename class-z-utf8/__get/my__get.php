<?php
	class A extends Test{
		
		function say(){
			echo "my name is obj A!";
		}
	}
	class B extends Test{
		
		function say(){
			echo "my name is obj B!";
		}
	}
	class C extends Test{
		function __construct(){
			echo "首次创建!";
		}
		function say(){
			echo "my name is obj C!";
		}
	}	
	class Test{
	
		function __get($name){
			$this->{$name}=new $name;
			return $this->{$name};
		}
	}
	
	$a=new Test();
	$a->C->say();
	$a->C->say();
?>