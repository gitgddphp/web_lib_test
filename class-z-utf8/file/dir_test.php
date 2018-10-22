<?php
//	$aa=scandir("../");
//	print_r($aa);
	$path_parts = pathinfo(__FILE__);
	echo $path_parts["dirname"]."<br>";
	$c=glob($path_parts["dirname"].'\*');
	echo $c[1];
	echo "<br />".mime_content_type($c[1]);
	echo filesize($path_parts["dirname"]);
//	tempnam ( $path_parts["dirname"],'aaa.php');
//	echo disk_total_space($path_parts["dirname"]);

?>
