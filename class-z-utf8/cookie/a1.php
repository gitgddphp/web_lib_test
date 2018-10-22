<?php

session_start();
$_SESSION['AA']="aa";
echo session_name();
echo $_COOKIE['PHPSESSID'];
  print_r($_COOKIE);

?>
