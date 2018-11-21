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
namespace Jar\Services;
class WxApi{
     private $cfg = array(
          'appid' => "wx54a5807b6b2f57b3",    /*微信小程序应用id*/
          'mch_id' => "1518211091",   /*微信申请成功之后邮件中的商户id*/
          'api_key' => "1aidfjasdfjas1b2dk3kravmznxv1405",    /*在微信商户平台上自己设定的api密钥 32位*/
          'notify_url' => 'https://www.renrenjiu.com/index.php/RrjAPI/Pay/wxCallback.html' /*自定义的回调程序地址id*/
      );
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
    function searchOrder(){
      $order = array(
        'order_sn'=>'201805091700508783',
        'transaction_id'=>'4200000126201805096006936360'
        );
       $nonce_str = $this->getRandChar(32);

       $ref= strtoupper(md5("appid={$this->cfg['appid']}&mch_id={$this->cfg['mch_id']}&nonce_str=$nonce_str"
          . "&out_trade_no={$order['order_sn']}&transaction_id={$order['transaction_id']}&key={$this->cfg['api_key']}"));//sign加密MD5

       $refund=array(
          'appid'=>$this->cfg['appid'],//应用ID，固定
          'mch_id'=>$this->cfg['mch_id'],//商户号，固定
          'nonce_str'=>$nonce_str,//随机字符串
          'out_trade_no'=>$order['order_sn'],//商户订单号,pay_sn码 1.1二选一,微信生成的订单号，在支付通知中有返回
          'transaction_id'=>$order['transaction_id'],//微信订单号 1.2二选一,商户侧传给微信的订单号
          'sign'=>$ref//签名
       );

       $url="https://api.mch.weixin.qq.com/pay/orderquery";;//微信退款地址，post请求
       $xml=$this->arrayToXml($refund);
       $info = $this->postXmlCurl($xml, $url, true, 30);
    //   print_r($info);

    }
    /*
    请确保您的libcurl版本是否支持双向认证，版本高于7.20.1
    */
    function refundOrder($order = array()){
      // $order = array(
      //   'order_sn'=>'201805091700508783',
      //   'transaction_id'=>'4200000126201805096006936360',
      //   'total_amount'=>1
      //   );
       $nonce_str = $this->getRandChar(32);
       // $ref= strtoupper(md5("appid={$this->cfg['appid']}&mch_id={$this->cfg['mch_id']}&nonce_str=$nonce_str&op_user_id=616132"
       //    . "&out_refund_no={$order['order_sn']}&out_trade_no={$order['order_sn']}&refund_fee={$order['total_amount']}"
       //    . "&total_fee={$order['total_amount']}&key={$this->cfg['api_key']}"));//sign加密MD5
       //&transaction_id={$order['transaction_id']}
        $sign_arr = array(  
          'appid'=>$this->cfg['appid'],//应用ID，固定  
          'mch_id'=>$this->cfg['mch_id'],//商户号，固定  
          'nonce_str'=>$nonce_str,//随机字符串  
          'op_user_id'=>'616132',//操作员  
          'out_refund_no'=>$order['master_order_sn'],//商户内部唯一退款单号  
          'out_trade_no'=>$order['master_order_sn'],//商户订单号,pay_sn码 1.1二选一,微信生成的订单号，在支付通知中有返回  
        //  'transaction_id'=>$order['transaction_id'],//微信订单号 1.2二选一,商户侧传给微信的订单号  
          'refund_fee'=>round($order['order_amount']*100),//退款金额  单个订单
          'total_fee'=>round($order['total_amount']*100),//总金额  总订单金额
        );
        $sign = $this->MakeSign($sign_arr,$this->cfg['api_key']);
        $refund=$sign_arr;
        $refund['sign'] = $sign;

       $url="https://api.mch.weixin.qq.com/secapi/pay/refund";;//微信退款地址，post请求
       $xml=$this->arrayToXml($refund);
       $xml = $this->postXmlCurl($xml, $url, true, 30);
//       var_dump($this->xmlToArray($xml));
    //   $this->checkReturn($arr,$refund);
//       \RrjAPI\Lib\Log::write_log($sign_arr);//返回信息写入记事本
       return $this->xmlToArray($xml);
    }
    //返回数据验证
    private function checkReturn($arr,$old){
      if($arr['appid']!=$old['appid'])
        return false;
      if($arr['nonce_str']!=$old['nonce_str'])
        return false;
      if($arr['mch_id']!=$old['mch_id'])
        return false;
      if($arr['sign']!=$old['sign'])
        return false;
      return true;
    }

