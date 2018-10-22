<?php
//配置接口
interface configure{

}
//基类
class Object implements configure{
	function __construct($config = []){
		$this->configure($this,$config);
	}
	function __get($name){
		$getter = 'get'.$name;
		return $this->$getter();
	}
    public function __set($name, $value)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            // set property
            $this->$setter($value);

            return;
        }
	}
	function configure($object,$propertes){
		foreach($propertes as $name=>$value){
			$object->$name = $value;
		}
	}	
}
class Event extends Object{
	public static function on(){
		
	}
}
//组件类
class Component{
	private $_behaviors = [];
	private $_events = [];
	function attachBehavior($name,$behavior){
		//注册行为
		return $this->attachBehaviorInternal($name,$behavior);
	}
	private function attachBehaviorInternal($name,$behavior){
		//行为依赖注入
		$behavior = new $behavior();
		$behavior->attach($this);
		$this->_behaviors[$name] = $behavior;
		return $behavior;
	}
	public function __call($name,$args){
		foreach($this->_behaviors as $behavior){
			if(method_exists($behavior,$name)){
				call_user_func_array(array($behavior,$name),$args);
			}
		}
	}
	public function trigger($name, Event $event = null){
		$event->handled = false;
		$event->name = $name;
		foreach($this->_events as $handler){
			$event->data = $handler[1];
			call_user_func($handler[0],$event);
		}
	}
}
//行为类
class Behavior{
	public $owner;
	private $behaviors = [];
	function __construct(){
		
	}
	function events(){
		return array_fill_keys(array_keys($this->behaviors),'evaluateBehaviors');
	}
	function evaluateBehaviors($event){
		$behaviors = (array) $this->behaviors[$event->name];
		$value = $this->getValue($event);
		foreach($behaviors as $behavior){
			$this->owner->$behavior = $value;
		}
	}
	function getValue($event){
		return $this->value;
	}
	function attach($owner){
		$this->owner = $owner;
		foreach($this->events() as $event=>$handler){
			$owner->on($event,$handler);
		}
	}
}
class TestBehavior extends Behavior{
	
	function f($a,$b,$c){
		echo $a.','.$b.','.$c;
	}
}

$a_component = new Component();
$a_component->attachBehavior('Test',new TestBehavior());
$a_component->f(1,2,3);
?>
