<?php

/******   消息类  ************/
    interface InterfaceMessage{
		function getMessage();//获取消息
		
		function getMessageId();//获取消息id
		
		function setMessageId();//设置消息id
		
		function getMessageString();//获取消息
		
		function setMessageString();
		
		function setMessageTemplet();
		
		function ShowMessage();
	
	}

	
	class Message implements InterfaceMessage{
		public $_message_string,$_message_id,$_message_templet;
		function __construct(){
			echo "message!";
		}
		
		function getMessage(){
		
		}
		
		function getMessageId(){
		
		}
		
		function setMessageId(){
		
		
		}
		
		function getMessageString(){
			return $this->_message_string;
		}
		
		public function setMessageString($message=''){
			if($message!=''){
				$this->_message_string=$message;
				return true;
			}else return false;
		}
		function setMessageTemplet(){
		
		}
		function ShowMessage(){
			require("message_templets.php");
		}

	}
    $a=new Message;
	$a->setMessageString("aaaaa");
	$a->ShowMessage();

/*************   静态助手类  ************/	

class FileHelper{//文件助手类
	public static function getRelPath($a,$b){//计算$a 到 $b 的相对路径; 
		$a_arr=explode('/',$a);
		$b_arr=explode('/',$b);
		if($a_arr[0]!=$b_arr[0]){//判断他们是否 是同一个 根目录
			return false;
		}
		$c1=array();
		$c2=array();
		$flag=1;
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
	

}

class viewHelper{//视图助手类

	public static function myList(){
		$args=func_get_args();
		$args_num=count($args);
		$data=$args[$args_num-1];	
	//	print_r($data);	
		$cStack=array();								//参数栈 用于存放 列表 标签 和 变量;
		for($i=0;$i<$args_num-1;$i++){
			array_push($cStack,$args[$i]);
		}
	//	print_r($cStack);
		$temCStack=$cStack;
		
		ob_start();  
		foreach($data as $list){
			$keys=array_keys ($list);
			for($i=0;$i<$args_num-2;$i++){
				if(in_array($cStack[$i],$keys,true)){
					echo $list[$cStack[$i]];
				}else
					echo $cStack[$i];
			}
		
		}
		return ob_get_clean();
	}

	public static function ifList($field,$value,$ifcStack,$samplecStack,$data){
//		ob_start();  
		$line_i=0;
		foreach($data as $list){
			if($list[$field]==$value)
				$cStack=$ifcStack;
			else
				$cStack=$samplecStack;
				
			$keys=array_keys ($list);
			for($i=0;$i<count($cStack);$i++){
				if(in_array($cStack[$i],$keys,true)){
					echo $list[$cStack[$i]];
				}else
					echo $cStack[$i];
			}
			$line_i++;
		}
		return ob_get_clean();
	}

	function myList_if(){
		$args=func_get_args();
	//	$args_num=count($args);
		$data=$args[2];	
		print_r($data);	

		$ifArray=$args[0];								//参数栈 用于存放 列表 标签 和 变量;
		$samplecStack=$args[1];
		
		$ifKeys=array_keys($ifArray);
		$ifValues=array_values($ifArray);
		
		$if_field=array_keys($ifValues[0]);
		$if_fieldStack=array_values($ifValues[0]);
		
		$if_fieldStack=$if_fieldStack[0];
		
		$if_field_value=$if_fieldStack[0];
		$if_stack=$if_fieldStack[1];
		
	//	echo $ifKeys[0];
	/*
		echo '<br>';
		echo '<br>';
		print_r($if_field[0]);echo "<br><br>";
		print_r($if_field_value);echo "<br><br>";
		print_r($if_stack);echo "<br><br>";
		echo "<br><br>";
		print_r($samplecStack);echo "<br><br>";
	*/
		
		switch($ifKeys[0]){
			case 'if': return ifList($if_field[0],$if_field_value,$if_stack,$samplecStack,$data);
			  break;
			default:
			  echo "No number between 1 and 3";
		}
		
	}

}



/****************    列表分页类     ***************/
class PageList{
	private $pageNum,$pageSize,$nowPage,$allNum,$updateflag=0;//基础属性  分页的大小 每页显示多少个页面链接  数据总条数  基础属性跟新标志 
	private $pageList,$allPageNum,$pageUpdateFlag=0;  //第一次计算出来的信息 每页显示的页面链接列表   总页数  页面跳转更新标志
	private $pageListUpdateFlag=0,$nowPageListNum,$allPageListNum; //用于滚屏 的参数  含 当前滚到第几屏  和 滚屏跟新标志
	