  /**
   * 以post方式提交xml到对应的接口url
   * 
   * @param string $xml  需要post的xml数据
   * @param string $url  url
   * @param bool $useCert 是否需要证书，默认不需要
   * @param int $second   url执行超时时间，默认30s
   * @throws WxPayException
   */
    private static function postXmlCurl($xml, $url, $useCert = false, $second = 30)
    {   
      $ch = curl_init();
      //设置超时
      curl_setopt($ch, CURLOPT_TIMEOUT, $second);
      //如果有配置代理这里就设置代理
      // if(WxPayConfig::CURL_PROXY_HOST != "0.0.0.0" 
      //   && WxPayConfig::CURL_PROXY_PORT != 0){
      //   curl_setopt($ch,CURLOPT_PROXY, WxPayConfig::CURL_PROXY_HOST);
      //   curl_setopt($ch,CURLOPT_PROXYPORT, WxPayConfig::CURL_PROXY_PORT);
      // }
      curl_setopt($ch,CURLOPT_URL, $url);
      curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,TRUE);
      curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,1);//严格校验
      //设置header
      curl_setopt($ch, CURLOPT_HEADER, FALSE);
      //要求结果为字符串且输出到屏幕上
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    
      if($useCert == true){
        //设置证书
        //使用证书：cert 与 key 分别属于两个.pem文件
        curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
        curl_setopt($ch,CURLOPT_SSLCERT, '/var/www/html/xcx.yjtc.com/src/cert/apiclient_cert.pem');
        curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
        curl_setopt($ch,CURLOPT_SSLKEY, '/var/www/html/xcx.yjtc.com/src/cert/apiclient_key.pem');
      }
      //post提交方式
      curl_setopt($ch, CURLOPT_POST, TRUE);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
      //运行curl
      $data = curl_exec($ch);
//      var_dump($data);
      //返回结果
      if($data){
        curl_close($ch);
        return $data;
      } else { 
        $error = curl_errno($ch);
//        var_dump($error);
        curl_close($ch);
        return $error;
      //  throw new WxPayException("curl出错，错误码:$error");
      }
    }

    function arrayToXml($arr){
       $xml = "<root>";
       foreach ($arr as $key=>$val){
          if(is_array($val)){
             $xml.="<".$key.">".$this->arrayToXml($val)."</".$key.">";
          }else{
             $xml.="<".$key.">".$val."</".$key.">";
          }
       }
       $xml.="</root>";

       return $xml ;
    }

    /**
     * 格式化参数格式化成url参数
     */
    public function ToUrlParams($arr)
    {
      $buff = "";
      foreach ($arr as $k => $v)
      {
        if($k != "sign" && $v != "" && !is_array($v)){
          $buff .= $k . "=" . $v . "&";
        }
      }

      $buff = trim($buff, "&");
      return $buff;
    }
    /**
     * 生成签名
     * @return 签名，本函数不覆盖sign成员变量，如要设置签名需要调用SetSign方法赋值
     */
    public function MakeSign($arr,$key)
    {
      //签名步骤一：按字典序排序参数
      ksort($arr);
      $string = $this->ToUrlParams($arr);
      //签名步骤二：在string后加入KEY
      $string = $string . "&key=$key";
      //签名步骤三：MD5加密
      $string = md5($string);
      //签名步骤四：所有字符转为大写
      $result = strtoupper($string);
      return $result;
    }
    public function xmlToArray($xml)
    {
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);        
        return $values;
    }
}