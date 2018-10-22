<?php
/** 
* STBLOG PluginManager Class 
* 
* ������Ƶ�ʵ�ֺ����� 
* 
* @package        STBLOG 
* @subpackage    Libraries 
* @category    Libraries 
* @author        Saturn 
*/  
class PluginManager  
{  
    /** 
     * ������ע��Ĳ�� 
     * 
     * @access private 
     * @var array 
     */  
    private $_listeners = array();  
     /** 
     * ���캯�� 
     *   
     * @access public 
     * @return void 
     */  
    public function __construct()  
    {  
        #����$plugin����������ǻ�ȡ�Ѿ����û�����Ĳ����Ϣ  
     #Ϊ��ʾ���㣬���Ǽٶ�$plugin�����ٰ���  
     #$plugin = array(  
        #    'name' => '�������',  
        #    'directory'=>'�����װĿ¼'  
        #);  
        $plugins = get_active_plugins();#�������������ʵ��  
        if($plugins)  
        {  
            foreach($plugins as $plugin)  
            {//�ٶ�ÿ������ļ����а���һ��actions.php�ļ������ǲ���ľ���ʵ��  
                if (@file_exists(STPATH .'plugins/'.$plugin['directory'].'/actions.php'))  
                {  
                    include_once(STPATH .'plugins/'.$plugin['directory'].'/actions.php');  
                    $class = $plugin['name'].'_actions';  
                    if (class_exists($class))    
                    {  
                        //��ʼ�����в��  
                        new $class($this);  
                    }  
                }  
            }  
        }  
        #�˴���Щ��־��¼����Ķ���  
    }  
       
    /** 
     * ע����Ҫ�����Ĳ�����������ӣ� 
     * 
     * @param string $hook 
     * @param object $reference 
     * @param string $method 
     */  
    function register($hook, &$reference, $method)  
    {  
        //��ȡ���Ҫʵ�ֵķ���  
        $key = get_class($reference).'->'.$method;  
        //�������������ͬ����push������������  
        $this->_listeners[$hook][$key] = array(&$reference, $method);  
        #�˴���Щ��־��¼����Ķ���  
    }  
    /** 
     * ����һ������ 
     * 
     * @param string $hook ���ӵ����� 
     * @param mixed $data ���ӵ���� 
     *    @return mixed 
     */  
    function trigger($hook, $data='')  
    {  
        $result = '';  
        //�鿴Ҫʵ�ֵĹ��ӣ��Ƿ��ڼ�������֮��  
        if (isset($this->_listeners[$hook]) && is_array($this->_listeners[$hook]) && count($this->_listeners[$hook]) > 0)  
        {  
            // ѭ�����ÿ�ʼ  
            foreach ($this->_listeners[$hook] as $listener)  
            {  
                // ȡ�������������úͷ���  
                $class =& $listener[0];  
                $method = $listener[1];  
                if(method_exists($class,$method))  
                {  
                    // ��̬���ò���ķ���  
                    $result .= $class->$method($data);  
                }  
            }  
        }  
        #�˴���Щ��־��¼����Ķ���  
        return $result;  
    }  
}  
/** 
* ����һ��Hello World�򵥲����ʵ�� 
* 
* @package        DEMO 
* @subpackage    DEMO 
* @category    Plugins 
* @author        Saturn 
*/  
/** 
*��Ҫע��ļ���Ĭ�Ϲ��� 
*    1. ���������ļ���������action 
*    2. ���������Ʊ�����{�����_actions} 
*/  
class DEMO_actions  
{  
    //���������Ĳ�����pluginManager������  
    function __construct(&$pluginManager)  
    {  
        //ע��������  
        //��һ�������ǹ��ӵ�����  
        //�ڶ���������pluginManager������  
        //�������ǲ����ִ�еķ���  
        $pluginManager->register('demo', $this, 'say_hello');  
    }  
       
    function say_hello()  
    {  
        echo 'Hello World';  
    }  
}  

//	$pluginManager=new PluginManager()
//	$pluginManager->trigger('demo','');
?>