	public function getAllPageNum(){
		if(isset($this->pageNum) && $updateflag)
			return $this->pageNum;
		else
			if(isset($this->pageSize) && isset($this->allNum)){
				$this->pageNum=ceil($this->allNum/$this->pageSize);
				return $this->pageNum;
			}else
				return false;
	}
	
	public function setPageNum($num){
		if($num!=$this->pageNum)
			$this->updateflag=0;
		$this->pageNum=$num;
		return $this;
	}
	
	public function getPageNum(){
		if(isset($this->pageNum))
			return $this->pageNum;
		else
			return false;
	}
	
	public function setPageSize($num){
		if($num!=$this->pageSize)
			$this->updateflag=0;
		$this->pageSize=$num;
		return $this;
	}
	
	public function getPageSize(){
		if(isset($this->pageSize))
			return $this->pageSize;
		else 
			return false;
	}
	
	public function setNowPage($num){
		if($num!=$this->nowPage)
			$this->pageUpdateFlag=0;
		$this->nowPage=$num;
		return $this;
	}
	
	public function getNowPage(){
		if(isset($this->nowPage))
			return $this->nowPage;
		else 
			return false;
	}
	
	public function getPageList(){
		if(isset($this->nowPageListNum) && $this->pageListUpdateFlag && $this->pageUpdateFlag){
			return $this->pageList;
		}else
			if(isset($this->nowPageListNum) && $this->pageListUpdateFlag){//通过滚屏获取到新的 页面链接列表
				$firstNum=$this->pageNum*($this->nowPageListNum-1);
				$this->pageList=array();
				for($i=1; $i<=$this->pageNum; $i++){
					$this->pageList[]=$firstNum+$i;
				}
				$this->pageListUpdateFlag=1;
				return $this->pageList;
			}else
				if($this->pageUpdateFlag){//通过跳转页面获取的 页面链接列表
					$this->nowPageListNum=ceil($this->nowPageNum/$this->pageNum);//获取当前页在哪一个滚屏页里面;
					$firstNum=$this->pageNum*($this->nowPageListNum-1);
					$this->pageList=array();
					for($i=1; $i<=$this->pageNum; $i++){
						$this->pageList[]=$firstNum+$i;
					}
					$this->pageUpdateFlag=1;
					return $this->pageList;
				}
	}
	
	public function getAllPageListNum(){//能滚屏多少次
		if(isset($this->pageListNum) && $this->updateflag){
			$this->allPageListNum;
		}else{
			$this->allPageListNum=ceil($this->allPageNum/$this->pageNum);
		}
	}
	
	public function getNowPageListNum(){//当前滚到第几屏
		if(isset($this->nowPageListNum)){
			return $this->nowPageListNum;
		}else
			return false;
	}
	
	public function setNowPageListNum($num){
		if($num=$this->nowPageListNum)
			$this->pageListUpdateFlag=0;
		$this->pageListUpdateFlag=$num;
		return $this;
	}
	
	public function setAllNum($num){
		if($this->allNum!=$num)
			$this->updateflag=0;
		$this->allNum=$num;
		return $this;
	}
	
	public function getAllNum(){
		if(isset($this->allNum))
			return $this->allNum;
		else
			return false;
	}
	

}
/******   用户类   *****/
interface InterfaceUser{

}
abstract class AbstractUser{	
	abstract public function getName();
	abstract public function upDateName();
	abstract public function upDatePass();
	abstract public function getId();
	abstract public function getUserGroupe();
}

/********** 文章类  *************/
interface InterfaceArticleManage{}
abstract class AbstractArticleManage{
	abstract public function addArticle();
	abstract public function deleteArticle();
	abstract public function upDateArticle();
	abstract public function getArticleByTitle();
	abstract public function getArticleById();
	abstract public function getArticleByKeyWords();
	abstract public function setArticleProperty();	
}
/*********  菜单类  **********/
abstract class AbstractMenu{
	abstract public function getMenuName();
	abstract public function getMenuId();
	abstract public function addIterm();

}

/************    视图输出类    ******************/
class ViewRend{

