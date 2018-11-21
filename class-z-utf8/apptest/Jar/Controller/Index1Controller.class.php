<?php
namespace Jar\Controller;

use Think\Controller;
use Jar\Facades\Role;
class Index1Controller extends Controller {
    public function index(){
//        $Role = new Role(['name'=>'user','age'=>12]);
        Role::init(['name'=>'user11111','age'=>12]);
        echo Role::getname();
    }

    /**
     * 添加罐子
     * @param \String jar_sn        串号
     * @param String jar_name       酒名字
     * @param String jar_capacity   总容量
     * @param String jar_freeze     冻结容量
     * @param String jar_residue    剩余容量
     * @param String jar_unit       单位
     * @param String jar_status     状态
     * @param String jar_qrcode     二维码
     * @param String jar_cellar_age 窖龄
     * @param String jar_cost       价值
     * @param String jar_material   原料
     * @param String jar_quality    质量等级
     */
    public function ajaxAddJar(){
        $jar = new Jar();
        $id = $jar->add(I('post.'));
        if ($id){
            $this->ajaxReturn(['info'=>'ok','status'=>1,'data'=>$id]);
        }else{
            $this->ajaxReturn(['info'=>'error','status'=>0,'data'=>$jar->errors]);
        }
    }

    /**
     * 获取酒罐-分页
     * @param Int pagenow 当前页
     * @param Int pagesize 一页大小
     */
    public function ajaxGetJarList(){
        $jar = new Jar();
        $list = $jar->getListWithPage([],[I('post.pagenow'),I('post.pagesize')]);
        $this->ajaxReturn(['info'=>'ok','status'=>1,'data'=>$list]);
    }

    /**
     * 买酒,绑定酒罐到人
     * @param Int u_id 用户id
     * @param Int jar_id 酒罐id
     */
    public function ajaxJarBuy(){
        $jar = new Jar();
        $id = $jar->getListWithPage(['jar_id'=>1],[]);
    }



    public function getTransTmp(){
        // 物流公司
        $shippingList = M('Plugin')->where("`type` = 'shipping' and status = 1")->select();// 物流公司 
        //设置一个默认物流用于 计算运费
        $shipping_code = [];
        foreach($store_id_arr as $v){
            $shipping_code[$v] = $shippingList[0]['code'];
        }

        //新的地址处理
        $shippingListTmp = M('shipping_area')->where(['store_id'=>['in',$store_id_arr]])->group('store_id')->select();
        //设置一个默认物流用于 计算运费
        $shipping_code = [];
        //调整 物流查询商家已经设置了的物流
        foreach($shippingListTmp as $v){
            $shipping_code[$v['store_id']] = $v['shipping_code'];
        }
        
        //print_r($coupon_id);
        if(!$address_id) exit(json_encode(array('status'=>-1,'msg'=>'请完善收货人信息','result'=>null))); // 返回结果状态
        //这里实际上 不用判断物流  物流是自动选择完成的了
        if(!$shipping_code) exit(json_encode(array('status'=>-1,'msg'=>'请选择物流信息','result'=>null))); // 返回结果状态
        $address = M('UserAddress')->where("address_id = $address_id")->find();
        
        //订单总价  应付金额  物流费  商品总价 节约金额 共多少件商品 积分  余额  优惠券
        $trans_price = CartLogic::getTransCountByWeight($shipping_code[0],$address['province'],$address['city'],$address['district'],$goods['weight']*$goods_num, $goods['store_id']);

        $totalPromPrice = 0;
        $total_amount = $trans_price;

        $result = array(
            'total_amount'      => $total_amount, // 订单总价
            'order_amount'      => $order_amount, // 应付金额      只用于订单在没有参与优惠活动的时候价格是对的, 如果某个商家参与优惠活动 价格会有所变动      
            'goods_price'       => 0, // 商品总价
            'cut_fee'           => 0, // 共节约多少钱
            'anum'              => 1, // 商品总共数量
            'integral_money'    => 0,  // 积分抵消金额
            'user_money'        => 0, // 使用余额
            'coupon_price'      => 0,// 优惠券抵消金额
            'order_goods'       => $goods, // 商品列表 多加几个字段原样返回
            'shipping_price'    => $trans_price, // 物流费                        
            'store_order_prom_amount' => $goods['shop_price']*$goods_num - $totalPromPrice,// 活动订单优惠了多少钱
            'store_order_prom_id'=> $id,// 订单优惠活动id
            'store_order_amount'=> $total_amount, // 订单优惠后是多少钱                        
            'store_shipping_price'=> 0, //每个商家的物流费
            'store_coupon_price'=> 0, //每个商家的优惠券金额        
            'store_goods_price' => 0,//  每个店铺的商品总价            
            'store_point_count' => 0, // 每个商家平摊使用了多少积分            
            'store_balance'     => 0, // 每个商家平摊用了多少余额            
        );
    }

