<?php
//监听 类的方法

	class A{
	
		function addLis(&$obj){
			return $obj;
		}
	}
	class B extends A{
		function display(){
			echo "display!";
		}
		function say(){
			echo "say!";
		}
	
	}
	class ALisener extends A{
		private $obj,$fun;
		function __construct($obj){
			$this->setObj($obj);
		}
		function setObj($obj){
			if($obj instanceof ALisener){
				$this->obj=$obj->obj;
			}else{
				$this->obj=$obj;
			}
		}
		function brefore(){
			echo "brefore!";
		}
		function end(){
			echo "end!";
		}
		function __call($name, $arguments){
			foreach($this->fun as $fun){
				if($fun['fun']==$name)
					break;
			}
			if(in_array('brefore',$fun['set'])){
				$this->brefore();
			}
			$this->obj->$name();
			if(in_array('end',$fun['set'])){
				$this->end();
			}
		}
		function add($set){
			$this->fun[]=$set;
		}
	}
	$b=new B();
	$b=$b->addLis(new ALisener($b));
	$b->add(array('fun'=>'display','set'=>array('brefore','end')));
	$b->add(array('fun'=>'say','set'=>array('brefore','end')));
	$b->display();
	$b->say();
	if($b instanceof ALisener){
		echo "isALisener!";
	}
	

?>