	function rendTemplet(){
	
	
	}
	
	


}


/*************   类管理器  *****************/
class Mtest{//这是一个 要测试的类
	public $a='';
	function __construct($a){
		$this->a=$a;
		echo "<br> create class Mtest Object!<br>";
	}
	function display(){
		echo "<br>".$this->a;
	}
	
	function __clone(){
		echo "<br>克隆了".__CLASS__."类:";
		print_r(get_class_methods($this));
		print_r($this);
	}
}


$x1=ObjectManage::getClass('Mtest','BBBaa');	
$x1->display();

$x2=ObjectManage::getClass('Mtest','CCCaa');	
$x2->display();

$x3=ObjectManage::getClass('Mtest','BBBaa');	
$x3->display();


/********** 实例管理器 用于 存放 系统创建的 类  列表 ************/

class ObjectManage{
	public static $ClassList='';         //   ['class1']['class']  ['class2']['class']  
	public function __construct(){
	
	
	}
	
	public static function getClass($className,$config=''){
		
		if(self::$ClassList!=''){
		
			$classNames=array_keys(self::$ClassList);
//			print_r($classNames);
			if(in_array($className,$classNames)){
				$myClass=self::hasConfigClass(self::$ClassList[$className],$config);
				return clone($myClass!=false ? $myClass:self::addConfigClass($className,$config));
			}else{
				return clone self::addConfigClass($className,$config);
			}
		}else
			return clone self::addConfigClass($className,$config);
	}
	
	public static function addConfigClass($className,$config=''){//添加一个 带 参数的 对象
		$myClass=new $className($config);
		if(isset($myClass)){
			$classArray=array('object'=>$myClass,'config'=>$config);
			self::$ClassList[$className][]=$classArray;
			return $myClass;
		}
		return false;
	}
	public static function hasConfigClass($classMap,$config=''){
		$i=0;
		foreach($classMap as $my){
			if($my['config']==$config)
				return $my['object'];
			$i++;
		}
		return false;	
	}
}

/***************     行为 类 （系统预定义行为接口）  和   用户自定义行为接口     **********
系统开始     标签位        
用户参数接收 标签位
视图解析标签     位
********************************************************************************************/
/*******  标签位植入 *

    实现标签下面挂载 执行函数;
    使用环境，当我们要在一个方法中进行切面编程时,在不通过修改该函数就能达到 
	插入切面的实现方式;
	用法:需要一个标签配置文件,形同:array('onBegin'=>array('abmy','abmy1')); 包含了 标签 和要挂载上去的函数;
    在使用时需要预先将标签 存放到 要实现切面编程的 函数中;
	并且在函数执行前创建好 标签调用类的实例;并将标签配置参数 加载好;
    


*/
class  MyLabelsManage{
	public $labels=array('onBegin'=>array('abmy','abmy1'));  
	function __construct($config=''){
		if($config!='') $this->labels=$config;
	}
	function getMyLabFun($label){
		if(is_array($this->labels[$label]))
			foreach($this->labels[$label] as $function)
				$function();
		else
			$this->labels[$label]();
	}
}
function abmy(){
	echo "<br>absdm!";
}
function abmy1(){
	echo "<br>absdm!111";
}


ObjectManage::getClass('MyLabelsManage')->getMyLabFun('onBegin');
/**********文件操作类*******/
class FileHandle{
	public $filename;
	function __construct(){
		
	}
	function _loadfile(){
	
	}
	function _deletefile(){
		
	}
	function _creatfile(){
	
	}
	
	function _writefile(){
	
	
	}
}
/*********字符串处理类***********/





/***********  链表类   树类  包含 节点类  可用于创建菜单 修改节点参数等 **************/
class CList{
	public $list=array();
	public $list_point;
	public $node;
	public $count=0;
	function __construct(){
		//后续完善
	}
	
