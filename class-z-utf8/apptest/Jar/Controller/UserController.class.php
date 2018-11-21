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
 */ 
namespace Jar\Controller;
use Think\Controller;
use Jar\Services\UsersLogic;
use Think\AjaxPage;
use Jar\Facades\Users;
use Jar\Facades\Role;
use Jar\Facades\JarTicket;
class UserController extends BaseController {
    public $userLogic;
    
    /**
     * 析构流函数
     */
    public function  __construct() {   
        parent::__construct();    
    
    } 
    
    public function _initialize(){
        parent::_initialize();
        $this->userLogic = new UsersLogic();
    }
    /**
     * 获取全部地址信息
     */
    public function getArea(){
        $data =  M('region')->where(array("parent_id"=>I('get.parent_id',0),"level"=>array("neq",4)))->select();
        $json_arr = array('status'=>1,'msg'=>'成功!','result'=>$data);
        $json_str = json_encode($json_arr);
        exit($json_str);
    }
    public function getAreaByName(){
        $info = I('get.pca','');
        if($info ==''||empty($info))
            $info = '四川省,成都市,成华区';
        $p_c_a = explode(',',$info);
        if(count($p_c_a)!=3)
            exit(json_encode(array('status'=>-1,'msg'=>'失败!','result'=>'')));
        $p_c_a[0] = M('region')->where('name like "'."%{$p_c_a[0]}%".'" and level=1')->find();
        $p_c_a[1] = M('region')->where('name like "'."%{$p_c_a[1]}%".'" and level=2')->find();
        $p_c_a[2] = M('region')->where('name like "'."%{$p_c_a[2]}%".'" and level=3')->find();

        $json_arr = array('status'=>1,'msg'=>'成功!','result'=>'');
        $json_arr['result'] = array('pca'=>$p_c_a);
        $json_str = json_encode($json_arr);
        exit($json_str);
    }
    /*
     * 第三方登录
     */
    public function thirdLogin(){
        $map['openid'] = I('openid','');
        $map['oauth'] = I('oauth','');
        $map['nickname'] = I('nickname','');
        $map['head_pic'] = I('head_pic','');        
        $data = $this->userLogic->thirdLogin($map);
        exit(json_encode($data));
    }

    /**
     * 用户注册
     */
    public function reg(){
        $username = I('post.username','');
        $password = I('post.password','');
        $password2 = I('post.password2','');
        $unique_id = I('unique_id');
        //是否开启注册验证码机制
        if(check_mobile($username) && TpCache('sms.regis_sms_enable')){
            $code = I('post.code');
            if(empty($code))
                exit(json_encode(array('status'=>-1,'msg'=>'请输入验证码','result'=>'')));                
            $check_code = $this->userLogic->sms_code_verify($username,$code,$unique_id);
            if($check_code['status'] != 1)
                exit(json_encode(array('status'=>-1,'msg'=>$check_code['msg'],'result'=>'')));
        }        
        $data = $this->userLogic->reg($username,$password,$password2);
        exit(json_encode($data));
    }

    /*
     * 获取用户信息
     */
    public function userInfo(){
        //$user_id = I('user_id');
        $data = $this->userLogic->get_info($this->user_id);
        exit(json_encode($data));
    }

    /*
     *更新用户信息
     */
    public function updateUserInfo(){
        if(IS_POST){
            //$user_id = I('user_id');
            if(!$this->user_id)
                exit(json_encode(array('status'=>-1,'msg'=>'缺少参数','result'=>'')));

            I('post.nickname') ? $post['nickname'] = I('post.nickname') : false; //昵称
            I('post.qq') ? $post['qq'] = I('post.qq') : false;  //QQ号码
            I('post.head_pic') ? $post['head_pic'] = I('post.head_pic') : false; //头像地址
            I('post.sex') ? $post['sex'] = I('post.sex') : false;  // 性别
            I('post.birthday') ? $post['birthday'] = strtotime(I('post.birthday')) : false;  // 生日
            I('post.province') ? $post['province'] = I('post.province') : false;  //省份
            I('post.city') ? $post['city'] = I('post.city') : false;  // 城市
            I('post.district') ? $post['district'] = I('post.district') : false;  //地区

            if(!$this->userLogic->update_info($this->user_id,$post))
                exit(json_encode(array('status'=>-1,'msg'=>'更新失败','result'=>'')));
            exit(json_encode(array('status'=>1,'msg'=>'更新成功','result'=>'')));
        }
    }

    /*
     * 修改用户密码
     */
    public function password(){
        if(IS_POST){
            //$user_id = I('user_id');
            if(!$this->user_id)
                exit(json_encode(array('status'=>-1,'msg'=>'缺少参数','result'=>'')));
            $data = $this->userLogic->password($this->user_id,I('post.old_password'),I('post.new_password'),I('post.confirm_password')); // 获取用户信息
            exit(json_encode($data));
        }
    }

    /**
     * 获取收货地址
     */
    public function getAddressList(){
    	/*"province": "338",
            "city": "569",
            "district": "586",*/
       $this->user_id = I('user_id');
       if(!$this->user_id)
            exit(json_encode(array('status'=>-1,'msg'=>'缺少参数','result'=>'')));
       $address = M('user_address')->where(array('user_id'=>$this->user_id))->select();
       foreach ($address as $key=>$value)
       {
       	$address[$key]['address'] = M('region')->where(array("id"=>$value['province']))->getField('name').M('region')->where(array("id"=>$value['city']))->getField('name').M('region')->where(array("id"=>$value['district']))->getField('name').$address[$key]['address'];
       }
            
        if(!$address)
            exit(json_encode(array('status'=>1,'msg'=>'没有数据','result'=>'')));
        exit(json_encode(array('status'=>1,'msg'=>'获取成功','result'=>$address)));
    }

    /*
     * 添加地址
     */
    public function addAddress(){

    	// $object = file_get_contents('php://input');
    	// $_POST = (json_decode($object, true));
        $this->user_id = Role::getuser_id();
        //echo $this->user_id.'1';
        if(!$this->user_id) exit(json_encode(array('status'=>-1,'msg'=>'缺少参数','result'=>'')));
        $address_id = I('address_id',0);
        $data = $this->userLogic->add_address($this->user_id,$address_id,I('post.')); // 获取用户信息
        exit(json_encode($data));
    }
    //获取address 地址  @address_id int; @user_id  int;
    public function getAddress(){
        //echo $address_id;
        // $object = file_get_contents('php://input');
        // $_POST = (json_decode($object, true));
        
        $address_id = $_POST['address_id'];
        $this->user_id =  Role::getuser_id();
        //echo $this->user_id.'1';
        if(!$this->user_id) exit(json_encode(array('status'=>-1,'msg'=>'缺少参数','result'=>'')));

        //echo $_POST['address'];
        $address = M('user_address')->where(array("address_id"=>$address_id,"user_id"=>$this->user_id))->find();
        $address['p_name'] = M('region')->where(array("id"=>$address['province']))->getField('name');
        $address['c_name'] = M('region')->where(array("id"=>$address['city']))->getField('name');
        $address['d_name'] = M('region')->where(array("id"=>$address['district']))->getField('name');
        exit(json_encode(array('status'=>1,'msg'=>'成功','result'=>$address)));
    }
    /*
     * 编辑地址
     */
    public function editAddress(){
    	//echo $address_id;
     //    $object = file_get_contents('php://input');
    	// $_POST = (json_decode($object, true));
    	
    	$address_id = $_POST['address_id'];
        $this->user_id =  Role::getuser_id();
        //echo $this->user_id.'1';
        if(!$this->user_id) exit(json_encode(array('status'=>-1,'msg'=>'缺少参数','result'=>'')));
        //检查手机格式
        if($_POST['consignee'] == '')
            exit(json_encode(array('status'=>-1,'msg'=>'收货人不能为空','result'=>'')));
        
        if(!$_POST['address'])
            exit(json_encode(array('status'=>-1,'msg'=>'地址不能为空','result'=>'')));
        if(!check_mobile($_POST['mobile']))
            exit(json_encode(array('status'=>-1,'msg'=>'手机号码格式有误','result'=>'')));
        //echo $address_id;
        
        //echo $_POST['address'];
        M('user_address')->where(array("address_id"=>$address_id))->save(array("address"=>$_POST['address'],"mobile"=>$_POST['mobile'],'zipcode'=>$_POST['zipcode'],'consignee'=>$_POST['consignee'],'province'=>$_POST['province'],'city'=>$_POST['city'],'district'=>$_POST['district']));
        exit(json_encode(array('status'=>1,'msg'=>'成功','result'=>'')));
    }
    
    
    /*
     * 地址删除
     */
    public function del_address(){

        $id = I('id');
    //    $this->user_id = I('user_id');
        if(!$this->user_id) exit(json_encode(array('status'=>-1,'msg'=>'缺少参数','result'=>'')));
        $address = M('user_address')->where("address_id = $id")->find();
        $row = M('user_address')->where(array('user_id'=>$this->user_id,'address_id'=>$id))->delete();                
        // 如果删除的是默认收货地址 则要把第一个地址设置为默认收货地址
        if($address['is_default'] == 1)
        {
            $address = M('user_address')->where("user_id = {$this->user_id}")->find();
            if($address)           
                M('user_address')->where("address_id = {$address['address_id']}")->save(array('is_default'=>1));
        }        
        if($row)
           exit(json_encode(array('status'=>1,'msg'=>'删除成功','result'=>''))); 
        else
           exit(json_encode(array('status'=>1,'msg'=>'删除失败','result'=>''))); 
    } 
    
