<?php
function arr_add_pre($arr,$pre){
	foreach($arr as $k=>$v){
		$arr[$k] = $pre.$v;
	}
	return $arr;
}
class html{
	static $css_path=array('css/'),$js_path=array('');
	static $css=array('common.css','public.css');
	static $js=array();
	static function css($path){
		header( 'Content-type: text/css;charset=utf-8' );
		$list = explode(',',$path);
		$list = arr_add_pre($list,self::$css_path[0]);
		foreach( glob( self::$css_path[0].'*.css' ) as $file ){
			if(in_array($file,$list)){
				include( $file );
				echo "\n";
			}

		}
	}
	static function js(){
		
	}
}
html::css('common.css,public.css');
?>
