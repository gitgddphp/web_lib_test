<?php
/**
 * tpshop
 * ============================================================================
 * * 版权所有 2015-2027 深圳搜豹网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tp-shop.cn
 * ----------------------------------------------------------------------------
 * Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
 * ============================================================================
 * $Author: IT宇宙人 2015-08-10 $
 * error_no  定义错误id  用于查询错误类型
 */ 
namespace Jar\Controller;
use Think\Controller;
use Jar\Facades\Role;
class BaseController extends Controller {
    public $http_url;
    public $user = array();
    public $user_id = 0;
    public $token = '';
    public $db_prefix = '';
    public $is_test_user = 0;
    public $openNewLogic;
    /**
     * 析构函数
     */
    function __construct() {
        parent::__construct();
        $this->openNewLogic = C('openNewLogic');
        $this->db_prefix = C('DB_PREFIX');
        $this->mergeRequest();
        $this->checkToken(); // 检查token
        return ; 
        $unique_id = I("unique_id"); // 唯一id  类似于 pc 端的session id
        define('SESSION_ID',$unique_id); //将当前的session_id保存为常量，供其它方法调用                

   }    
    
    /*
     * 初始化操作
     */
    public function _initialize() {
                
        return ;
        $local_sign = $this->getSign();
        $api_secret_key = C('API_SECRET_KEY');
        
         if('www.tp-shop.cn' == $api_secret_key)
                exit(json_encode(array('status'=>-1,'msg'=>'请到后台修改php文件 Application/Api/Conf/config.php 文件内的秘钥','data'=>'' )));
            
        // 不参与签名验证的方法
        if(!in_array(strtolower(ACTION_NAME), array('getservertime','getconfig','alipaynotify','goodslist','search','goodsthumimages','login')))
        {        
            if($local_sign != $_POST['sign'])
            {    
                $json_arr = array('status'=>-1,'msg'=>'签名失败!!!','result'=>'' );
                 exit(json_encode($json_arr));

            }
            if(time() - $_POST['time'] > 600)
            {    
                $json_arr = array('status'=>-1,'msg'=>'请求超时!!!','result'=>'' );
                 exit(json_encode($json_arr));
            }
        } 

       
    }
    
    /**
     *  app 端万能接口 传递 sql 语句 sql 错误 或者查询 错误 result 都为 false 否则 返回 查询结果 或者影响行数
     */
    public function sqlApi()
    {            
        exit(json_encode(array('status'=>-1,'msg'=>'使用万能接口必须开启签名验证才安全','result'=>''))); //  开启后注释掉这行代码即可
        
        C('SHOW_ERROR_MSG',1);
            $Model = new \Think\Model(); // 实例化一个model对象 没有对应任何数据表
            $sql = $_REQUEST['sql'];                        
            try
            {
                 if(preg_match("/insert|update|delete/i", $sql))            
                     $result = $Model->execute($sql);
                 else             
                     $result = $Model->query($sql);
             }
             catch (\Exception $e)
             {
                 $json_arr = array('status'=>-1,'msg'=>'系统错误','result'=>'');
                 $json_str = json_encode($json_arr);            
                 exit($json_str);            
             }            
                         
            if($result === false) // 数据非法或者sql语句错误            
                $json_arr = array('status'=>-1,'msg'=>'系统错误','result'=>'');
            else
                $json_arr = array('status'=>1,'msg'=>'成功!','result'=>$result);
                                   
            $json_str = json_encode($json_arr);            
            exit($json_str);            
    }

    /**
     * 获取全部地址信息
     */
    public function allAddress(){
        $data =  M('region')->where('level < 4')->select();
        $json_arr = array('status'=>1,'msg'=>'成功!','result'=>$data);
        $json_str = json_encode($json_arr);
        exit($json_str);
    }

    /**
     * app端请求签名
     * @return type
     */
    protected function getSign(){
        header("Content-type:text/html;charset=utf-8");
        $data = $_POST;        
        unset($data['time']);    // 删除这两个参数再来进行排序     
        unset($data['sign']);    // 删除这两个参数再来进行排序
        ksort($data);
        $str = implode('', $data);        
        $str = $str.$_POST['time'].C('API_SECRET_KEY');        
        return md5($str);
    }
        
    /**
     * 获取服务器时间
     */
    public function getServerTime()
    {
        $json_arr = array('status'=>1,'msg'=>'成功!','result'=>time());
        $json_str = json_encode($json_arr);
        exit($json_str);       
    }
    
    /**
     * 校验token
     */
    public function checkToken(){
        $this->token = $_REQUEST['token']; // token
        // $class_methods = get_class_methods(new \Api\Controller\UserController());        
        // 判断哪些控制器的 哪些方法不需要登录验证的
        $check_arr = array(           
            'user::sendappid',
            'user::validateopenid',
            'user::register',
            'user::getpriv1',
            'user::sendmsg',
            'index::createorder',
            'index::ajaxsetticketsinvalidbytimeout',
            'pay::wxcallback',
            'index::test'
            );

        $controller_name = strtolower(CONTROLLER_NAME);
        $action_name = strtolower(ACTION_NAME);
        if(!in_array($controller_name.'::'.$action_name,array_values($check_arr)))
        {
            if(empty($this->token)){
                exit(json_encode(array('status'=>-100,'msg'=>'必须传递token','result'=>'')));
            }
            $this->user = M('users')->where("token = '{$this->token}'")->find();
            if(empty($this->user)){
                exit(json_encode(array('status'=>-101,'msg'=>'token错误','result'=>'')));
            }
            @Role::init($this->user);
            $this->is_test_user = $this->user['is_test'];   //是否为测试商品
            // 登录超过72分钟 则为登录超时 需要重新登录.  //这个时间可以自己设置 可以设置为 20分钟
        //    if(time() - $this->user['last_login'] > 3600)   //3600
        //        exit(json_encode(array('status'=>-102,'msg'=>'登录超时,请重新登录!!!','result'=>(time() - $this->user['last_login']))));
            $this->user_id = $this->user['user_id'];
             // 更新最后一次操作时间 如果用户一直操作 则一直不超时
            M('users')->where("user_id = {$this->user_id}")->save(array('last_login'=>time()));
        }
    }

    function mergeRequest(){
        $_REQUEST = array_merge($_GET,$_REQUEST);
        $input = json_decode(file_get_contents("php://input"),true);
        if($input)
            $_REQUEST = array_merge($_REQUEST,$input);
        $filters    =   C('DEFAULT_FILTER');
        $filters    =   explode(',',$filters);
        if(is_array($filters)){
            foreach($filters as $filter){
                if(function_exists($filter)) {
                    $_REQUEST   =   is_array($_REQUEST) ? array_map_recursive($filter,$_REQUEST) : $filter($_REQUEST); // 参数过滤
                }else{
                    $_REQUEST   =   filter_var($_REQUEST,is_int($filter) ? $filter : filter_id($filter));
                    if(false === $data) {
                        return   isset($default) ? $default : null;
                    }
                }
            }
        }
        
    }

    //简单过滤处理
    function guolv($data){
        $filters    =   C('DEFAULT_FILTER');
        $filters    =   explode(',',$filters);
        if(is_array($filters)){
            foreach($filters as $filter){
                if(function_exists($filter)) {
                    $data   =   is_array($data) ? array_map_recursive($filter,$data) : $filter($data); // 参数过滤
                }else{
                    $data   =   filter_var($data,is_int($filter) ? $filter : filter_id($filter));
                    if(false === $data) {
                        return   isset($default) ? $default : null;
                    }
                }
            }
        }
        return $data;
    }
}