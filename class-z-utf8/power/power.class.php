<?php
	/*****Ȩ�� ���� ���� ����*/
	function controller($handle,$powers,$group,$user,$args=null){//$handle Ϊ ������  $powers ΪȨ������   $user Ϊ���� �û�
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
			echo "<br>".$user['name']."���в���->".$handle."��Ȩ��!";
			call_user_func_array($handle,$args);
		}else
			echo "<br>".$user['name']."û�в���->".$handle."��Ȩ��!";
	}
	
	function aa(){
		echo "ִ���� aa  ����!";
	}
	function bb(){
		echo "ִ���� bb  ����!";
	}
	
	$handle='bb';
	
	$powers=array(array('id'=>1,'handle'=>'aa','other'=>1),array('id'=>2,'handle'=>'bb','other'=>1));
	
	$group=array(array('id'=>1,'powers'=>'1,7,6,4'),array('id'=>2,'powers'=>'2,9,3,4'));
	
	$user=array('name'=>'myname','group'=>2);
	
	controller($handle,$powers,$group,$user,$args);


	/************�ڼ��� �˵�ʱ�ж�  ��Щ �˵� �� �û� ���� �� Ȩ�� ����ʾ���� ************/
	/*** �˵�Ĭ������: array(array('id'=>1,'handle'=>'aa','name'=>'first','next'->2,'father'=>0,'first_son'=>1),array('id'=>1,'handle'=>'aa','name'=>'cx','next'->0,'father'=>1));
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