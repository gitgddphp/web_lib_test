<?php
	class gddFile{
		function __construct(){
		/**/
		
		}
		
		static function dir_list($dir=''){
			/*遍历下级目录*/
			if(empty($dir)){
				$dir = getcwd();
			}
			$mydir = dir($dir);
			$tree_list = array();
			$i = 0;
			while($file = $mydir->read()){
				if(is_dir("$dir/$file") AND ($file!=".") AND ($file!="..")){
					$tree_list[$i] = array('name'=>"$dir/$file",'type'=>'dir','childs'=>NULL);
					$tree_list[$i]['childs'] = gddFile::dir_list("$dir/$file");
				}else if(($file!=".") AND ($file!=".."))
					$tree_list[$i] = array('name'=>"$dir/$file",'type'=>'file');
				else
					$tree_list[$i] = array('name'=>"$dir/$file",'type'=>'ps');
				$i++;
			}
			$mydir->close();
			return $tree_list;
		}
		static function isAllowDir($dir){
			$allow_del_dir = 'D:\xampp\htdocs\class-z-utf8\file\class/c1';//运行操作的路径		
			if(strpos($dir,$allow_del_dir)!==false){
				echo $dir;
				return true;
			}else
				return false;
		}
		static function del_dir($dir){
			/*删除目录*/
			$mydir = dir($dir);
			$tree_list = array();
			$i = 0;
			while($file = $mydir->read()){
				if(is_dir("$dir/$file") AND ($file!=".") AND ($file!="..")){
					$tree_list[$i] = array('name'=>"$dir/$file",'type'=>'dir','childs'=>NULL);
					$tree_list[$i]['childs'] = gddFile::del_dir("$dir/$file");
					if(gddFile::isAllowDir("$dir/$file")){
						echo ' allow delete!<br>';
					}
				}else if(($file!=".") AND ($file!="..")){
					$tree_list[$i] = array('name'=>"$dir/$file",'type'=>'file');
					if(gddFile::isAllowDir("$dir/$file")){
						echo ' allow delete!<br>';
					}
					unlink("$dir/$file");//删除文件
				}else
					$tree_list[$i] = array('name'=>"$dir/$file",'type'=>'ps');
				$i++;
			}
			$mydir->close();
			if(gddFile::isAllowDir("$dir/$file")){
				echo ' allow delete!<br>';
				rmdir($dir);//删除空目录
			}
			return $tree_list;
		}
		static function mkdir($dir,$name){
			/*创建目录*/
			mkdir("$dir/$name");//建立目录
		}
		function setFileType(){
			
		
		}
		
		function getContents(){
		
		
		}
		
		function getFileType(){
		
		
		}
	
	}
	
//	$dir_list = gddFile::dir_list();//遍历
//	print_r($dir_list);
	
//	$del_list = gddFile::del_dir('D:\xampp\htdocs\class-z-utf8\file\class/c1');	//批量删
//	print_r($del_list);

//	gddFile::mkdir('D:\xampp\htdocs\class-z-utf8\file\class/c1','ca'); //创建目录
?>
