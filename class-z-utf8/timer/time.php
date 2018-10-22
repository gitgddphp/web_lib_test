<?php

$gdd_stime=microtime(true);//运行时间测试
echo "fffffff";

$gdd_etime=microtime(true);//获取程序执行结束的时间  
$total=$gdd_etime-$gdd_stime;   //计算差值  

$str_total = var_export($total, TRUE);  
	if(substr_count($str_total,"E")){  
		$float_total = floatval(substr($str_total,5));  
		$total = $float_total/100000;  
	}  
	echo "$total".'秒';  

?>