	function init(){
		$this->node=new CNode();//以后再做完善
	}
	
	function addNode($node,$pre_id='',$next_id='',$father_id=''){
			if(isset($this->count))
				$this->list[]=$node;
			else 
				$this->list=array($node);
			
			$this->count++;
			$my_index=$this->count;
//			echo $my_index;
//			print_r($this->list[$my_index]);
			$this->list[$my_index-1]->setProperty(array('name'=>'id','value'=>$my_index));
			echo "<br>";
			
			if(is_int($pre_id))
				if($pre_id!=0){
					if(isset($this->list[$pre_id-1])){
						if($tempNextNode_id=$this->list[$pre_id-1]->node['next']){
							$this->list[$tempNextNode_id-1]->node['pre']=$my_index;
							$this->list[$my_index-1]->setProperty(array('name'=>'next','value'=>$tempNextNode_id));
						}
						$this->list[$pre_id-1]->node['next']=$my_index;
						$this->list[$my_index-1]->setProperty(array('name'=>'pre','value'=>$pre_id));
					}else{
						echo "列表中没有这个节点!节点添加失败!";
						unset($this->list[$my_index-1]);
					}
				}
				
			if(is_int($next_id))
				if($next_id!=0){
					if(isset($this->list[$next_id-1])){
						if($this->list[$next_id-1]->node['pre']){
							echo "<br>".$this->list[$next_id-1]->node['pre']."<br>";
							$tempPreNode_id=$this->list[$next_id-1]->node['pre'];
							$this->list[$tempPreNode_id-1]->node['next']=$my_index;
							$this->list[$my_index-1]->setProperty(array('name'=>'pre','value'=>$tempPreNode_id));
						}
						$this->list[$next_id-1]->node['pre']=$my_index;
						
						$this->list[$my_index-1]->setProperty(array('name'=>'next','value'=>$next_id));
					}else{
						echo "列表中没有这个节点!节点添加失败!";
						unset($this->list[$my_index-1]);
					}
				}
				
			if(is_int($father_id))
				if($father_id!=0){
					if(isset($this->list[$father_id-1]))
						$this->list[$my_index-1]->setProperty(array('name'=>'father','value'=>$father_id));
					else{
						echo "列表中没有这个节点!节点添加失败!";
						unset($this->list[$my_index-1]);
					}
				}
				
				
			print_r($this->list);
	}
	
