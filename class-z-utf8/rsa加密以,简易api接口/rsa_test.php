<?php

function test_rsa(){
	$privateKeyFilePath = 'rsa_private_key.pem'; 
	$publicKeyFilePath = 'rsa_public_key.pem'; 

	extension_loaded('openssl') or die('php需要openssl扩展支持'); 

	(file_exists($privateKeyFilePath) && file_exists($publicKeyFilePath)) or die('文件路径不正确'); 

	$privateKey = openssl_pkey_get_private(file_get_contents($privateKeyFilePath)); 

	$publicKey = openssl_pkey_get_public(file_get_contents($publicKeyFilePath)); 

	($privateKey && $publicKey) or die('密钥或者公钥不可用'); 

	// 加密数据 
	$originalData = '我的帐号是:toutiao,密码是:123456'; 

	// 加密以后的数据

	$encryptData = ''; 

	echo '原数据为:', $originalData, PHP_EOL; 

	///////////////////////////////用私钥加密//////////////////////// 

	if (openssl_private_encrypt($originalData, $encryptData, $privateKey)) { 

	// 加密后 可以base64_encode后方便在网址中传输 

	echo '加密成功，加密后数据(base64_encode后)为:', base64_encode($encryptData), PHP_EOL; 

	} else { 

	exit('加密失败');

	}

	///////////////////////////////用公钥解密//////////////////////// 

	//解密以后的数据 

	$decryptData =''; 

	if (openssl_public_decrypt($encryptData, $decryptData, $publicKey)) { 

	echo '解密成功，解密后数据为:', $decryptData, PHP_EOL; 

	} else { 

	exit('解密成功'); 

	}
}


//电商加密私钥，快递鸟提供，注意保管，不要泄漏
defined('AppKey') or define('AppKey', '7f583a74-ea0e-4f12-957a-21c45c6a10d9');
//测试请求url
defined('ReqURL') or define('ReqURL', 'http://test2.in/rsa_test.php');

$userApi = new UserApi();
$request = $userApi->getRequest();

if(count($request)){
	if($userApi->checkSign(urlencode($request['DataSign']), $request['RequestData'])){
		$data = ['status'=>1,'msg'=>'成功!'];
		//执行后续请求操作
	}else{
		$data = ['status'=>0,'msg'=>'签名验证失败!'];
	}
	exit(json_encode($data));
}else{
	$userApi->getUserInfo();	
}


class UserApi{
	function getRequest(){
		return $_POST;
	}
	/**
	 * Json方式  物流信息订阅
	 */
	function getUserInfo($type=0){
		$requestData=[
			'user_id'=>5,'act'=>'getOrderData','time'=>time(),'sort'=>mt_rand(1,5000)
			];
		$requestData = json_encode($requestData);
		
		$datas = array(
	        'RequestType' => '1002',
	        'RequestData' => urlencode($requestData),
	        'DataType' => '2',
	    );
	    $datas['DataSign'] = $this->encrypt($requestData, AppKey);

		$result=$this->sendPost(ReqURL, $datas);	
		echo '返回信息:<br>';
		var_dump($result);
		exit;
		//根据公司业务处理返回的信息......
		
		return $result;
	}

	/**
	 *  post提交数据 
	 * @param  string $url 请求Url
	 * @param  array $datas 提交的数据 
	 * @return url响应返回的html
	 */
	function sendPost($url, $datas) {
	    $temps = array();	
	    foreach ($datas as $key => $value) {
	        $temps[] = sprintf('%s=%s', $key, $value);		
	    }	
	    $post_data = implode('&', $temps);
	    $url_info = parse_url($url);
		if(empty($url_info['port']))
		{
			$url_info['port']=80;	
		}
	    $httpheader = "POST " . $url_info['path'] . " HTTP/1.0\r\n";
	    $httpheader.= "Host:" . $url_info['host'] . "\r\n";
	    $httpheader.= "Content-Type:application/x-www-form-urlencoded\r\n";
	    $httpheader.= "Content-Length:" . strlen($post_data) . "\r\n";
	    $httpheader.= "Connection:close\r\n\r\n";
	    $httpheader.= $post_data;
	    $fd = fsockopen($url_info['host'], $url_info['port']);
	    fwrite($fd, $httpheader);
	    $gets = "";
		$headerFlag = true;
		while (!feof($fd)) {
			if (($header = @fgets($fd)) && ($header == "\r\n" || $header == "\n")) {
				break;
			}
		}
	    while (!feof($fd)) {
			$gets.= fread($fd, 128);
	    }
	    fclose($fd);  
	    
	    return $gets;
	}

	/**
	 * 电商Sign签名生成
	 * @param data 内容   
	 * @param appkey Appkey
	 * @return DataSign签名
	 */
	function encrypt($data, $appkey) {
	    return urlencode(base64_encode(md5($data.$appkey)));
	}
	/**
	 * 验签
	 */
	function checkSign($sign, $data){
		if($this->encrypt($data, AppKey) == $sign)
			return true;
		else
			return false;
	}
}