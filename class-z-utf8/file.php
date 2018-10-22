<?php
/* 
	$lines=file('http://php.net/manual/zh/function.file.php',FILE_SKIP_EMPTY_LINES); //获取到的 是 行组成的 数组
    foreach($lines as $line_num=>$line){
	
		echo "Line #<b>{$line_num}</b> : " . htmlspecialchars($line) ."<br>\n";
	
	}

 
$html = implode('', file('http://php.net/manual/zh/function.file.php'));  //将读取到的 html  写入到另外一个字符串末尾
echo $html;  //原样输出 html


$trims=file('somefile.txt',FILE_IGNORE_NEW_LINES); 
foreach($trims as $line_num=>$line){
	
		echo "Line #<b>{$line_num}</b> : " . htmlspecialchars($line) . "<br />\n";
	
*/

/*   
    $mfile = 'somefile.txt';
	$lines=file('http://php.net/manual/zh/function.file.php',FILE_SKIP_EMPTY_LINES); //获取到的 是 行组成的 数组
    foreach($lines as $line_num=>$line){
		$current = file_get_contents($mfile);
		file_put_contents('somefile.txt',$current."Line #<b>{$line_num}</b> : " . htmlspecialchars($line) ."<br>\r");
		echo "Line #<b>{$line_num}</b> : " . htmlspecialchars($line) ."<br> \n";
	
	}
*/	
/********** 二进制流写入文件 **********/
/*
    $fp = fopen("somefile.txt","rb+");
	while (!feof($fp)) {
		$contents .= fread($fp,256);
	}
	
	$lines=file("http://php.net/manual/zh/function.file.php",FILE_SKIP_EMPTY_LINES); //获取到的 是 行组成的 数组
    foreach($lines as $line_num=>$line){
		fwrite($fp,"Line #<b>{$line_num}</b> : " ." htmlspecialchars($line) "."<br>\r\n");
		echo "Line #<b>{$line_num}</b> : " . htmlspecialchars($line) ."<br> \n";
	
	}
	fclose($fp);
/*
$fp = fopen("somefile.txt","w");
fwrite($fp," htmlspec\r\nialchars ");
fclose($fp);
*/
/*************  创建目录  ******************/

/*
mkdir ("./dept1",0700);
*/
/********************* 计算2个路径 的差距 ***************************/
/** 应用范围： 知道当前路径 和 根路径  要创建一个路径 *****/
/*
a a   b x        c m       d tt.php           aa.php
../,../,../
x,m,tt.php
../../../../../x/m/tt.php
*/

/*
   计算从一个路径 到另外一个路径 的相对路径 计算;
   使用环境:当我们知道 2个 文件相对与 根路径 的位置时;但我们
   又不知道根目录的 绝对路径;
   就可以 获取到  当前位置到目标位置 的相对路径;

*/
$a='/c/d/c/aa.php';$b='/a/b/c/tt.php';
function getRelPath($a,$b){//计算$a 到 $b 的相对路径;
	$a_arr=explode('/',$a);
	$b_arr=explode('/',$b);
	$c1=array();
	$c2=array();
	$flag=1;
	if($a_arr[0]!=$b_arr[0]){//判断他们是否 是同一个 根目录
		return false;
	}
	for($i=0;$i<(count($a_arr)>count($b_arr)?count($a_arr):count($b_arr));$i++){


			
		if(isset($a_arr[$i])&&isset($b_arr[$i])){
			if($a_arr[$i]!=$b_arr[$i]){
				$c1[]='../';
				$c2[]=$b_arr[$i];
				$flag=0;
			}else{
			    if($flag==0){
					$c1[]='../';
					$c2[]=$b_arr[$i];
					$flag=0;
				}else  $flag=1;
			}
		} 
		if(isset($a_arr[$i])&&!isset($b_arr[$i])){
			$c1[]='../';
		}
		if(!isset($a_arr[$i])&&isset($b_arr[$i])){
				$c2[]=$b_arr[$i];
		}
	}
	return array_merge($c1,$c2);
}
$c=getRelPath($a,$b);

 print_r($c);
/*关于文件操作流程*/
 


 $mfile ='somefile.txt';
 $isexist=file_exists ($mfile);
 if(!$isexist){
	echo "文件不存在!<br>";
 }
 echo "上次修改时间".filemtime($mfile);
 $fp = fopen($mfile,"r");
 //fwrite($fp," htmlspec\r\n ialchars ");
 fclose($fp);
 
 echo "当前脚本最后修改时间".getlastmod();
 
 
 echo "当前使用zend引擎版本".zend_version ();
 /*
 $lines=file('http://php.net/manual/zh/function.file.php',FILE_SKIP_EMPTY_LINES); //获取到的 是 行组成的 数组
 foreach($lines as $line_num=>$line){
	$current = file_get_contents($mfile);
	file_put_contents('somefile.txt',$current."Line #<b>{$line_num}</b> : " . htmlspecialchars($line) ."\r\n");
	echo "Line #<b>{$line_num}</b> : " . htmlspecialchars($line) ."<br> \n";
	
 }

 echo "c盘可用空间:".disk_free_space('c://');
 */
 
 
 
 
 /****  用于生产静态 html 文件 ****
 
     使用环境：比如 当修改了一个 文档 模板后 就需要判断   模板上次修改时间  和 文档上次修改时间  如果 文档时间>模板时间 就不用更新  否则就需要 更新;
 
 ***/
    
 class html{
	public $fileName,$contents,$fileTime;
	
	public function upDateFile($fileName,$contents,$time=null){//用于更新文档  time  一般 为时间 在此时间 之前的都被跟新 可以是 数组  0为 开始时间  1为 结束时间 更新 在 这个时间段内的文档  空的话就是完全更新
		
	
	}
	
	public function deleteFile(){//用于删除文档
	
	
	
	}

    public function createFile(){//用于创建文档
	
	
	}    
	
 
 }
?>
