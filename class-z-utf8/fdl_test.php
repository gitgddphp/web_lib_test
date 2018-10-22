<?php
/*
//获取referer 从a.html来的没有referer参数，而从b.html来的有referer参数
  if(isset($_SERVER['HTTP_REFERER']))
  {
      if(strpos($_SERVER['HTTP_REFERER'],"http://localhost/")==0)//判断$_SERVER['HTTP_REFERER']是不是以http://localhost/开始的
      {
       echo "username:kyx password:123456";
      } else  header("Location:warning.php");//跳转页面到warning.php
   } else header("Location:warning.php");
 */
 if(isset($_SERVER['HTTP_REFERER']))
 {
	echo "用户通过:".$_SERVER['HTTP_REFERER']."访问到本页的!"; 
 }else 
	echo "这是用户的直接访问!";                                                  
	
//可以通过这种方法授权 哪些页面可以访问本页;
?>
