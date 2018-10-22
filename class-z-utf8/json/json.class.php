<?php
	class jsonHelper{
		function __construct(){
		
		}
		
		function array_DFS($arr){
			
		}
		
		function get_depth($arr){ //数组深度
			$max_depth=1;
			foreach($arr as $myarr){
				if(is_array($myarr)){
					$now_depth=$this->get_depth($myarr)+1;
				}
				if($now_depth>$max_depth)
					$max_depth=$now_depth;
			}
			return $max_depth;
		}
		
		function jsonToArray($str){ //json  转换为 数组
			/**
				{'aa':bb,cc:[{cc:dd}],xx:mm}  'aa':bb,cc:[{cc:dd}],xx:mm      
			**/
//			echo "<br>".$str;
			
			$tem_str=preg_replace('/^\[/','',$str);  $tem_str=preg_replace('/^{/','',$tem_str);
			$tem_str=preg_replace('/\]$/','',$tem_str);  $tem_str=preg_replace('/}$/','',$tem_str);		//去掉 json 外层 格式 {} []
			
//			preg_replace('/http\:\/\/www\.jb51\.net\//','http://e.jb51.net/w3c/',$weigeti);
			$marr=explode(',',$tem_str);			//以  ，  符  初步划分 数组
//			echo "<br>";
//			print_r($marr);
			$xmarr;									
			$tem_i=0;
			$comb_boor_arr=array();
			$flag_comb_count=0;
			for($i=0;$i<count($marr);$i++){
				$comb_boor_arr[$i]=0;
				if(strpos($marr[$i],'[',0)>0)
					$comb_boor_arr[$i]=2;
				if(strpos($marr[$i],']',0)>0){
					$comb_boor_arr[$i]=4;
				}
			}
//			print_r($comb_boor_arr);
//			echo "<br>";
			for($i=0;$i<count($marr);$i++){	
				if($comb_boor_arr[$i]==2){	
					$x_4=0;
					$x_2=1;
					for($n=$i+1;$n<count($marr);$n++){
						
						$marr[$i].=','.$marr[$n];
						$x_4++;
						if($comb_boor_arr[$n]==2){
							$mi=substr_count($marr[$n], '[');
							$x_2+=$mi;
						}
						if($comb_boor_arr[$n]==4){
							$mi=substr_count($marr[$n], ']');
							$x_2-=$mi;
							if($x_2==0)
								break;
						}
						
						
					}
				
					for($n=$i+1;$n<count($marr);$n++){
						$marr[$n]=$marr[$n+$x_4];
						$comb_boor_arr[$n]=$comb_boor_arr[$n+$x_4];
					}
					
					$index_i=stripos($marr[$i],':',0);
					
					$key=substr($marr[$i],0,$index_i);
					
//					echo '<br>'.substr($marr[$i],$index_i+1,strlen($marr[$i])-$index_i);
//					$xmarr[$key]=substr($marr[$i],$index_i+1,strlen($marr[$i])-$index_i);
					$xmarr[$key]=$this->jsonToArray(substr($marr[$i],$index_i+1,strlen($marr[$i])-$index_i));
				}else{
					if(isset($marr[$i])){
						$tem_xarr=explode(':',$marr[$i]);
						$xmarr[$tem_xarr[0]]=$tem_xarr[1];
					}
				}
			}	
			return $xmarr;
			
		}
		
		function arrayToJson($arr){ //数组转换为 json
			$json_text="{";
			foreach($arr as $name=>$value){
				if(is_array($value)){
					$json_text.=$name.":[".$this->arrayToJson($value)."],";
				}else{
					$json_text.=$name.":'".$value."',";
				}
			}
			return rtrim ($json_text,',')."}";
		}

	}
	$arr=array(array('ax'=>'q','m'),array('w',array('x','m'),'y'),'e');
	$a=array_keys($arr);
	print_r($a);
	
	$helper=new jsonHelper();         
	
	echo $helper->get_depth($arr);    //获取 数组深度
	
	echo $helper->arrayToJson($arr);  //数组转换为 json 数据
	
	echo "<br>";
	//json 转换为  PHP 数组 函数
	print_r($helper->jsonToArray('{a1:dd,e1:[{b1:de,c1:[{b1:de,c1:dd}],j1:[{e1:de,f1:dd}]}],x1:[{b1:de,c1:[{e1:de,f1:dd}]}],z1:[{b1c:dde,cs1:ddd}],d1:aa}'));
	
/*  {a1:dd,e1:[{b1:de,c1:dd}],d1:aa}	
	if({)
	&$x['a1']='dd';
	
*/
	echo "<br>------->";
	
//	$m=preg_replace('/\]$/','','[{net.jb51.net}]}]');
//	echo $m;
//	$m=preg_replace('/{$/','',$m);

//	print_r(jtest('{a1:dd,e1:[{b1:de,c1:dd}],d1:aa}'));
	function jtest($a){
		$tem_str=trim($a,'[{}]');
//		echo '<br>'.$tem_str;
		$marr=explode(',',$tem_str);
		$xmarr;
		$tem_i=0;
		$comb_boor_arr=array();
		$flag_comb_count=0;
		for($i=0;$i<count($marr);$i++){
			$comb_boor_arr[$i]=0;
			if(strpos($marr[$i],'[',0)>0)
				$comb_boor_arr[$i]=2;
			if(strpos($marr[$i],']',0)>0)
				$comb_boor_arr[$i]=4;
		}
//		print_r($comb_boor_arr);
//		echo count($marr);
//		echo "<br>";
		for($i=0;$i<count($marr);$i++){	
//			echo $i.':'.$comb_boor_arr[$i];
			if($comb_boor_arr[$i]==2){
//				echo $i;
				$marr[$i].=','.$marr[$i+1];
				$index_i=stripos($marr[$i],':',0);
				$key=substr($marr[$i],0,$index_i);
				
				$xmarr[$key]=jtest(substr($marr[$i],$index_i+1,strlen($marr[$i])-$index_i));
				
				//$xmarr[$key]=jtest(substr($marr[$i],stripos($marr[$i],':',0)));
				
				for($n=$i+1;$n<count($marr);$n++){
//					echo "<br>";
					$marr[$n]=$marr[$n+1];
				}
			}else{
				if(isset($marr[$i])){
					$tem_xarr=explode(':',$marr[$i]);
					$xmarr[$tem_xarr[0]]=$tem_xarr[1];
				}
			}
		}	
//		print_r($xmarr);
		return $xmarr;
		/*
		foreach($marr as $mx){
			$tem_mx_ii=explode(':',$mx);
			$tem_mx[0]=$tem_mx_ii[0];
			$find_str_position=strpos($mx,':',0);
			$tem_mx[1]=substr($tem_mx,$find_str_position);
			$e[$tem_mx[0]]=count(explode('[',$tem_mx[1]))>1?jtest($tem_mx[1]):$tem_mx[1];
		}
		print_r($e);
		return $e;
		*/
	}
	
	
	
?>