    /*
     * 
     */
    public function get_address(){
    	$id = I('id');
    	$this->user_id =  Role::getuser_id();
    	if(!$this->user_id) exit(json_encode(array('status'=>-1,'msg'=>'缺少参数','result'=>'')));
    	$address = M('user_address')->where("address_id = $id")->find();
    	$address['cityvalue'] = $address['city'];
    	$address['city'] = M('region')->where(array("id"=>$address['province']))->getField('name').M('region')->where(array("id"=>$address['city']))->getField('name').M('region')->where(array("id"=>$address['district']))->getField('name');
  
    	exit(json_encode(array('status'=>1,'msg'=>'删除失败','result'=>$address)));
    }
    
    
    /*
     * 设置默认收货地址
     */
    public function setDefaultAddress(){
//        $user_id = I('user_id',0);
    	$this->user_id = I('user_id',0);
        if(!$this->user_id) exit(json_encode(array('status'=>-1,'msg'=>'缺少参数','result'=>'')));
        $address_id = I('address_id',0);
        $data = $this->userLogic->set_default($this->user_id,$address_id); // 获取用户信息
        if(!$data)
            exit(json_encode(array('status'=>-1,'msg'=>'操作失败','result'=>'')));
        exit(json_encode(array('status'=>1,'msg'=>'操作成功','result'=>'')));
    }

    /*
     * 获取优惠券列表
     */
    public function getCouponList(){

        
            // 会员注册送优惠券
            // $coupon = M('coupon')->where("send_end_time > ".time()." and ((createnum - send_num) > 0 or createnum = 0) and type = 1")->select();
            // foreach ($coupon as $key => $val)
            // {
            //     // 送券            
            //     M('coupon_list')->add(array('cid'=>$val['id'],'type'=>$val['type'],'uid'=>$this->user_id,'send_time'=>time()));
            //     M('Coupon')->where("id = {$val['id']}")->setInc('send_num'); // 优惠券领取数量加一            
            // }
        

        $this->user_id = Role::getuser_id();
        $p = I('page',0);
        if(!$this->user_id)
            exit(json_encode(array('status'=>-1,'msg'=>'参数有误','result'=>'')));
        $data = $this->userLogic->get_coupon($this->user_id,$_REQUEST['type'],$p);
        
        foreach ($data['result'] as $k=>$v){
        	$data['result'][$k]['use_end_time'] = date("y-m-h h:m:s",$v['use_end_time']);
            $data['result'][$k]['money'] = round($v['money'],0);
        }
        
        unset($data['show']);
        exit(json_encode($data));
    }
    /*
     * 获取商品收藏列表
     */
    public function getGoodsCollect(){
        $this->user_id = I('user_id',0);
        $page = I("page",0);
        
        //清理结束 活动商品
        ActivityLogic::clearEndGoods($this->user_id);

        //if(!$this->user_id) exit(json_encode(array('status'=>-1,'msg'=>'缺少参数','result'=>'')));
        $data = $this->userLogic->get_goods_collect($this->user_id,$page);
        foreach($data['result'] as $key=>$value){
        	
        		$data['result'][$key]['image'] = SITE_URL.$value['original_img'];
        	
        }
        unset($data['show']);
        exit(json_encode($data));
    }
    public function getOrderCount(){
        //订单统计  
        if(!$this->user_id) exit(json_encode(array('status'=>-1,'msg'=>'缺少参数','result'=>'')));
        $where = ' user_id='.$this->user_id;
        $where_need_pay = $where.C('WAITPAY').'AND (activity_list_id=0 OR activity_status=0) ';
        $where_need_rec = $where.C('WAITRECEIVE').'AND (activity_list_id=0 OR activity_status=1) ';
        $where_need_send = $where.C('WAITSEND').'AND (activity_list_id=0 OR activity_status=1) ';
        $where_success  = $where.' AND order_status = 2 AND pay_status = 1 '.'AND (activity_list_id=0 OR activity_status=1) ';
        
        $count_need_pay = M('order')->where($where_need_pay)->count(); //待付款
        $count_need_rec = M('order')->where($where_need_rec)->count(); //待收货
        $count_need_send = M('order')->where($where_need_send)->count(); //待发货
        $count_all = M('order')->where($where)->count(); //待付款
        $success_count = M('order')->where($where_success)->count();//已完成 普通
        exit(json_encode(array(
            'status'=>1,
            'msg'=>'获取成功',
            'result'=>array(
                'need_pay'=>$count_need_pay,
                'need_rec'=>$count_need_rec,
                'need_send'=>$count_need_send,
                'count_all'=>$count_all,
                'cut_ongoing'=>$cut_ongoing,
                'pin_ongoing'=>$pin_ongoing,
                'reward_ongoing'=>$reward_ongoing,
                'success_count'=>$success_count
                )
        )));
    }
    /*
     * 用户订单列表
     */
    public function getOrderList(){
    //    $this->user_id = I('user_id',0);
        $type = I('type','');
        if($type == "NO")
        	$type = "";
        
        $page = I('page',1);
        if(!$this->user_id) exit(json_encode(array('status'=>-1,'msg'=>'缺少参数','result'=>'')));
        //条件搜索
        //I('field') && $map[I('field')] = I('value');
        //I('type') && $map['type'] = I('type');
        //$map['user_id'] = $user_id;
        $map = " user_id = {$this->user_id} ";        
        if($type == 'REFUND')
            $map .= 'AND order_status in(6,7) ';
        else
            $map = $type ? $map.C($type) : $map;

        //echo 1;
        //print_r($map);
        //活动类型
        //$map .='AND (activity_list_id is NULL OR activity_status=1) ';
        if($type == 'WAITSEND')
            $map .='AND (activity_status=1 OR activity_list_id=0)';
        
        if(I('type') )
        $count = M('order')->where($map)->count();

        $Page  = new \Think\Page($count,10);

        $show = $Page->show();
        $order_str = "order_id DESC";
        $Page->firstRow = $Page->listRows * $page;
        //echo $page;
        //echo $Page->firstRow;
        
        $order_list = M('order')->order($order_str)->where($map)->limit($Page->firstRow.','.$Page->listRows)->order('order_id desc')->select();
        $orderInfo['activity_status_desc'] = '';//活动状态描述
        //获取订单商品
        foreach($order_list as $k=>$v){
            $order_list[$k] = set_btn_order_status($v);  // 添加属性  包括按钮显示属性 和 订单状态显示属性
            if($order_list[$k]['order_status']==7)
                $order_list[$k]['order_status_desc']='已退款';
            //订单总额
            //$order_list[$k]['total_fee'] = $v['goods_amount'] + $v['shipping_fee'] - $v['integral_money'] -$v['bonus'] - $v['discount'];
            $data = $this->userLogic->get_order_goods($v['order_id']);
            
            foreach ($data['result'] as $key => $value)
            {
            	$value['image'] = SITE_URL.$value['original_img'];
                $value['actInfo'] = $actInfo;
                $data['result'][$key] = $value;
            }
            
            $order_list[$k]['goods_list'] = $data['result'];            
        }
        exit(json_encode(array('status'=>1,'msg'=>'获取成功','result'=>$order_list,'start'=>$Page->firstRow,'where'=>'')));
    }

