<?php
namespace Jar\Services;

use Jar\Exception\TransException;
use Jar\Facades\JarTicket;
use Jar\Facades\Role;
use Jar\Facades\UserAddress;
use Jar\Facades\Jar;
use Jar\Facades\OrderGoods;

class PayService extends Service {

    public function __construct(){
        parent::__construct();
        $this->model = D('Order');
    }

    public function getWXPayData(Int $orderId)
    {
        if(!$orderId){ self::$errors = '订单不能为空!'; return false; }
        $data['master_order_sn'] = date('YmdHis').rand(1000,9999);
        $res = D('order')->where(array("order_id"=>$orderId))->save($data);
        if(!$res){ self::$errors = '订单不存在!'; return false; }
        $return_arr = ['result' => $this->getWXPayInfo($data['master_order_sn'])]; // 返回结果状态
        $return_arr['master_order_sn'] = $data['master_order_sn'];
        //进行一次库存检查
        return $return_arr;
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

        $response = $this->getPrePayOrder($orderBody, $tade_no, $total_fee,$open_id);
        
        $x = $this->getOrder($response['prepay_id']);

        $data1['wdata'] = $x;
        $data1['pay_money'] = $total_fee;
        
        return $data1;
    }
    
    var $config = array(
        'appid' => "wx54a5807b6b2f57b3",    /*微信小程序应用id*/
        'mch_id' => "1518211091",   /*微信申请成功之后邮件中的商户id*/
        'api_key' => "1aidfjasdfjas1b2dk3kravmznxv1405",    /*在微信商户平台上自己设定的api密钥 32位*/
        'notify_url' => 'https://xcx.yijiutiancheng.com/index.php/Jar/Pay/wxCallback.html' /*自定义的回调程序地址id*/
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