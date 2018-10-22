<?php

  $m=glob("../zt/**");
  foreach($m as $a){
	echo ltrim($a,"../zt/")."<br>";
  }
  

?>
