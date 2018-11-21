<?php
namespace Jar\Facades;

class Facade{
    protected static $_classes=[];
    public static function getIns($arguments){
        if (!isset(self::$_classes[$class])||!is_object(self::$_classes[$class])){
            $ins=static::class;
            self::$_classes[$class] = new $ins($arguments);
        }
        return self::$_classes[$class];
    }
    public static function __callStatic($name, $arguments){
        $class = self::getIns($arguments);
        return call_user_func_array(array($class,$name),$arguments);
    }

    public function __get($name)
    {
        $class = self::getIns();
        return $class->$name;
    }
    public function say(){
        
    }
}
?>