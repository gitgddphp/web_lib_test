<?php
	$userBrowser["HTTP_USER_AGENT"]=$_SERVER["HTTP_USER_AGENT"];
	$userBrowser["HTTP_ACCEPT_CHARSET"]=$_SERVER["HTTP_ACCEPT_CHARSET"];
	$userBrowser["REQUEST_METHOD"]=$_SERVER["REQUEST_METHOD"];
	$userBrowser["HTTP_REFERER"]=$_SERVER["HTTP_REFERER"];//提出请求的页面
	$userBrowser["REQUEST_URI"]=$_SERVER["REQUEST_URI"];//响应请求的页面
	$userBrowser["REQUEST_TIME"]=$_SERVER["REQUEST_TIME"];//响应请求的时间
	$userBrowser["CONTENT_TYPE"]=$_SERVER["CONTENT_TYPE"];//数据格式
	
	switch($userBrowser["REQUEST_METHOD"]){
		case 'GET':$userBrowser["REQUEST_VALUES"]=$_GET;break;
		case 'POST':$userBrowser["REQUEST_VALUES"]=$_POST;break;
	}
	echo $_SERVER['REMOTE_ADDR'];

//	echo json_encode(getenv('REMOTE_ADDR'));//转换为json 方便浏览器端js进行数据处理

?>