    /*
     * 获取订单详情
     */
    public function getOrderDetail(){
        $order_id = (int)$_POST['order_id'];
        if(!$_POST['openid']) exit(json_encode(array('status'=>-1,'msg'=>'缺少参数','result'=>'')));
        $u = M('users')->where(array('open_id'=>$_POST['openid']))->find();
        $this->user_id = $u['user_id'];
        if($order_id){
            $map['order_id'] = $order_id;
        }
        $map['user_id'] = $this->user_id;
        $order_info = M('order')->where($map)->find();
        $order_info = set_btn_order_status($order_info);  // 添加属性  包括按钮显示属性 和 订单状态显示属性
        
        if(!$this->user_id > 0)
            exit(json_encode(array('status'=>-1,'msg'=>'参数有误','result'=>'')));
        if(!$order_info){
            exit(json_encode(array('status'=>-1,'msg'=>'订单不存在','result'=>'')));
        }
        
        $invoice_no = M('DeliveryDoc')->where("order_id = $order_id")->getField('invoice_no',true);
        $order_info['invoice_no'] = implode(' , ', $invoice_no);
        // 获取 最新的 一次发货时间
        $order_info['shipping_time'] = M('DeliveryDoc')->where("order_id = $order_id")->order('id desc')->getField('create_time');        
        
        //获取订单商品
        $data = $this->userLogic->get_order_goods($order_info['order_id']);
        foreach ($data['result'] as $key=>$value)
        {
        	$data['result'][$key]['image'] = SITE_URL.$value['original_img'];
        }
        $order_info['goods_list'] = $data['result'];
//        $order_info['invoice_no'] = empty($order_info['invoice_no']) ? '等待商家发货':$order_info['invoice_no'];
        $traces = [];
        // $traces = $this->getKd('yunda',$order_info['invoice_no']);
        // $traces = $traces['Traces'];
        // $traces = [
        // ['2018年7月3日 下午1:26:02','快件已被 已签收 签收'],
        // ['2018年7月3日 下午1:22:09','[珠海市]广东珠海公司香洲区桃园分部派件员 王陈伟 15697599307正在为您派件'],
        // ['2018年7月3日 上午9:27:51','到达目的地网点广东珠海公司香洲区桃园分部，快件将很快进行派送'],
        // ['2018年7月3日 上午7:44:33','在广东珠海公司进行发出扫描，将发往：广东珠海公司香洲区桃园分部'],
        // ['2018年7月3日 上午3:44:05','从广东中山分拨中心发出，本次转运目的地：广东珠海公司'],
        // ['2018年7月3日 上午3:41:07','在分拨中心广东中山分拨中心进行卸车扫描'],
        // ['2018年7月2日 下午11:56:18','在广东深圳公司中心分拨分部进行下级地点扫描，即将发往：广东中山分拨中心'],
        // ['2018年7月2日 下午9:12:11','在广东深圳公司宝安区福永大洋田分部进行揽件扫描'],
        // ['2018年7月2日 下午6:53:08','卖家发货'],
        // ['2018年7月2日 下午5:29:32','商品已经下单']
        // ];
        $order_info['traces'] = $traces;
        if(empty($order_info['invoice_no'])){
            $order_info['invoice_no']='暂无';
        }else{
            //查询快递信息 字段数据为 快递鸟 接口数据
            // $kd = new \RrjAPI\Lib\KdApi();
            // $res = $kd->getTraces($order_info['invoice_no'], $order_info['shipping_code']);
            $kdData = $this->getKd($order_info['shipping_code'],$order_info['invoice_no']);
            for($i=count($kdData['Traces']);$i>0;$i--){
                $traces[] = $kdData['Traces'][$i-1];    
            }
            // if(!count($traces))
            //     $traces[] = '暂无收货信息!';
        }
        if(($order_info['pay_status']==1||$order_info['pay_status']=='cod')&&$order_info['shipping_status']!=1&&($order_info['order_status']==0||$order_info['order_status']==1)){
            $order_info['status']='等待商家发货';
        }
        if ($order_info['order_status']==6){
            $order_info['status']='退款中';
        }
        if ($order_info['order_status']==7){
            $order_info['status']='已退款';
        }
        if ($order_info['order_status']==3){
            $order_info['status']='已取消';
        }
        if ($order_info['order_status']==5){
            $order_info['status']='已作废';
        }
        if ($order_info['order_status']==2&&$order_info['pay_status']==1){
            $order_info['status']='已完成';
        }
        if ($order_info['pay_status']==0&&$order_info['order_status']==0&&$order_info['pay_code']!='cod'){
            $order_info['status']='待支付';
        }
        if ($order_info['shipping_status']==1&&$order_info['order_status']==1){
            $order_info['status']='待收货';
        }
    //    $order_info['shipping_name'] = "京东";
        //$order_info['total_fee'] = $order_info['goods_price'] + $order_info['shipping_price'] - $order_info['integral_money'] -$order_info['coupon_price'] - $order_info['discount'];
        exit(json_encode(array('status'=>1,'msg'=>'获取成功','result'=>$order_info,'traces'=>$traces)));
    }

    /**
     * 取消订单
     */
    public function cancelOrder(){
        $id = I('post.order_id');
        $this->user_id = Role::getuser_id();
        if(!$this->user_id > 0 || !$id > 0)
            exit(json_encode(array('status'=>-1,'msg'=>'参数有误','result'=>'')));
        $data = $this->userLogic->cancel_order($this->user_id,$id);
        $order = D('order')->find($id);
        JarTicket::setInvalidByUser($order['ticket_id']);
        exit(json_encode($data));
    }

    /**
     *未发货订单申请退款
     */
    public function refundOrder(){
        $order_id = (int)$_POST['order_id'];
        $content = $_POST['content'];
        if(!$order_id) exit(json_encode(array('status'=>-1,'msg'=>'参数有误','result'=>'')));
        $order = M('order')->where(array('order_id'=>$order_id,'user_id'=>$this->user_id))->find();
        if(!$order) exit(json_encode(array('status'=>-1,'msg'=>'订单不存在!','result'=>'')));
        if($order['shipping_status']!=0) exit(json_encode(array('status'=>-1,'msg'=>'订单已发货!','result'=>'')));
        if($order['pay_status']!=1) exit(json_encode(array('status'=>-1,'msg'=>'订单未支付!','result'=>'')));
        


        $res = M('order')->where(array('order_id'=>$order_id,'user_id'=>$this->user_id))->save(array('order_status'=>6, 'u_text'=>$content));
        if(!$res)
            exit(json_encode(array('status'=>-1,'msg'=>'操作失败!','result'=>'')));

        //生成退款申请记录
    //     $data['order_id'] = $order_id; 
    //     $data['order_sn'] = $order['order_sn']; 
    // //    $data['goods_id'] = $order['$goods_id'); 
    //     $data['addtime'] = time(); 
    //     $data['user_id'] = $this->user_id;            
    //     $data['type'] = 0; // 服务类型  退货 或者 换货
    //     $data['reason'] = $_REQUEST['content']; // 问题描述
    //     $data['imgs'] = ''; // 用户拍照的相片
    //     $data['spec_key'] = 0; // 商品规格
    //     $data['store_id'] = M('order')->where("order_id = $order_id")->getField('store_id'); // 店铺id
    //     M('return_order')->add($data);

        exit(json_encode(array('status'=>1,'msg'=>'申请成功!','result'=>$_POST)));
        
    }

    /**
     * 发送手机注册验证码
     * /index.php?m=Api&c=User&a=send_sms_reg_code&mobile=13800138006&unique_id=123456
     */
    public function send_sms_reg_code(){
        $mobile = I('mobile');     
        $unique_id = I('unique_id');
        if(!check_mobile($mobile))
            exit(json_encode(array('status'=>-1,'msg'=>'手机号码格式有误')));
        $code =  rand(1000,9999);
        $send = $this->userLogic->sms_log($mobile,$code,$unique_id);
        if($send['status'] != 1)
            exit(json_encode(array('status'=>-1,'msg'=>$send['msg'])));
        exit(json_encode(array('status'=>1,'msg'=>'验证码已发送，请注意查收')));
    }

 /**
     * 获取openId
     * https://api.weixin.qq.com/sns/jscode2session?appid=wx8e7f5dc3ea8ebc27&secret=6a859a26666237f40bb87c3c6de0cc82&js_code=' + res.code + '&grant_type=authorization_code
     */
    public function sendappid(){
        $appid = $_GET['appid'];
        $secret = $_GET['secret'];
        $js_code = $_GET['js_code'];
        $url='https://api.weixin.qq.com/sns/jscode2session';
        $data = array
        (
            'appid'=>$appid,			//用户账号
            'secret'=>$secret,			//MD5位32密码,密码和用户名拼接字符
            'js_code'=>$js_code,				//号码，以英文逗号隔开
            'grant_type'=>'authorization_code',			//内容
        );
        $openid = httpRequest($url,'POST',$data);
        $obj=json_decode($openid);
//        if($obj->openid){
//            $user = M('users')->where(['openid'=>$obj->openid])->find();
//            if(!$user){ M('users')->add(['openid'=>$obj->openid]);}//2018 gdd 创建用户
//        }
        exit(json_encode(array('openid'=>$obj->openid,'msg'=>'成功')));
    }

    /**
     *  收货确认
     */
    public function orderConfirm(){
        $id = I('post.order_id',0);
        $this->user_id = Role::getuser_id();
        if(!$this->user_id || !$id)
            exit(json_encode(array('status'=>-1,'msg'=>'参数有误','result'=>'')));
        $data = confirm_order($id,$this->user_id);
        
        $param = D('jar_ticket')->where(['ticket_order_id'=>$order_id])->find();
        D('jar_ticket')->where(['id'=>$param['id']])->save(['ticket_status'=>1]);
        //确认收货后领酒卷被完全使用
        exit(json_encode($data));
    }

