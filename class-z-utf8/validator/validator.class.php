<?php
	class validator{
	
		function __construct(){
		
		
		}
		public static function isPhoneNumber($text){
			$isMob="/^1[3-5,8]{1}[0-9]{9}$/";
			$isTel="/^([0-9]{3,4}-)?[0-9]{7,8}$/";
			if(!preg_match($isMob,$text) && !preg_match($isTel,$text))
			{
			  return false;
			} 
			return true;
		}
		
		public static function isEmpty($text){
			if($text=='' || $text==null)
			{
			  return false;
			} 
			return true;
		}
		
		public static function isEmail($text){
			$isEmail="^([w-.]+)@(([[0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3}.)|(([w-]+.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(]?)$";
			if(!preg_match($isEmail,$text) && !preg_match($isEmail,$text))
			{
			  return false;
			} 
			return true;
		}
		public static function isPost($text){
			$isPost="^[1-9]\d{5}$";
			if(!preg_match($isPost,$text) && !preg_match($isPost,$text))
			{
			  return false;
			} 
			return true;
		}
		
		public static function checkdata($data,$validateConfig){
			$keys=array_keys($data);
			$bool=validator::$validateConfig[$keys[0]]($data[$keys[0]]);
			$validateInfos=array();
			
			$validateInfos[$keys[0]]=$bool;
			return $validateInfos;
		}
		
	}
//	validator::isPhoneNumber('028-83288599');
	
	$validator1=array('aa'=>'isPhoneNumber','cc'=>'isPhoneNumber');
	
	$data=array('aa'=>'028-83288599','cc'=>'cccccc');
	
	function checkdata($data,$validateConfig){
		$keys=array_keys($data);
//		$vali=new validator();
		$bool=validator::$validateConfig[$keys[0]]($data[$keys[0]]);
		$validateInfos=array();
		
		$validateInfos[$keys[0]]=$bool;
		return $validateInfos;
	}
	
	$info=validator::checkdata($data,$validator1);
	echo "验证结果输出:<br>";
	print_r($info);
	
?>
