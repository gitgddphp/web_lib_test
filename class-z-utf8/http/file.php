<?php
/* 
	$lines=file('http://php.net/manual/zh/function.file.php',FILE_SKIP_EMPTY_LINES); //获取到的 是 行组成的 数组
    foreach($lines as $line_num=>$line){
	
		echo "Line #<b>{$line_num}</b> : " . htmlspecialchars($line) ."<br>\n";
	
	}
*/
header('Content-Type:text/html;Charset=utf-8;');

$html = implode('', file('http://127.0.0.1/myad/?m=admin&c=Article&a=index',FILE_SKIP_EMPTY_LINES));

//$arr=preg_split ("/\<(\/)*title[\w\d\'\"\=\-\;\!\:\/\.\ \#\(\)\%]*\>/", $html );
//eregi("<div([^>]*)>(.*)</div>a","<div>ddd</div>a<div>ddd</div>",$arr);
preg_match_all ("|<[^>]+>(.*)</[^>]+>|U",
    "<b>example: </b><div align=left>this is a <div>test</div></div><div>test</div>",
    $arr, PREG_SET_ORDER);

//eregi("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})",'2014-37-83', $arr);
//$arr=preg_split ("/\</title(\/)*[\w\d\'\"\=\-\;\!\:\/\.\ \#\(\)\%]*\>/", $html );
//$arr=preg_split ('<p([^>]*)>(.*)</p>[|]', $html );
print_r($arr);



?>