    public function comments(){
    	$this->user_id = Role::getuser_id();
    	$p = I('page',0);
    	
    	$status = I('get.status');
    	$logic = new UsersLogic();
    	$result = $logic->get_comment($this->user_id , $status,$p); //获取评论列表

    	$datas = $result['result'];
    	
    	foreach ($datas as $key=>$value)
    	{
    		$datas[$key] = array_merge(M('goods')->where(array("goods_id"=>$value['goods_id']))->find(),$value);
    		$datas[$key]['image'] = SITE_URL.$datas[$key]['original_img'];
    		$datas[$key]['add_time'] = date("Y-m-d H:i:s",$datas[$key]['add_time']);
    		$comment = M('comment')->where(array("goods_id"=>$datas[$key]['goods_id'],"order_id"=>$datas[$key]['order_id']))->find();
    		if($comment)
    		{
    			$datas[$key]['service_rank'] = $comment['service_rank'];
    		}
    	}
    	
    	
    	if(!$datas)
    		exit(json_encode(array('status'=>-1,'msg'=>'操作失败','result'=>'')));
    	exit(json_encode(array('status'=>1,'msg'=>'操作成功','result'=>$datas)));
    }
    
    /*
     *添加评论
     */
    public function add_comment(){                
      
            // 晒图片        
            if($_FILES[img_file][tmp_name][0])
            {
                    $upload = new \Think\Upload();// 实例化上传类
                    $upload->maxSize   =    $map['author'] = (1024*1024*3);// 设置附件上传大小 管理员10M  否则 3M
                    $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
                    $upload->rootPath  =     './Public/upload/comment/'; // 设置附件上传根目录
                    $upload->replace  =     true; // 存在同名文件是否是覆盖，默认为false
                    //$upload->saveName  =   'file_'.$id; // 存在同名文件是否是覆盖，默认为false
                    // 上传文件 
                    $info   =   $upload->upload();                 
                    if(!$info) {// 上传错误提示错误信息                                                                                                
                        exit(json_encode(array('status'=>-1,'msg'=>$upload->getError()))); //$this->error($upload->getError());
                    }else{
                        foreach($info as $key => $val)
                        {
                            $comment_img[] = '/Public/upload/comment/'.$val['savepath'].$val['savename'];                            
                        }   
                        $comment_img = serialize($comment_img); // 上传的图片文件
                    }                     
            }         
         
         
            
            //$unique_id = I("unique_id"); // 唯一id  类似于 pc 端的session id
            $this->user_id = I('user_id'); // 用户id
            $user_info = M('users')->where("user_id = {$this->user_id}")->find();            

            $add['goods_id'] = I('goods_id');
            $add['email'] = $user_info['email'];
            //$add['nick'] = $user_info['nickname'];
            $add['username'] = $user_info['nickname'];
            $add['order_id'] = I('order_id');
            $add['service_rank'] = I('service_rank');
            $add['deliver_rank'] = I('deliver_rank');
            $add['goods_rank'] = I('goods_rank');
           // $add['content'] = htmlspecialchars(I('post.content'));
            $add['content'] = I('content');
            $add['img'] = $comment_img;
            $add['add_time'] = time();
            $add['ip_address'] = $_SERVER['REMOTE_ADDR'];
            $add['user_id'] = $this->user_id;                    
            
            //添加评论
            $row = $this->userLogic->add_comment($add);
            exit(json_encode($row));
    }  
    
    /*
     * 账户资金
     */
    public function account(){
        
        $this->user_id = I("user_id"); // 唯一id  类似于 pc 端的session id
       // $user_id = I('user_id'); // 用户id
        //获取账户资金记录
        $page = I("page",0);
        $data = $this->userLogic->get_account_log($this->user_id,I('get.type'),$page);
        $account_log = $data['result'];
        
        foreach ($account_log as $key=>$value)
        {
        	$account_log[$key]['change_time'] = date("Y-m-d h:i:s",$value['change_time']);
        }
        
        exit(json_encode(array('status'=>1,'msg'=>'获取成功','result'=>$account_log)));
    }    
    
    /**
     * 退换货列表
     */
    public function return_goods_list()
    {        
        
        $unique_id = I("unique_id"); // 唯一id  类似于 pc 端的session id
       // $user_id = I('user_id'); // 用户id       
        $count = M('return_goods')->where("user_id = {$this->user_id}")->count();        
        $page = new \Think\Page($count,4);
        $list = M('return_goods')->where("user_id = {$this->user_id}")->order("id desc")->limit("{$page->firstRow},{$page->listRows}")->select();
        $goods_id_arr = get_arr_column($list, 'goods_id');
        if(!empty($goods_id_arr))
            $goodsList = M('goods')->where("goods_id in (".  implode(',',$goods_id_arr).")")->getField('goods_id,goods_name');        
        foreach ($list as $key => $val)
        {
            $val['goods_name'] = $goodsList[$val[goods_id]];
            $list[$key] = $val;
        }
        //$this->assign('page', $page->show());// 赋值分页输出                    	    	
        exit(json_encode(array('status'=>1,'msg'=>'获取成功','result'=>$list)));
    }
    
    
    /**
     *  售后 详情
     */
    public function return_goods_info()
    {
        $id = I('id',0);
        $return_goods = M('return_goods')->where("id = $id")->find();
        if($return_goods['imgs'])
            $return_goods['imgs'] = explode(',', $return_goods['imgs']);        
        $goods = M('goods')->where("goods_id = {$return_goods['goods_id']} ")->find();                
        $return_goods['goods_name'] = $goods['goods_name'];
        exit(json_encode(array('status'=>1,'msg'=>'获取成功','result'=>$return_goods)));
    }    
    
    
    /**
     * 申请退货状态
     */
    public function return_goods_status()
    {
        $order_id = I('order_id',0);        
        $goods_id = I('goods_id',0);
        $spec_key = I('spec_key','');
        
        $return_goods = M('return_goods')->where("order_id = $order_id and goods_id = $goods_id and spec_key = '$spec_key' and status in(0,1)")->find();            
        if(!empty($return_goods))        
            exit(json_encode(array('status'=>1,'msg'=>'已经在申请退货中..','result'=>$return_goods['id']))); 
         else
             exit(json_encode(array('status'=>1,'msg'=>'可以去申请退货','result'=>-1)));
    }

    /**
     * 申请退货
     */
    public function return_goods()
    {
        $unique_id = I("unique_id"); // 唯一id  类似于 pc 端的session id
        //$user_id = I('user_id'); // 用户id              
        $order_id = I('order_id',0);
        $order_sn = I('order_sn',0);
        $goods_id = I('goods_id',0);
        $type = I('type',0); // 0 退货  1为换货
        $reason = I('reason',''); // 问题描述
        $spec_key = I('spec_key');
		                
        if(empty($order_id) || empty($order_sn) || empty($goods_id)|| empty($this->user_id)|| empty($type)|| empty($reason))
            exit(json_encode(array('status'=>-1,'msg'=>'参数不齐!')));
        
        $c = M('order')->where("order_id = $order_id and user_id = {$this->user_id}")->count();
        if($c == 0)
        {
             exit(json_encode(array('status'=>-3,'msg'=>'非法操作!')));           
        }         
        
        $return_goods = M('return_goods')->where("order_id = $order_id and goods_id = $goods_id and spec_key = '$spec_key' and status in(0,1)")->find();            
        if(!empty($return_goods))
        {
            exit(json_encode(array('status'=>-2,'msg'=>'已经提交过退货申请!')));
        }       
        if(IS_POST)
        {
            
    		// 晒图片
    		if($_FILES[img_file][tmp_name][0])
    		{
    			$upload = new \Think\Upload();// 实例化上传类
    			$upload->maxSize   =    $map['author'] = (1024*1024*3);// 设置附件上传大小 管理员10M  否则 3M
    			$upload->exts      =    array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
    			$upload->rootPath  =    './Public/upload/return_goods/'; // 设置附件上传根目录
    			$upload->replace   =    true; // 存在同名文件是否是覆盖，默认为false
    			//$upload->saveName  =  'file_'.$id; // 存在同名文件是否是覆盖，默认为false
    			// 上传文件
    			$upinfo  =  $upload->upload();
    			if(!$upinfo) {// 上传错误提示错误信息
    				$this->error($upload->getError());
    			}else{
    				foreach($upinfo as $key => $val)
    				{
    					$return_imgs[] = '/Public/upload/return_goods/'.$val['savepath'].$val['savename'];
    				}
    				$data['imgs'] = implode(',', $return_imgs);// 上传的图片文件
    			}
    		}            
            $data['order_id'] = $order_id; 
            $data['order_sn'] = $order_sn; 
            $data['goods_id'] = $goods_id; 
            $data['addtime'] = time(); 
            $data['user_id'] = $this->user_id;            
            $data['type'] = $type; // 服务类型  退货 或者 换货
            $data['reason'] = $reason; // 问题描述            
            $data['spec_key'] = $spec_key; // 商品规格						
            M('return_goods')->add($data);      
            exit(json_encode(array('status'=>1,'msg'=>'申请成功,客服第一时间会帮你处理!')));                        
        }     
    } 

    
    public function validateOpenid()
    {
        $open_id = I('get.openid','');
        if(empty($open_id))
            exit(json_encode(array("code"=>'400','msg'=>'验证失败')));
        $res = M('users')->where(array("open_id"=>$open_id))->find();

        if($res)
        {
            if(empty($res['nick_name'])){ exit(json_encode(array("code"=>'400','msg'=>'验证失败'))); }

            $res['role_id']=2;
            $this->updateToken($res['user_id']);
            $res['token'] = $this->token;
            $this->user = $res;
            //token
            $level = M('user_level')->find($res['level']);
            $res['level_info'] = $level; 
//            $res['level_cfg'] = $this->getLevelCfg();
            $res['share_id'] = $res['user_id'];
        	$res['avatarUrl'] = $res['head_pic'] = SITE_URL.$res['head_pic'];
            $res['nickName'] = $res['nickname'];
            $tp_config = M('config')->where(array("name"=>'hot_keywords'))->find();
            $res['lower'] = $this->getShareSubList(0,array('first_leader'=>$res['user_id']));
            $res['priv'] =D("Admin/XcxPriv")->getPrivTree();
            if($tp_config['name'] == 'hot_keywords')
                    $res['hot_keywords'] = explode('|', $tp_config['value']);
            echo  json_encode(array("code"=>'200','msg'=>'验证成功',"data"=>$res));
        }
        else
            echo json_encode(array("code"=>'400','msg'=>'验证失败'));
    }