	function insertNode($node,$pre_id=0,$next_id=0,$father_id=0){
		/*节点插入：分几种情况
			1/有前节点 和 父亲节点 (前节点 没有后续节点的情况下 可以直接插入节点)
			2/有后节点 和 父亲节点（后节点 没有前续节点的情况下 可以直接插入节点）
			3/有前节点 和 后续节点（前续节点 都没有  父亲节点  或者 具有相同的父亲节点  才可以插入）
			4/只有父节点 （父节点 没有 孩子节点  可以直接插入该节点）
			5/只有前节点 （前节点 没有 父节点 也没有 后续节点）
            6/只有后节点  后节点 没有前节点  和父节点			
		*/
		if($node instanceof CNode){
		
		
			if($pre_id==0 && $next_id==0 && $father_id==0){
				$this->addNode($node,$pre_id,$next_id,$father_id);
			}
				else if($pre_id!=0 && $next_id!=0 && $father_id!=0){
					if(isset($this->list[$pre_id-1]) && isset($this->list[$next_id-1]) && isset($this->list[$father_id-1]) && $pre_id!=$next_id && $father_id!=$next_id && $father_id!=$pre_id)
						if($this->list[$pre_id-1]->node['father']==$this->list[$next_id-1]->node['father'] && $this->list[$next_id-1]->node['father']==$this->list[$father_id-1]->node['id']+1){
							$this->addNode($node,$pre_id,$next_id,$father_id);
						}
				}
					else if($pre_id!=0 && $next_id==0 && $father_id==0){
						if(isset($this->list[$pre_id-1]))
						if($this->list[$pre_id-1]->node['father']==null){
							$this->addNode($node,$pre_id,$next_id,$father_id);
						}
					}
						else if($pre_id==0 && $next_id!=0 && $father_id==0){
							if(isset($this->list[$next_id-1]))
							if($this->list[$next_id-1]->node['father']==null){
								$this->addNode($node,$pre_id,$next_id,$father_id);
							}
						}
							else if($pre_id==0 && $next_id==0 && $father_id!=0){
								if(isset($this->list[$father_id-1])){
									$this->addNode($node,$pre_id,$next_id,$father_id);
								}
							}
								else if($pre_id!=0 && $next_id!=0 && $father_id==0){
										if(isset($this->list[$next_id-1]) && isset($this->list[$pre_id-1]) && $this->list[$pre_id-1]->node['id']!=$this->list[$next_id-1]->node['id'])
											if($this->list[$next_id-1]->node['father']==$this->list[$pre_id-1]->node['father']){
												$this->addNode($node,$pre_id,$next_id,$father_id);
											}
								}
									else if($pre_id==0 && $next_id!=0 && $father_id!=0){
											if(isset($this->list[$next_id-1]) && isset($this->list[$father_id-1]))
												if($this->list[$next_id-1]->node['pre']==null && $this->list[$next_id-1]->node['id']!=$this->list[$father_id-1]->node['id']){
													$this->addNode($node,$pre_id,$next_id,$father_id);
												}
									}
										else if($pre_id!=0 && $next_id==0 && $father_id!=0){
												if(isset($this->list[$pre_id-1]) && isset($this->list[$father_id-1])){
													if($this->list[$pre_id-1]->node['id']!=$this->list[$father_id-1]->node['id']){
														if($this->list[$pre_id-1]->node['next']==null)
															$this->addNode($node,$pre_id,$next_id,$father_id);															
													}													
													
												}
										}
				
		}else
			echo "错误! 不是 CNode 的实例!";
	}
	
	function foundNode($name){
		
	}
	
	function deletNode($node_name){
	
	
	}
}

/*********  节点类  ************/
abstract class AbstractgddNode{
	abstract public function addProperty();
	abstract public function setProperty();
	abstract public function deleteProperty();
}

class CNode{
   //节点类  主要用于组成链表
    public $node=array();
	
    function __construct($propertys=null){
	    //初始节点属性   节点具有一些固有 属性(前续节点, 后续节点,节点名称 )
		if(isset($propertys)){
			foreach($propertys as $property_name=>$property_value){
				$this->node[$property_name]=$property_value;
			}
		}
		isset($this->node['pre'])?:$this->node['pre']=null;         //节点前续
		isset($this->node['next'])?:$this->node['next']=null;       //节点后续
		isset($this->node['name'])?:$this->node['name']=null;       //节点名称
		isset($this->node['id'])?:$this->node['id']=null;           //节点序数
		isset($this->node['father'])?:$this->node['father']=null;           //父亲节点
    }
    public function addProperty(){
    
    }
    public function deleteProperty(){
    
    }
    public function setProperty($property){
		$this->node[$property['name']]=$property['value'];
    }
   
}


$mylist=new CList();
$mylist->insertNode(new CNode(array('father'=>'','name'=>'第一个节点','Controller'=>'Index','Action'=>'abs')));
$mylist->insertNode(new CNode(array('father'=>'','name'=>'第二个节点','Controller'=>'Index','Action'=>'abs')),1);
$mylist->insertNode(new CNode(array('father'=>'','name'=>'第三个节点','Controller'=>'Index','Action'=>'abs')),1,0,0);
$mylist->insertNode(new CNode(array('father'=>'','name'=>'第四个节点','Controller'=>'Index','Action'=>'abs')),0,1,0);//节点实例   前节点   后节点  父节点 位置

/*********  菜单类  依赖于 节点类  ***********/
/*
    菜单由节点组成;  每个节点 有子节点

*/




/*
	array(array('item'=>array('name'=>'name','url'=>'url'),'children'=>array()))

*/ 

abstract class aAbstractMenu{
	abstract function addItem($a);
}

class GddMenu extends aAbstractMenu{//items=array(name=>'',url=>'',children=>array|())

	public function addItem($a){
		echo "$a";
	}

}



	

?>
