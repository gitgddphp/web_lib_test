<?php

	$hook['pre_controller'] = array(
                                'class'    => 'MyClass',
                                'function' => 'Myfunction',
                                'filename' => 'Myclass.php',
                                'filepath' => 'hooks',
                                'params'   => array('beer', 'wine', 'snacks')
                                );
	
	
	function Router($url){
		//解析 分割 url
		$controller="";
		$action="";
	}
	
	function hook(){
		
		
	}
	
	

?>
