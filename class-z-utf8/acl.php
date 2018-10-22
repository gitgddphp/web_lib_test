<?php
    class role{//角色类
		public $_name;  //角色名称
		public $_group;  //角色 分组
		public $_id;
		
	
	}
	
	class user{
		public $_id;
		public $_role;
		public $_groups;
	}
	
	class privilege{//权利数组
		public $_prower_id;
		public $_controller,$_action,$_module,$_values;
	    
	}
	
	class mresource{//资源类
		public $_name,$_type,$_fileName,$_id;
	
	}
	
	class MyResource{
		public $resource_arr;
		function __construct($resource_arr){
			$this->resource_arr;
		}
		function getResourceType(){//获取资源类型： 包括(文档资源（文档属性）、)
			$type=0;
		
			return $type
		}
		
	
	}

	class acl{//控制列表类
		public $_role,$_resources,$_privilege,$acl_arr;
		
	
		function isAllowed($resource,$uid){//判断 资源 是否 可以被 该 用户 访问
			
			return false;
		}
		function check($resource,$uid,$url){//判断失败 将返回指定的 url
			
			return $url;
		}
	}


?>
