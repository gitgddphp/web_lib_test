<?php
	/*****权限 控制 函数 测试*/
	function controller($handle,$powers,$group,$user,$args=null){//$handle 为 操作符  $powers 为权力数组   $user 为操作 用户
		$userGroup=$user['group'];
		$userPowers;
		foreach($group as $arr){
			if($arr['id']==$userGroup){
				$userPowers=$arr['powers'];
				break;
			}
		}
		$userPowers=explode(',',$userPowers);
		$handle_powerId;
		foreach($powers as $arr){
			if($arr['handle']==$handle){
				$handle_powerId=$arr['id'];
				break;
			}
		}
		if(in_array($handle_powerId,$userPowers)){
			echo "<br>".$user['name']."具有操作->".$handle."的权限!";
			call_user_func_array($handle,$args);
		}else
			echo "<br>".$user['name']."没有操作->".$handle."的权限!";
	}
	
	function aa(){
		echo "执行了 aa  函数!";
	}
	function bb(){
		echo "执行了 bb  函数!";
	}
	
	$handle='bb';
	
	$powers=array(array('id'=>1,'handle'=>'aa','other'=>1),array('id'=>2,'handle'=>'bb','other'=>1));
	
	$group=array(array('id'=>1,'powers'=>'1,7,6,4'),array('id'=>2,'powers'=>'2,9,3,4'));
	
	$user=array('name'=>'myname','group'=>2);
	
	controller($handle,$powers,$group,$user,$args);


	/************在加载 菜单时判断  哪些 菜单 是 用户 具有 的 权限 才显示出来 ************/
	/*** 菜单默认数组: array(array('id'=>1,'handle'=>'aa','name'=>'first','next'->2,'father'=>0,'first_son'=>1),array('id'=>1,'handle'=>'aa','name'=>'cx','next'->0,'father'=>1));
		if handle in  user_powers   item['display']=true;
	***/
    /*****	
		onDisplayMenu()
		displayMenu() 
		  if Item[i]['father']==0 {  Menu[]=Item[i]     }
		
		getSon($father_id,$menus,$mm){
			if($menus[i]['father']==$father_id){
				$mm[]=$menus[i];			
			}
			
			
			if next=0 return $mm;
			
		}
			
	**/
	
	
	
	

?>