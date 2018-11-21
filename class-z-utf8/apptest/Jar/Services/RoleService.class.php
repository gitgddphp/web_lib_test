<?php
namespace Jar\Services;

class RoleService {
    private static $fields=['role_id'=>0,'user_id'=>1,'token'=>null];//角色id,user_id,token
    function __construct($data){
        static::$fields = $data;
    }
    function init($data){
        static::$fields = $data;
    }
    function __call($func, $args){
        $methods = get_class_methods(self);
        if(in_array($func,$methods)){
            return $this->$func();
        }
        $fields_keys = array_keys(static::$fields);
        foreach($fields_keys as $key){
            if($func=='get'.$key && count($args)==0){
                return $this->$key;
            }
            if($func=='set'.$key && count($args)==1){
                return $this->$key=$args[0];
            }
        }
    }
    public function isManager(){
        if ($this->role_id==1){
            return true;
        }else{
            return false;
        }
    }

    public function getAll(){
        return self::$fields;
    }
    function __get($name){
        if(self::$fields[$name])
            return self::$fields[$name];
        else
            return false;
    }
    function __set($name, $value){
        self::$fields[$name] = $value;
    }
}