    //获取小程序可以看到模块的权限
    public function getPriv(){
        //新增权限
        $res=D("Admin/XcxPriv")->getPrivTree();
        echo  json_encode(array("code"=>'200','msg'=>'ok',"data"=>$res));
    }

    //获取小程序可以看到模块的权限1
    public function getPriv1(){
        //新增权限
        $data = D("Admin/XcxPriv")->order('sort desc')->select();
        foreach($data as $k=>$item){
            if($item['xp_id']==18){
                $data[$k]['xp_is_show'] = 1;
            }
        }
        $res=D("Admin/XcxPriv")->getTree($data);
        echo  json_encode(array("code"=>'200','msg'=>'ok',"data"=>$res));
    }

    //level_cfg 加工
    private function getLevelCfg(){
        $level_cfg = M('user_level')->select();
        $level_cfg = \setArrKeyField($level_cfg,'level_id');
        foreach($level_cfg as $k=>$v){
            $level_cfg[$k]['amount'] = (int)$v['amount'];
        }
        return $level_cfg;
    }
    
    public function bindPhone(){
    	$user_id = $_GET['user_id'];
    	$phoneNum = $_GET['phone'];
    	$res = M('users')->where(array("user_id"=>$user_id))->save(array("mobile"=>$phoneNum));
    	echo json_encode(array("code"=>'1','msg'=>'验证成功'));
    }

    /**
     * $phone
     * $realname
     * $code
     */
    public function register()
    {
    	$data['city'] = $_GET['city'];
    	$data['country'] = $_GET['country'];
    	$data['gender'] = $_GET['gender'];
    	$data['open_id'] = $_GET['open_id'];
    	$data['nick_name'] = $_GET['nick_name'];
        $data['nickname'] = $_GET['nick_name'];
    	$data['province'] = $_GET['province'];
    	$data['head_pic'] = $_GET['head_pic'];
    	$data['mobile'] = $_GET['mobile'];
    	$data['realname'] = $_GET['realname'];
        $data['reg_time'] = time();
        $data['first_leader'] = isset($_GET['share_id']) ? (int)$_GET['share_id']:0;
        $code = $_GET['code'];
    	
        if($data['first_leader']){
            $first_leader = M('users')->where("user_id = {$data['first_leader']}")->find();
            $data['second_leader'] = $first_leader['first_leader']; //  第一级推荐人
            $data['third_leader'] = $first_leader['second_leader']; // 第二级推荐人
        }

//        $res = M('users')->where(['openid'=>$data['open_id']])->find();
//        if(!$res){
//            echo json_encode(array("code"=>'400','msg'=>'非法请求!'));
//            exit;
//        }
//        $id = $res['user_id'];
//        if($res['nick_name']){
//            echo json_encode(array("code"=>'200','msg'=>'注册成功!','res'=>$res));
//            exit;
//        }
        if (!Users::checkSmsCode($data['mobile'],$code)){
            echo json_encode(array("code"=>'400','msg'=>'手机验证码错误!','res'=>[]));exit;
        }
        $res = M('users')->where(['mobile'=>$data['mobile']])->find();
        if ($res){
            if ($res['open_id']==null){   //后台添加用户,用户未绑定
                if(M('users')->where(['mobile'=>$data['mobile']])->save($data)){
                    $id = $res['user_id'];
                }else{
                    echo json_encode(array("code"=>'400','msg'=>'系统错误!','res'=>[]));exit;
                }
            }else{  //手机号已注册
                echo json_encode(array("code"=>'400','msg'=>'手机号重复!','res'=>[]));exit;
            }
        }else{      //自主注册用户
            if (!$id = M('users')->add($data)){
                echo json_encode(array("code"=>'400','msg'=>'系统错误!','res'=>[]));exit;
            }
        }
//        M('users')->where(['openid'=>$data['open_id']])->save($data);

        $this->updateToken($id);

        $res = M('users')->where(array("user_id"=>$id))->find();
        $this->user = $res;
        $res['lower'] = $this->getShareSubList(0,array('first_leader'=>$id));
        $level = M('user_level')->find($res['level']);
        $res['level_info'] = $level;
        $res['level_cfg'] = $this->getLevelCfg();
        //更新上级 对应下级 的数量
        if($data['first_leader']) M('users')->where("user_id = {$data['first_leader']}")->setInc('lower_1',1);
        if($data['second_leader']) M('users')->where("user_id = {$data['second_leader']}")->setInc('lower_2',1);
        if($data['third_leader']) M('users')->where("user_id = {$data['third_leader']}")->setInc('lower_3',1);

    	$head_pic = $this->test($data['head_pic'],$id);
    	
        $res['avatarUrl'] = $res['head_pic'] = SITE_URL.$head_pic;
        $res['nickName'] = $res['nickname'];
        $res['share_id'] = $res['user_id'];
    	if($res)
    	{
    		$tp_config = M('config')->where(array("name"=>'hot_keywords'))->find();
    		if($tp_config['name'] == 'hot_keywords')
    			$res['hot_keywords'] = explode('|', $tp_config['value']);
    			echo json_encode(array("code"=>'200','msg'=>'注册成功','res'=>$res));
    	}
    	else
    		echo json_encode(array("code"=>'400','msg'=>'失败'));
    
    }
    
    
    function test($url,$id) {
    
    
    	$header = array("Connection: Keep-Alive", "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8", "Pragma: no-cache", "Accept-Language: zh-Hans-CN,zh-Hans;q=0.8,en-US;q=0.5,en;q=0.3", "User-Agent: Mozilla/5.0 (Windows NT 5.1; rv:29.0) Gecko/20100101 Firefox/29.0");
    
    	$ch = curl_init();
    
    	curl_setopt($ch, CURLOPT_URL, $url);
    
    	curl_setopt($ch, CURLOPT_HEADER, $v);
    
    	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    	curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
    
    
    	$content = curl_exec($ch);
    
    	$curlinfo = curl_getinfo($ch);
    
    	//echo "string";
    
    	//print_r($curlinfo);
    
    	//关闭连接
    
    	curl_close($ch);
    
    
    	if ($curlinfo['http_code'] == 200) {
    
    		if ($curlinfo['content_type'] == 'image/jpeg') {
    
    			$exf = '.jpg';
    
    		} else if ($curlinfo['content_type'] == 'image/png') {
    
    			$exf = '.png';
    
    		} else if ($curlinfo['content_type'] == 'image/gif') {
    
    			$exf = '.gif';
    
    		}
    
    		//存放图片的路径及图片名称  *****这里注意 你的文件夹是否有创建文件的权限 chomd -R 777 mywenjian
    
    		$filename = "Public/head/".$id . $exf;//这里默认是当前文件夹，可以加路径的 可以改为$filepath = '../'.$filename
    
    		$res = file_put_contents($filename, $content);//同样这里就可以改为$res = file_put_contents($filepath, $content);
    
    		$filename = "/".$filename;
    		D('users')->where(array("user_id"=>$id))->save(array('head_pic'=>$filename));
    		return $filename;
    	}
    
    }
    
    
    public function getHotKeywords()
    {
    
    	
    		$tp_config = M('config')->where(array("name"=>'hot_keywords'))->find();
    
    		if($tp_config['name'] == 'hot_keywords')
    			$res['hot_keywords'] = explode('|', $tp_config['value']);
    
    
    		echo  json_encode(array("code"=>'200','msg'=>'验证成功',"data"=>$res));
    	
    }

    public function logoutWX(){
    	
    	$open_id = $_GET['openid'];
    	
    	if($open_id == null)
    	{
    		echo json_encode(array("code"=>'400','msg'=>'非法参数'));
    		exit();
    	}
    	
    	
    	$res = 1;///D('phone_captcha')->where(array("phone"=>$phone,'captcha'=>$num))->find();
    	if($res)
    	{
    		$res = M('users')->where(array("open_id"=>$open_id))->save(array("open_id"=>'',"openid"=>'','oauth'=>''));
    	
    	
    		if($res)
    		{
    			echo json_encode(array("code"=>'200','msg'=>'注销成功','res'=>$res));
    		}
    		else
    			echo json_encode(array("code"=>'400','msg'=>'注销有误,请稍后重试'));
    	}
    	else
    		echo json_encode(array("code"=>'400','msg'=>'验证码或者手机号码有误'));
    }
    