    /**
     * 根据重量计算商品的的运费 
     * @param type $shipping_code 物流 编号
     * @param type $province  省份
     * @param type $city     市
     * @param type $district  区
     * @return int
     */
    public static function getTransCountByWeight($shipping_code,$province,$city,$district,$weight,$store_id)
    {
    //    file_put_contents('./Public/shipping.log', "$shipping_code,$province,$city,$district,$weight");
        if($weight == 0) return 0; // 商品没有重量
        if($shipping_code == '') return 0;

       // 先根据 镇 县 区找 shipping_area_id   
          $shipping_area_id = M('AreaRegion')->where("shipping_area_id in (select shipping_area_id from  ".C('DB_PREFIX')."shipping_area where shipping_code = '$shipping_code') and region_id = {$district}")->getField('shipping_area_id');
        
        // 先根据市区找 shipping_area_id
       if($shipping_area_id == false)    
          $shipping_area_id = M('AreaRegion')->where("shipping_area_id in (select shipping_area_id from  ".C('DB_PREFIX')."shipping_area where shipping_code = '$shipping_code') and region_id = {$city}")->getField('shipping_area_id');

       // 市区找不到 根据省份找shipping_area_id
       if($shipping_area_id == false)
            $shipping_area_id = M('AreaRegion')->where("shipping_area_id in (select shipping_area_id from  ".C('DB_PREFIX')."shipping_area where shipping_code = '$shipping_code') and region_id = {$province}")->getField('shipping_area_id');

       // 省份找不到 找默认配置全国的物流费
       if($shipping_area_id == false)
       {
            // 如果市和省份都没查到, 就查询 tp_shipping_area 表 is_default = 1 的  表示全国的  select * from `tp_plugin`  select * from  `tp_shipping_area` select * from  `tp_area_region`           
           $shipping_area_id = M("ShippingArea")->where("shipping_code = '$shipping_code' and is_default = 1")->getField('shipping_area_id');
       }
       if($shipping_area_id == false)
           return 0;
       /// 找到了 shipping_area_id  找config       
       $shipping_config = M('ShippingArea')->where("shipping_area_id = $shipping_area_id AND store_id=$store_id")->getField('config');
       $shipping_config  = unserialize($shipping_config);
       $shipping_config['money'] = $shipping_config['money'] ? $shipping_config['money'] : 0;

       // 1000 克以内的 只算个首重费
       if($weight < $shipping_config['first_weight'])
       {          
           return $shipping_config['money'];     
       }
       // 超过 1000 克的计算方法 
       $weight = $weight - $shipping_config['first_weight']; // 续重
       $weight = ceil($weight / $shipping_config['second_weight']); // 续重不够取整 
       $freight = $shipping_config['money'] +  $weight * $shipping_config['add_money']; // 首重 + 续重 * 续重费       
       
       return $freight;  
    }



    public function getWXPayData()
    {
        $order_id = (int)$_GET['order_id'];
        //此处需要一个过期检查  2018-6-22
        $og = D('order_goods')->where(array("order_id"=>$order_id))->select();
        foreach($og as $g){
            if($g['prom_type']>0){
                //支付状态
                if($g['goods_num'] > ActivityLogic::getGoodsNum($g['prom_id'], $g['prom_type'], 1)){
                    //库存不足 无法完成支付
                    $return_arr = array('status' => -1, 'msg' => '库存不足!', 'result' => ''); 
                    exit(json_encode($return_arr));
                }
            }
        }
        $data['master_order_sn'] = date('YmdHis').rand(1000,9999);
        D('order')->where(array("order_id"=>$order_id))->save($data);
        $return_arr = array('status' => 1, 'msg' => '成功', 'result' => $this->getWXPayInfo($data['master_order_sn'])); // 返回结果状态
        $return_arr['master_order_sn'] = $data['master_order_sn'];
        //进行一次库存检查
        exit(json_encode($return_arr));
    }
    
    public function getWXPayInfo($order_id)
    {
        $orders = M('order')->where(array("master_order_sn"=>$order_id))->select();
        $goods = M('order_goods')->where(array("order_id"=>$orders[0]['order_id']))->find();
        $orderBody = $goods['goods_name'];
        $tade_no = $order_id;
        
        $user_id = $orders[0]['user_id'];
        $open_id = D('users')->where(array("user_id"=>$user_id))->getField("open_id");

        $total_fee = 0;
        foreach ($orders as $order)
            $total_fee += $order['order_amount'] * 100;
        
        // if($order['pt_price']>0){
        //     $total_fee = $total_fee - $order['pt_price'];
        // }
        
        $response = $this->getPrePayOrder($orderBody, $tade_no, $total_fee,$open_id);

        $x = $this->getOrder($response['prepay_id']);
        //print_r($x);
        
        $data1['wdata'] = $x;
        $data1['pay_money'] = $total_fee;
        
        return $data1;
        //print_r($data1); // 返回新增的订单id
    }
    
