<?php

//调用模型数据 表名 字段  页数  每页数据条数
function getTableData($table, $fields, $page, $everyPageNum){

}
//模型设置 字段可见性(游客  管理员  会员) 是否开启日志记录
function setTableCfg($table, $fields, $open_log=0){

}
//模块设置 模块功能结构 [插件[参数配置] ] 插件之间的关联性
function setModuleCfg(){

}
//所有功能插件 使用插件需要提供的参数 使用者(模块名[可能用于回调]) 
function usePlug(){

}
//获取 插件 所需参数[参数名[中文名] 表[关联字段]]和描述
function getPlugUseArgs(){

}
//表字段控制测试
class TableController{
}

//组装测试
class a{
    public $plugs;
    public $methods;
    function useClass($class){
        $this->plugs[$class] = new $class();
        $this->plugs[$class]->setUser($this);
        $this->regMethods($class);
    }
    function regMethods($class){
        $methods = get_class_methods($class);
        foreach($methods as $method){
            $this->methods[$class.$method] = [$class,$method];
        }
    //    var_dump($this->methods);
    }
    function regClassByArr(Array $classArr){
        foreach($classArr as $class){
            $this->useClass($class);
        }
    }
    function getFunc($obj,$func,$args=null){
        if(method_exists($this->plugs[$obj] , $func)){
            if($args!==null){
                call_user_func_array([$this->plugs[$obj] , $func],$args);
            }else{
                call_user_func([$this->plugs[$obj] , $func]);
            }
        }
    }
    function __call($name, $arguments){
        if( isset($this->methods[$name]) ){
            $obj    = $this->methods[$name][0];
            $func   = $this->methods[$name][1];
            call_user_func_array([$this->plugs[$obj] , $func],$arguments);
        }
    }
}
class plug{
    public $users;
    public $user;
    public $plugs=[];
    function setUser($obj){
        $this->user = $this->users[get_class($obj)] = $obj;
    }
    function __call($name, $arguments){
        $methods    = $this->user->methods;
        $plugs      = $this->user->plugs;
        if( isset($methods[$name]) ){
            $obj    = $methods[$name][0];
            $func   = $methods[$name][1];
            call_user_func_array([$plugs[$obj] , $func],$arguments);
        }
    }
    function getFuncInfo($name){
        return [];
    }
}
class b extends plug{
    public $plugs=['user'];
    function setUser($obj){
        $this->user = $this->users[get_class($obj)] = $obj;
    }
    function say(){
        echo '调用b::say!<br>';
        $this->usersay();
    }
    function getFuncInfo($name){
        $funcs = [
            'say'=>['des'=>'方法说', 'arg'=>'传入参数:$info'],
        ];
        return $funcs[$name];
    }
}
class user extends plug{
    function getB(){
        var_dump( array_keys($this->user->plugs));
    }
    function say(){
        echo '调用user::say!<br>';
    }
}
$a = new a();
$a->useClass('b');
$a->useClass('user');
$a->bsay();

class d{
    function a(){

    }
    public static function b(){

    }
}
//var_dump(get_class_methods('d'));
