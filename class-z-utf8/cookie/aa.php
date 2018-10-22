<?php
  session_id($_COOKIE['PHPSESSID']);
  session_start();
  print_r($_SESSION);
  $value = 'something from somewhere';
  setcookie("TestCookie", $value, time()+60);//变量续时 工具
  print_r($_COOKIE);
?>