    var $config = array(
        'appid' => "wx54a5807b6b2f57b3",    /*微信小程序应用id*/
        'mch_id' => "1341010701",   /*微信申请成功之后邮件中的商户id*/
        'api_key' => "1aidfjasdfjas12381235avmznxvfasd",    /*在微信商户平台上自己设定的api密钥 32位*/
        'notify_url' => 'https://www.renrenjiu.com/index.php/RrjAPI/Pay/wxCallback.html' /*自定义的回调程序地址id*/
    );
    
    public function getPrePayOrder($body, $out_trade_no, $total_fee,$open_id)
    {
        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        $notify_url = $this->config["notify_url"];
    
        $onoce_str = $this->getRandChar(32);
    
        $data["appid"] = $this->config["appid"];
        $data["body"] = $body;
        $data["mch_id"] = $this->config['mch_id'];
        $data["nonce_str"] = $onoce_str;
        $data["notify_url"] = $notify_url;
        $data["out_trade_no"] = $out_trade_no;
        $data["spbill_create_ip"] = $this->get_client_ip();
        $data["total_fee"] = $total_fee;
        $data["trade_type"] = "JSAPI";
        $data["openid"] = $open_id;
        $s = $this->getSign($data, false);
        $data["sign"] = $s;
    
        
        
        $xml = $this->arrayToXml($data);
        
        $response = $this->postXmlCurl($xml, $url);
    //    file_put_contents('./Public/pay.log',var_export($response,true));
        //echo $response;
        // 将微信返回的结果xml转成数组
        return $this->xmlstr_to_array($response);
    }
    
    // 执行第二次签名，才能返回给客户端使用
    public function getOrder($prepayId)
    {
        $data["appId"] = $this->config["appid"];
        $data["nonceStr"] = $this->getRandChar(32);
        $data["package"] = "prepay_id=".$prepayId;
        $data['signType'] = "MD5";
        $data["timeStamp"] = time();
        //$data["partnerid"] = $this->config['mch_id'];
        //$data["prepayid"] = $prepayId;
        
        $s = $this->getSign1($data, false);
        $data["sign"] = $s;
        //$data["prepayid"] = $prepayId;
        return $data;
    }
    
    // 获取指定长度的随机字符串
    function getRandChar($length)
    {
        $str = null;
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol) - 1;
    
        for ($i = 0; $i < $length; $i ++) {
            $str .= $strPol[rand(0, $max)]; // rand($min,$max)生成介于min和max两个数之间的一个随机整数
        }
    
