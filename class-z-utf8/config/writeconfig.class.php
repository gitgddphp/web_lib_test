<?php
/********  参数设置类 *******/
$arr=array(1,"fff",3,4,5);
file_put_contents('config.php',"<?php\n return ".var_export($arr,true)."\n?>");
//echo ;
?>