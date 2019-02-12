<?php

class Collect{
    private $_class=[];
    function load($_class, $data = []){
        if(!isset($this->_class[$_class])){
            $this->_class[$_class] = new $_class();
        }
        return $this->_class[$_class];
    }
}

interface Observe{
    function update(Observable $observervable);
}
class ObserveBaic implements Observe{
    final function update(Observable $observervable){
        $data = $observervable->getMessage();
        $action = $data['act'];
        $this->$action($data['data']);
    }
}
class Observe1 extends ObserveBaic{
    function say($data){
        echo __CLASS__.'收到通知!'.'data:'.var_export($data, true);
        echo '<br>下面执行处理逻辑!';
    }
}

interface IObservable{
    function notify($_act, $_data);
    function setMessage($data);
    function getMessage();
    function register($observeName, $observe);
    function autoRegister($data);
}
class Observable implements IObservable{
    private $_observes,$_message;
    function __construct(){
        $cfg = [
            'Observable1'=>['observe1'=>'Observe1']
            ];
        $this->autoRegister($cfg[get_class($this)]);
    }
    final function notify($_act, $_data){
        $this->setMessage(['act'=>$_act,'data'=>$_data]);
        foreach($this->_observes as $k=>$observe){
            //通知的时候进行实例化
            if(!is_object($observe)){
                $observe = new $observe();
                $this->_observes[$k] = $observe;
            }
            $observe->update($this);
        }
    }
    final function setMessage($data){
        $this->_message = $data;
    }
    final function getMessage(){
        return $this->_message;
    }
    final function register($observeName, $observe){
        if(!isset($this->_observes[$observeName])){
            $this->_observes[$observeName] = $observe;
        }
    }
    final function autoRegister($data){
        foreach($data as $k=>$v){
            $this->register($k, $v);
        }
    }
}

class Observable1 extends Observable{
    function say(){
    //    $this->autoRegister(['observe1'=>'Observe1']);
        $say = 'I am observable1!<br>';
        echo $say;
        $this->notify(__FUNCTION__,['say'=>$say]);
    }
}

$o = new Observable1();
$o->say();
//链式任务 迭代
$links = ['a::sayinfo','b::sayinfo','c::sayinfo','d::sayinfo'];
class say{
    function sayinfo($data=''){
        $info = empty($data) ? get_class($this):$data.','.get_class($this);
        echo static::class;
        echo $info.'<br>';
        return get_class($this);
    }
}
class a extends say{}
class b extends say{}
class c extends say{}
class d extends say{}

function run($links,$data=''){
    foreach($links as $item){
        $objInfo = explode('::',$item);
        //$objInfo[1] = explode(',',$objInfo[1]);
        $obj = new $objInfo[0]();
        $data = call_user_func_array([$obj,$objInfo[1]],[$data]);
    }
}
run($links);
//创建类文件
function createClassFile($class,$ext='.class.php',$basicDir='./'){
    $classArr = explode('\\',$class);
    $class = array_pop($classArr);
    $funcs = count(explode('#',$class))>1 ? explode('#',$class)[1] : '';
    $class = explode('#',$class)[0];
    $className = explode('|',$class)[0];
    $extends = '';
    if(count(explode('|',$class))>1){
        $extends = ' extends '.explode('|',$class)[1];
    }
    $namespace = implode('\\',$classArr);
    $dir = $basicDir.implode('/',$classArr);
    $funcs = createFuncStr($funcs);
    echo $funcs;
    $str = "<?php\r\nnamespace $namespace;\r\nclass A$extends{\r\n$funcs\r\n}";
    if(!is_dir($dir)){
        mkdir($dir,0,true);
    }
    file_put_contents($dir.'/'.$className.$ext,$str);
}
function createFuncStr($funcs){
    if(($funcs=trim($funcs,'[]'))!=''){
        $funcs = explode(',',$funcs);
        var_dump($funcs);
        $str = '';
        foreach($funcs as $func){
            $str.="function $func(){}\r\n";
        }
        return $str;
    }
    return '';
}
//createClassFile('ma\\mm\\b|f#[a,b,c]');//应用场景快捷生成骨架

//将类转换为静态类方法进行调用 应用场景简化代码 省去代码实例化

class Facade{
    protected static $_classes=[];
    public static function getIns($arguments){
        $classarr = explode('\\',static::class);
        $class = end($classarr);
        if (!isset(self::$_classes[$class])||!is_object(self::$_classes[$class])){
            $ins='Jar\Services\\'.$class.'Service';//定义路径
            self::$_classes[$class] = new $ins($arguments);
        }
        return self::$_classes[$class];
    }

    public static function __callStatic($name, $arguments){
        $class = self::getIns($arguments);
        return call_user_func_array(array($class,$name),$arguments);
    }
}



class myclass {
    // constructor
    function myclass()
    {
        return(true);
    }

    // method 1
    function myfunc1()
    {
        return(true);
    }

    // method 2
    function myfunc2(myclass $a, $b)
    {
        return(true);
    }
}

$class_methods = get_class_methods('myclass');
// or
$class_methods = get_class_methods(new myclass());

foreach ($class_methods as $method_name) {
    echo "$method_name\n";
}

echo '<br>';
$reflector = new ReflectionClass('myclass');

//Get the parameters of a method
$parameters = $reflector->getMethod('myfunc2')->getParameters();

//Loop through each parameter and get the type
foreach($parameters as $param)
{
     //Before you call getClass() that class must be defined!
    // echo $param;
    // echo gettype($param);
     echo '<br>1'; 
//    echo $param->getName();
//    var_dump($param->getClass());
    if($obj = $param->getClass()){
        echo $obj->name;
    }else{
        echo $param->getName();
    }
}