<?php
namespace Jar\Model;

use Think\Model;
use Evenement\EventEmitter;

class BaseModel extends Model{
    protected $_emitor;
    public function __construct(){
        $this->_emitor = new EventEmitter();
        parent::__construct();
        $this->_onEvent();
    }

    protected function _onEvent(){
        $classarr =  explode('\\',get_class($this));
        $class = substr(end($classarr),0,-5);
        $observer = 'Jar\Observers\\'.$class.'Observer';
        if (class_exists($observer)){
            foreach (get_class_methods($observer) as $method){
                $this->_emitor->on('_'.strtolower($class.$method),array($observer,$method));
            }
        }
    }

    public function emit($event,Array $param){
        $this->_emitor->emit('_'.strtolower($event),array($param));
    }
}
?>