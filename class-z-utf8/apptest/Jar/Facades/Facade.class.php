<?php
namespace Jar\Facades;

class Facade{
    protected static $_classes=[];
    public static function getIns($arguments){
        $classarr = explode('\\',static::class);
        $class = end($classarr);
        if (!isset(self::$_classes[$class])||!is_object(self::$_classes[$class])){
            $ins= self::__getNewClass('Jar\Services\\'.$class.'Service');
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
    public static function __getNewClass($classStr){
        $class_arr = explode('\\',$classStr);
        $class = array_pop($class_arr);
        $newClass = $classStr.'\\'.$class;
        if(class_exists($newClass)){
            return self::__getNewClass($newClass);
        }else{
            return $classStr;
        }
    }
}
?>