    public function validate()
    {
        $phone = $_GET['phone'];
        $num = $_GET['num'];
        $open_id = $_GET['openid'];
    
        if($open_id == null)
        {
            echo json_encode(array("code"=>'400','msg'=>'非法参数'));
            exit();
        }
        if($phone == null || strlen($phone) != 11)
        {
            echo json_encode(array("code"=>'400','msg'=>'手机号码输入有误'));
            exit();
        }
        
        
        $res = 1;///D('phone_captcha')->where(array("phone"=>$phone,'captcha'=>$num))->find();
        if($res)
        {
            $res = M('users')->where(array("mobile"=>$phone))->save(array("open_id"=>$open_id,"openid"=>$open_id,'oauth'=>'weixin'));
            
            
            if($res)
            {
            	$res = M('users')->where(array("open_id"=>$open_id))->find();
                echo json_encode(array("code"=>'200','msg'=>'登录成功','res'=>$res));
            }
            else
                echo json_encode(array("code"=>'400','msg'=>'手机号码有误'));
        }
        else
            echo json_encode(array("code"=>'400','msg'=>'验证码或者手机号码有误'));
    }

    
    public function points()
    {
    	$this->user_id = I('user_id');
    	$p = I('page',0);
    	$type = I('type','all');
    	$this->assign('type',$type);
    	if($type == 'recharge'){
    		$count = M('recharge')->where("user_id=" . $this->user_id)->count();
    		$Page = new Page($count, 16);
    		$Page->firstRow = $p * $Page->listRows;
    		$account_log = M('recharge')->where("user_id=" . $this->user_id)->order('order_id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
    	}else if($type == 'points'){
    		$count = M('account_log')->where("user_id=" . $this->user_id ." and pay_points!=0 ")->count();
    		$Page       = new \Think\Page($count,10);
    		$Page->firstRow = $p * $Page->listRows;
    		$account_log = M('account_log')->where("user_id=" . $this->user_id." and pay_points!=0 ")->order('log_id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
    	}else{
    		$count = M('account_log')->where("user_id=" . $this->user_id)->count();
    		$Page = new Page($count, 16);
    		$Page->firstRow = $p * $Page->listRows;
    		$account_log = M('account_log')->where("user_id=" . $this->user_id)->order('log_id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
    	}
    	foreach ($account_log as $key=>$value)
    	{
    		$account_log[$key]['change_time'] = date("Y-m-d h-i-s",$value['change_time']);
    	}
    	echo json_encode(array("code"=>'200','msg'=>'成功','res'=>$account_log));
    }

    private function updateToken($user_id){
        $this->token = md5(time().mt_rand(1,999999999));
        M('users')->where(array('user_id'=>$user_id))->save(array('token'=>$this->token));
    }

    public function getSubList(){
        $this->mergeRequest();
        $this->checkToken();
        $condition = array();
        if((int)$_REQUEST['son_id']){
            $condition['first_leader'] = (int)$_REQUEST['son_id'];
        }else{
            $condition['first_leader'] = $this->user['user_id'];
        }
        $this->getShareSubList(1,$condition,100);
    }

    public function getSubSearch(){
        $this->mergeRequest();
        $this->checkToken();
        $condition = array();
        $condition['first_leader'] = $this->user['user_id'];
        $condition['nick_name'] = array('like','%'.$_REQUEST['name'].'%');
        $this->getShareSubList(1,$condition,100);
    }

    private function getShareSubList($is_ajax=0,$condition=array(),$page_length=5){
    //    $condition['first_leader'] = $this->user['user_id'];
        $model = M('users');
        $count = $model->where($condition)->count();
        $_GET['p'] = (int)$_REQUEST['p'];
        $Page  = new AjaxPage($count,$page_length);
        $order = 'user_id '; //默认排序方式
        $order_type = 'desc';
        $access_order = array('time','count');
        $access_order_type = array('desc','asc');

        if(in_array($_REQUEST['order'],$access_order)){
            switch($_REQUEST['order']){
                case 'time':$order='user_id ';break;
                case 'count':$order='lower_1 ';break;
            }
            if(in_array($_REQUEST['order_type'],$access_order_type))
            switch($_REQUEST['order_type']){
                case 'desc':$order_type='desc';break;
                case 'asc':$order_type='asc';break;
            }
        }
        $order = $order.$order_type;

        $sub_user = $model->where($condition)
        ->order($order)
        ->limit($Page->firstRow.','.$Page->listRows)
        ->field('head_pic,nick_name,user_id,lower_1,lower_2,lower_3,reg_time')
        ->select();
        foreach($sub_user as $k=>$v){
            $sub_user[$k]['head_pic'] = SITE_URL.$v['head_pic'];
            $sub_user[$k]['reg_time'] = date('Y-m-d',$v['reg_time']);
        }
        //加假数据
        // for($i=$count;$i<100;$i++){
        //     $sub_user[$i] = $sub_user[0];
        // }

        $return['result'] = array('count'=>$count,'list'=>$sub_user);//调
        if($is_ajax==1)
            $this->ajaxReturn($return);
        else
            return $sub_user;
    }

    private function getShareInfo(){
        $return = array('status'=>1,'msg'=>'返回成功!','result'=>'');
        $first_leader = M('users')
        ->where(array('user_id'=>$this->user['first_leader']))
        ->field('user_id,head_pic,nick_name')
        ->find();
        $result = array(
            'p_leader'=>$first_leader
            );
        $return['result'] = $result;
        $this->ajaxReturn($return);
    }

    //我的团购列表
    public function myGroupBuy(){
        $this->checkToken();
        $prefix = C('DB_PREFIX');
        $time = time();
        $page = (int)$_REQUEST['p']>0 ? (int)$_REQUEST['p']:1;
        $total_num = M('group_share')
        ->where(array(
                'start_time'=>array('lt',$time),
                'end_time'  =>array('gt',$time),
                'pub_uid'   =>$this->user_id
            ))
        ->count();
        $every_page = 20;
        $total_page = ceil($total_num/$every_page);
        $res = M('group_share')->alias('gs')
        ->join("{$prefix}group_buy gb on(gb.id=gs.group_id)",'LEFT')
        ->join("{$prefix}goods g on(g.goods_id=gb.goods_id)",'LEFT')
        ->where(
             array(
                'gb.start_time'=>array('lt',$time),
                'gb.end_time'  =>array('gt',$time),
                'gs.pub_uid'   =>$this->user_id
            )
        )
        ->field('gs.group_id,gs.create_time,gb.goods_id,gb.g_type,gb.every_cut_money,gb.cut_num,g.original_img,g.shop_price,g.goods_name')
        ->limit(($page-1)*$every_page,$every_page)
        ->select();
        foreach($res as $k=>$v){
            $res[$k]['original_img'] = SITE_URL.$v['original_img'];
            $res[$k]['buy_num'] = 1;
            $res[$k]['has_cut_num'] = M('group_share_info')->where(array('group_id'=>$v['group_id'], 'help_uid'=>$this->user_id))->count();
            $res[$k]['now_price'] = $v['shop_price'] - $res[$k]['has_cut_num']*$v['every_cut_money'];
            $res[$k]['goods'] = array(
                'goods_id'=>$v['goods_id'],
                'original_img'=> SITE_URL.$v['original_img'],
                "goods_name"=>$v['goods_name'],
                "attr"=>$this->getGoodsAttr($v['goods_id'])
            );
            $res[$k]['create_time'] = date('Y-m-d',$v['create_time']);
        }
        $return = array('status'=>1,'msg'=>'返回成功!','result'=>$res);
        $this->ajaxReturn($return);
    }

    private function getGoodsAttr($goods_id){
        $where['goods_id'] = $goods_id;
        $model = M('Goods');
        $goods  = $model->where($where)->find();
        // 处理商品属性
        $goods_attribute = M('GoodsAttribute')->getField('attr_id,attr_name'); // 查询属性 

        $goods_attr_list = M('GoodsAttr')->where(['goods_id'=>$goods_id])->order('attr_id asc')->select(); // 查询商品属性表                        
        foreach($goods_attr_list as $key => $val)
        {
            $goods_attr_list[$key]['attr_name'] = $goods_attribute[$val['attr_id']];
        }
        return $goods_attr_list;
    }


   /**
     * 获得用户的下级的佣金
     * @param Int p 当前页
     * @param String date 当前月份
     * @param String token 加密字符串
     * @param String user_id 加密字符串
     */
    public function ajaxGetRebateList(){
        $this->mergeRequest();
//        $this->checkToken();
        if (!($_REQUEST['user_id']&&$_REQUEST['date'])){
            $data['info']='error';
            $data['status']=0;
            $data['result']=array();
            die(json_encode($data));
        }
        $condition = array();
        if((int)$_REQUEST['user_id']){
            $condition['first_leader|second_leader'] = (int)$_REQUEST['user_id'];
        }else{
            $condition['first_leader|second_leader'] = $this->user['user_id'];
        }
        $data=$this->getRebateList($condition,100);
        $data['info']='ok';
        $data['status']=1;
        die(json_encode($data));
    }

    protected function getRebateList($condition,$page_length){

        $map1['first_leader']=$_REQUEST['user_id'];
        $model = M('users');
        $count = $model->where($map1)->count();
        $_GET['p'] = (int)$_REQUEST['p'];
//        $Page  = new AjaxPage($count,$page_length);
//        $order = 'user_id '; //默认排序方式
//        $order_type = 'desc';
//        $access_order = array('time','count');
//        $access_order_type = array('desc','asc');
//
//        if(in_array($_REQUEST['order'],$access_order)){
//            switch($_REQUEST['order']){
//                case 'time':$order='user_id ';break;
//                case 'count':$order='lower_1 ';break;
//            }
//            if(in_array($_REQUEST['order_type'],$access_order_type))
//                switch($_REQUEST['order_type']){
//                    case 'desc':$order_type='desc';break;
//                    case 'asc':$order_type='asc';break;
//                }
//        }
//        $order = $order.$order_type;

        $sub_user = $model->where($condition)
//            ->order($order)
//            ->limit($Page->firstRow.','.$Page->listRows)
            ->field('head_pic,nick_name,user_id,reg_time,first_leader,second_leader')
            ->select();
        $user_idArr=array();
        $first=array();         //算下级有多少人
        $returnData=array();
        foreach($sub_user as $k=>$v){           //整理数据
            $sub_user[$v['user_id']]=$v;
            unset($sub_user[$k]);
            $sub_user[$v['user_id']]['head_pic'] = SITE_URL.$v['head_pic'];
            $sub_user[$v['user_id']]['reg_time'] = date('Y-m-d',$v['reg_time']);
            $first[$v['first_leader']][$v['user_id']]=0;
            $user_idArr[]=$v['user_id'];
            if($v['first_leader']==$_REQUEST['user_id']){
                $sub_user[$v['user_id']]['num']=0;
                $returnData[$v['user_id']]=$sub_user[$v['user_id']];
            }
        }
//        这里有个时间筛选
        $timeStr=$_REQUEST["date"];
        $starttime=strtotime($timeStr);
        $lasttime=strtotime($timeStr." + 1 month");
     //   $map['confirm_time']=array(array('egt',$starttime),array('elt',$lasttime));
        $map['confirm'] = array('between',$starttime.','.$lasttime);
        $distributeDataArr=array();
        if (!empty($user_idArr)){               //取出用户的佣金 并整理
            $logModel=M("rebate_log");
            $map['buy_user_id']=array('in',$user_idArr);
            $map['user_id']=$_REQUEST['user_id'];
            $map['status']=3;
            $distributeData=$logModel
            ->field('sum(money) as num,buy_user_id')
            ->where($map)
            ->group('buy_user_id')
            ->select();
            // echo date('Y-m-d',$starttime);
            // var_dump($map);
            foreach ($distributeData as $item){
                $distributeDataArr[$item['buy_user_id']]=$item['num'];
            }
        }

        //将用户的佣金累加到一级分销人
        foreach($sub_user as $k=>$value){
            if (isset($first[$value['user_id']])){
                foreach ($first[$value['user_id']] as $user_id=>$v){
                    $returnData[$value['user_id']]['num']+=isset($distributeDataArr[$user_id])?$distributeDataArr[$user_id]:0;
                }
            }
        }
        foreach ($first[$_REQUEST["user_id"]] as $user_id=>$v){
            $returnData[$user_id]['num']+=isset($distributeDataArr[$user_id])?$distributeDataArr[$user_id]:0;
        }
	$returnData=array_values($returnData);
        //返回数据
        $return['result'] = array('count'=>$count,'list'=>$returnData);//调
        return $return;
    }

    // public function uploadHead(){
    //     $tmpname = $this->user_id;
    //     $tmpP = $_FILES["file"]["tmp_name"];
    //     if($tmpP){
    //          $type = mime_content_type($tmpP);//获得文件类型
    //          $p = null;
    //          if($type=="image/png"){
    //              $p.=".png";
    //          }
    //          elseif($type=="image/gif"){
    //              $p.=".gif";
    //          }
    //          elseif($type=="image/jpeg"){
    //              $p.=".jpeg";
    //          }
    //          elseif($type=="image/bmp"){
    //              $p.=".bmp";
    //          }
    //          elseif($type=="image/x-icon"){
    //              $p.=".icon";
    //          }
    //          if($p){
    //              $fp =WEB_ROOT."Public/head/".$tmpname.$p ;
    //              if (move_uploaded_file($tmpP, $fp )) {//保存文件
    //                  $headimgurl=SITE_URL."/Public/head/".$tmpname.$p ;
    //                  echo $headimgurl;
    //              }else{
    //                 echo '-1';
    //              }
    //          }
    //     }

    // }
    //用户签到
    public function report(){
        $return = ['status'=>-1,'msg'=>'已签到!'];
        $day_end = strtotime(date('Y-m-d',strtotime("+1 day")));
        $has_report = M('report')->where(['create_time'=>['lt',$day_end], 'uid'=>$this->user_id])->count();
        if(!$has_report){
            $success = M('report')->add(['uid'=>$this->user_id, 'create_time'=>time()]);
            if($success){
                $return['status'] = 1;
                $return['msg'] = '签到成功!';
            }
        }
        $this->ajaxReturn($return);
    }

    //签到列表
    public function reportList(){

    }

    //读取活动订单列表  活动状态 act_status   订单状态  o_status
    public function getActivityOrder(){
        $act_type = 1;//活动类型
        // $pay_status = 1;//支付状态
        // $act_status = 1;//活动状态
        // $send_status=1; //发货状态
        $o_status = 1;//订单状态  0所有,1待付款,2待收货,3已取消,4过期

        if($type)
            $type = "";
        
        $page = I('page','');
        if(!$this->user_id) exit(json_encode(array('status'=>-1,'msg'=>'缺少参数','result'=>'')));
        $where = ['user_id'=>$user_id];
        //条件搜索
        switch($o_status){
            case 0:;break;
            case 1:;break;
            case 2:;break;
            case 3:;break;
            case 4:;break;
            default:;break;
        }
        $count = M('order')->where($where)->count();
        $Page       = new \Think\Page($count,10);
        $show = $Page->show();
        $order_str = "order_id DESC";
        $Page->firstRow = $Page->listRows * $page;
        //echo $page;
        //echo $Page->firstRow;
        $order_list = M('order')->order($order_str)->where($map)->limit($Page->firstRow.','.$Page->listRows)->select();

        //获取订单商品
        foreach($order_list as $k=>$v){     
            $order_list[$k] = set_btn_order_status($v);  // 添加属性  包括按钮显示属性 和 订单状态显示属性
            if($order_list[$k]['order_status']==7)
                $order_list[$k]['order_status_desc']='已退款';
            //订单总额
            //$order_list[$k]['total_fee'] = $v['goods_amount'] + $v['shipping_fee'] - $v['integral_money'] -$v['bonus'] - $v['discount'];
            $data = $this->userLogic->get_order_goods($v['order_id']);
            
            foreach ($data['result'] as $key => $value)
            {
                $data['result'][$key]['image'] = SITE_URL.$value['original_img'];
            }
            
            $order_list[$k]['goods_list'] = $data['result'];            
        }
        exit(json_encode(array('status'=>1,'msg'=>'获取成功','result'=>$order_list)));
    }

    /**
     * 获取用户收藏店铺列表
     */
    public function getUserCollectStore()
    {
        $page = I('page', 1);
        $store_list = D('store')->getUserCollectStore($this->user_id,$page,10);
        $json_arr = array('status' => 1, 'msg' => '获取成功', 'result' => $store_list);
        exit(json_encode($json_arr));
    }

    //我的拼团列表
    public function myPinList(){
        $prefix = C('DB_PREFIX');
        $cfg = getPinConfig();
        $time = time();

        $page = (int)$_REQUEST['p']>0 ? (int)$_REQUEST['p']:1;
        $total_num = M('order')
        ->where([
            'activity_type'   =>5,
        //    'pay_status'      =>1,
            'user_id'         =>$this->user_id,
            ])
        ->count();
        $return = array('status'=>1,'msg'=>'返回成功!','result'=>'');
        if(!$total_num){
            $return['result'] = [];
            $this->ajaxReturn($return);
        }
        $every_page = 20;
        $total_page = ceil($total_num/$every_page);
        $res = M('order')->alias('o')
        ->join("{$prefix}activity_pin_list apl on(apl.id=o.activity_list_id)",'LEFT')
        ->join("{$prefix}goods g on(g.goods_id=apl.goods_id)",'LEFT')
        ->join("{$prefix}order o2 on(o2.activity_list_id=o.activity_list_id)",'LEFT')
        ->where([
            'o.activity_type'=>5,
            'o.user_id'=>$this->user_id,
            'o.order_status'=>['neq',5],
            'o.activity_list_id'=>['gt',0],
            // '_complex'=>[
            //     '_logic'=>'OR',
            //     [
            //     'o.pay_status'=>1,
            //     'o.add_time'=>['lt',time()-120]
            //     ]
            // ],
            'o2.pay_status'=>1,
            'o2.activity_type'=>5
            ])
        ->field('apl.activity_id,apl.create_time,apl.end_time,'.
            'apl.goods_id,apl.order_num,apl.group_num,g.original_img,'.
            'g.shop_price,g.goods_name,o.order_id,apl.p_status as act_status,'.
            'o.activity_list_id,count(o2.activity_list_id) as pay_order_num,o.pay_status')
        ->order('o.order_id desc')
        ->group('o2.activity_list_id')
        ->limit(($page-1)*$every_page,$every_page)
        ->select();
        foreach($res as $k=>$v){
        //    print_r($v);
            $res[$k]['original_img'] = SITE_URL.$v['original_img'];
            $res[$k]['buy_num'] = 1;
            $res[$k]['now_price'] = $v['shop_price'] - $res[$k]['has_cut_num']*$v['every_cut_money'];
            $res[$k]['goods'] = array(
                'goods_id'=>$v['goods_id'],
                'original_img'=> SITE_URL.$v['original_img'],
                "goods_name"=>$v['goods_name'],
                "attr"=>$this->getGoodsAttr($v['goods_id'])
            );
            $res[$k]['need_num'] = $v['group_num']-$v['pay_order_num'];
            $res[$k]['create_time'] = date('Y-m-d',$v['create_time']);
        }
        $res1 = M('order')->alias('o')
        ->where([
            'o.activity_type'=>5,
            'o.user_id'=>$this->user_id,
            'o.order_status'=>['neq',5]
            ])
        ->select();
        $return = array('status'=>1,'msg'=>'返回成功!','result'=>$res,'res1'=>$res1);
        $this->ajaxReturn($return);
    }

    //我发起的砍价列表
    public function myCutList(){
        $return = array('status'=>1,'msg'=>'返回成功!','result'=>'');
        $page = (int)$_REQUEST['p']>0 ? (int)$_REQUEST['p']:1;
        $every_page = 20;
        $this->ajaxReturn($return);
    }

    //我参与的砍价列表
    public function myHelpCutList(){
        $return = array('status'=>1,'msg'=>'返回成功!','result'=>'');
        $page = (int)$_REQUEST['p']>0 ? (int)$_REQUEST['p']:1;
        $every_page = 20;
        $this->ajaxReturn($return);
    }

    //我的抽奖列表
    public function myRewardList(){
        $return = array('status'=>1,'msg'=>'返回成功!','result'=>'');
        $page = (int)$_REQUEST['p']>0 ? (int)$_REQUEST['p']:1;
        $every_page = 20;
        $return['user_id'] = $this->user_id;
        $this->ajaxReturn($return);
    }

    /**
     * 设置订单的form_id号
     * @param String sn 主订单号
     * @param String form_id id号
     */
    public function setformid(){
        $sn=I('sn');
        $form_id=I('form_id');
        if ($sn&&$form_id){
            $map['master_order_sn']=$sn;
            echo $sn,$form_id;
            $data=M('order')->where($map)->find();
            $formArr=explode(',',$data['form_id']);
            $formArr[]=$form_id;
            $formStr=implode(',',$formArr);
            $datas['form_id']=$formStr;
//            echo 'update ty_order set form_id="'.$formStr.'" where master_order_sn='.$sn;
//            M('order')->query('update ty_order set form_id="'.$formStr.'" where master_order_sn='.$sn);
            $res=M('order')->where($map)->save($datas);
        }else{
            return false;
        }
    }

    public function getKd($KdPin,$Num){
        //字段数据为 快递鸟 接口数据
        $kd = new \RrjAPI\Lib\KdApi();
        $num = $Num;
        $kd_pin = $KdPin;
        $num = '3910951848654';
        $kdInfo = M('kd_list')->find($num);
        if($kdInfo){
            if($kdInfo['update_time'] > time()-60*1){
            //    var_dump($kdInfo);
                $kdData = json_decode($kdInfo['data'],true);
                return $kdData;
                $traces = $kdData['Traces'];
                return $traces;
                // foreach($traces as $tra){
                //     echo $tra['AcceptStation'].'['.$tra['AcceptTime'].']<br>';
                // }
            }else{
                $kdData = json_decode($kdData,true);
                if($kdData['State']==3){
                    //如果已签收就不用再去查询了
                    return $kdData;
                }

                $kdData = $kd->getTraces($num, $kd_pin);
                $data = ['trans_num'=>$num, 'data'=>$kdData, 'update_time'=>time()];
                M('kd_list')->save($data);
                $kdData = json_decode($kdData,true);
                return $kdData;
                $traces = $kdData['Traces'];
                return $traces;
                // foreach($traces as $tra){
                //     echo $tra['AcceptStation'].'['.$tra['AcceptTime'].']<br>';
                // }
            //    var_dump($kdData);
            }
        }else{
            $kdData = $kd->getTraces($num, $kd_pin);
            $data = ['trans_num'=>$num, 'data'=>$kdData, 'update_time'=>time()];
            M('kd_list')->add($data);
            $kdData = json_decode($kdData,true);
            return $kdData;
            $traces = $kdData['Traces'];
            return $traces;
            // foreach($traces as $tra){
            //     echo $tra['AcceptStation'].'['.$tra['AcceptTime'].']<br>';
            // }
        //    var_dump($kdData);
        }
    }

    /**
     * 发送手机验证码
     * @param String phone 手机号
     */
    public function sendMsg(){
        $sms = rand(100000,999999);
        if (\Jar\Utils\Utils::sendSMS(I('post.phone',123465),$sms)){
            $this->ajaxReturn(['info'=>'ok','status'=>1,'data'=>$sms]);
        }else{
            $this->ajaxReturn(['info'=>'error','status'=>0,'data'=>[]]);
        }
    }

    public function confirmAddress(){
        // 收货地址
        $addresslist = M('UserAddress')->where("user_id = {$this->user_id}")->select();
        $c = M('UserAddress')->where("user_id = {$this->user_id} and is_default = 1")->count(); // 看看有没默认收货地址 

        if ((count($addresslist) > 0) && ($c == 0)) // 如果没有设置默认收货地址, 则第一条设置为默认收货地址
            $addresslist[0]['is_default'] = 1;
        else if (count($addresslist) > 0) 
        {
            $addresslist[0] = M('UserAddress')->where("user_id = {$this->user_id} and is_default = 1")->find();
            $addresslist[0]['addr_str'] = $this->getAddressStr($addresslist[0]);

            $address = $addresslist[0];

            $addresslist[0] = M('UserAddress')->where("user_id = {$this->user_id} and is_default = 1")->find();
            $addresslist[0]['addr_str'] = $this->getAddressStr($addresslist[0]);
        }

        $points = M("config")->where(array("name"=>"point_rate"))->getField("value");
        
        $json_arr = array(
            'status' => 1,
            'msg' => '获取成功',
            'result' => array(
                'addressList' => $addresslist[0] // 收货地址
            ));
        exit(json_encode($json_arr));
    }
    private function getAddressStr($addr_info){
        $pro = M('region')->find($addr_info['province']);
        $city = M('region')->find($addr_info['city']);
        $dist = M('region')->find($addr_info['district']);
        return array('pro'=>$pro['name'],'city'=>$city['name'],'dist'=>$dist['name'],'addr'=>$addr_info['address']);
    }

    /**
     * 初始化用户密码
     */
    public function ajaxInitUserPass(){
        if (Users::initUserPass(I('post.password'))){
            $this->ajaxReturn(['info'=>'ok','status'=>1,'data'=>[]]);
        }else{
            $this->ajaxReturn(['info'=>Users::getError(),'status'=>0,'data'=>[]]);
        }
    }

    /**
     * 检查用户密码是否存在
     */
    public function ajaxCheckUserPass(){
        if ($data = Users::checkUserPassExist()){
            $this->ajaxReturn(['info'=>'ok','status'=>1,'data'=>['pass'=>true]]);
        }else{
            $this->ajaxReturn(['info'=>'ok','status'=>1,'data'=>['pass'=>false]]);
        }
    }

    /**
     * 修改用户密码
     * @param String newpass 密码
     * @param String oldpass 密码
     */
    public function ajaxChangePass(){
        if ($data = Users::changePass()){
            $this->ajaxReturn(['info'=>'ok','status'=>1,'data'=>[]]);
        }else{
            $this->ajaxReturn(['info'=>Users::getError(),'status'=>0,'data'=>[]]);
        }
    }

    /**
     * 检查用户密码
     * @param String password 密码
     */
    public function ajaxCheckPass(){
        if ($data = Users::checkPass(I('post.password'))){
            $this->ajaxReturn(['info'=>'ok','status'=>1,'data'=>[]]);
        }else{
            $this->ajaxReturn(['info'=>Users::getError(),'status'=>0,'data'=>[]]);
        }
    }
}