        return $str;
    }
    /*
     * 生成签名
     */
    function getSign($Obj)
    {
        foreach ($Obj as $k => $v) {
            $Parameters[strtolower($k)] = $v;
        }
        // 签名步骤一：按字典序排序参数
        ksort($Parameters);
        $String = $this->formatBizQueryParaMap($Parameters, false);
        // echo "【string】 =".$String."</br>";
        // 签名步骤二：在string后加入KEY
        $String = $String . "&key=" . $this->config['api_key'];
        // echo "<textarea style='width: 50%; height: 150px;'>$String</textarea> <br />";
        // 签名步骤三：MD5加密
        $result_ = strtoupper(md5($String));
        return $result_;
    }
    
    /*
     * 生成签名
     */
    function getSign1($Obj)
    {
        foreach ($Obj as $k => $v) {
            $Parameters[strtolower($k)] = $v;
        }
        // 签名步骤一：按字典序排序参数
        ksort($Parameters);
        //$String = $this->formatBizQueryParaMap($Parameters, false);
        $String  = "appId=".$Obj['appId']."&nonceStr=".$Obj['nonceStr']."&package=".$Obj['package']."&signType=MD5&timeStamp=".$Obj['timeStamp']; 
        
        // echo "【string】 =".$String."</br>";
        // 签名步骤二：在string后加入KEY
        $String = $String . "&key=" . $this->config['api_key'];
        // echo "<textarea style='width: 50%; height: 150px;'>$String</textarea> <br />";
        // 签名步骤三：MD5加密
        //echo $String;
        $result_ = strtoupper(md5($String));
        return $result_;
    }
    
    /*
     * 获取当前服务器的IP
     */
    function get_client_ip()
    {
        if ($_SERVER['REMOTE_ADDR']) {
            $cip = $_SERVER['REMOTE_ADDR'];
        } elseif (getenv("REMOTE_ADDR")) {
            $cip = getenv("REMOTE_ADDR");
        } elseif (getenv("HTTP_CLIENT_IP")) {
            $cip = getenv("HTTP_CLIENT_IP");
        } else {
            $cip = "unknown";
        }
        return $cip;
    }
    
    // 数组转xml
    function arrayToXml($arr)
    {
        
        $xml = "<xml>";
        
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
        }
        $xml .= "</xml>";
        
        
        return $xml;
    }
    
    // post https请求，CURLOPT_POSTFIELDS xml格式
    function postXmlCurl($xml, $url, $second = 30)
    {
        // 初始化curl
        $ch = curl_init();
        // 超时时间
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        // 这里设置代理，如果有的话
        // curl_setopt($ch,CURLOPT_PROXY, '8.8.8.8');
        // curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        // 设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        // 要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        // post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        // 运行curl
        $data = curl_exec($ch);
        
        // 返回结果
        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            echo "curl出错，错误码:$error" . "<br>";
                echo "<a href='http://curl.haxx.se/libcurl/c/libcurl-errors.html'>错误原因查询</a></br>";
                    curl_close($ch);
                    return false;
        }
    }
    
    /**
     * xml转成数组
     */
    function xmlstr_to_array($xmlstr)
    {
        //$doc = new \DOMDocument();
        //$doc->loadXML($xmlstr);
        //return $this->domnode_to_array($doc->documentElement);
        
        //禁止引用外部xml实体
        
        libxml_disable_entity_loader(true);
        
        $xmlstring = simplexml_load_string($xmlstr, 'SimpleXMLElement', LIBXML_NOCDATA);
        
        $val = json_decode(json_encode($xmlstring),true);
        
        return $val;
        
        
    }
    
    // 将数组转成uri字符串
    function formatBizQueryParaMap($paraMap, $urlencode)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if ($urlencode) {
                $v = urlencode($v);
            }
            $buff .= strtolower($k) . "=" . $v . "&";
        }
        $reqPar;
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }
        return $reqPar;
    }
    
    function domnode_to_array($node)
    {
        $output = array();
        switch ($node->nodeType) {
            case XML_CDATA_SECTION_NODE:
            case XML_TEXT_NODE:
                $output = trim($node->textContent);
                break;
            case XML_ELEMENT_NODE:
                for ($i = 0, $m = $node->childNodes->length; $i < $m; $i ++) {
                    $child = $node->childNodes->item($i);
                    $v = $this->domnode_to_array($child);
                    if (isset($child->tagName)) {
                        $t = $child->tagName;
                        if (! isset($output[$t])) {
                            $output[$t] = array();
                        }
                        $output[$t][] = $v;
                    } elseif ($v) {
                        $output = (string) $v;
                    }
                }
                if (is_array($output)) {
                    if ($node->attributes->length) {
                        $a = array();
                        foreach ($node->attributes as $attrName => $attrNode) {
                            $a[$attrName] = (string) $attrNode->value;
                        }
                        $output['@attributes'] = $a;
                    }
                    foreach ($output as $t => $v) {
                        if (is_array($v) && count($v) == 1 && $t != '@attributes') {
                            $output[$t] = $v[0];
                        }
                    }
                }
                break;
        }
        return $output;
    }
    
    private function getAddressStr($addr_info){
        $pro = M('region')->find($addr_info['province']);
        $city = M('region')->find($addr_info['city']);
        $dist = M('region')->find($addr_info['district']);
        return array('pro'=>$pro['name'],'city'=>$city['name'],'dist'=>$dist['name'],'addr'=>$addr_info['address']);
    }
    public function getActListId(){
        $return = ['status' => -1, 'msg' => '活动不存在!', 'result' => '','request'=>$_REQUEST];
        $order_id = $_REQUEST['order_id'];
        $order = M('order')->where(['master_order_sn'=>$order_id])->cache(false)->find();
        if($order){
        //    $order = $order[0];
            $return['status'] = 1;
            $return['msg'] = '活动订单创建成功!';
            $return['result'] = ['activity_list_id'=>$order['activity_list_id'], 'activity_type'=>$order['activity_type']];
        }
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
            $data=M('order')->where($map)->find();
            $formArr=explode(',',$data['form_id']);
            array_unshift($formArr,$form_id);
            $formStr=implode(',',$formArr);
            $data['form_id']=$formStr;
            $res=M('order')->where($map)->save($data);
        }else{
            return false;
        }
    }


}