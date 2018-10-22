<?php
/***********  php指针测试  ***********/
$a='aaaaaaaa';
$point=&$a;
$a='eeeeeeee';
$b=&$point;
//&$b='cccccc';
echo $b;

/*******  链表  *****/
$list=array();
$list[]=array('front'=>'','next'=>'','father'=>'','name'=>'第一个节点','Controller'=>'Index','Action'=>'abs');
$list[]=array('front'=>'','next'=>'','father'=>'','name'=>'第二个节点','Controller'=>'Index','Action'=>'abs');
$node_tem=&$list[0];
print_r($list[0]);
$node_tem['name']='修改了第一个节点';
print_r($list[0]);
echo "<br>";
/******  判断一个 属性-值对 二维数组  *******/
$arr=array(array('aa','age'=>20));
$arr2=array('name'=>'gdd','age'=>20,'dname'=>'cc');

$i=0;

foreach($arr2 as $k=>$v){
	echo $k;
	if(is_int($k) && $k!=$i)
		break;
	$i++;
}


echo "<br>".$i;

$length=count($arr2);
if($i<$length)
	echo "不是一个合法的 属性-值 的数组!";


print_r($arr2);


/***********  链表类   树  **************/
class CList{
	public $list=array();
	public $list_point;
	public $node;
	public $count=0;
	function __construct(){
	
	}
	
	function init(){
		$this->node=new CNode();
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
    public function deletProperty(){
    
    }
    public function setProperty($property){
		$this->node[$property['name']]=$property['value'];
    }
   
}


$mylist=new CList();
$mylist->insertNode(new CNode(array('father'=>'','name'=>'第一个节点','Controller'=>'Index','Action'=>'abs')));
$mylist->insertNode(new CNode(array('father'=>'','name'=>'第y个节点','Controller'=>'Index','Action'=>'abs')));
$mylist->insertNode(new CNode(array('father'=>'','name'=>'第二个节点','Controller'=>'Index','Action'=>'abs')),1);
$mylist->insertNode(new CNode(array('father'=>'','name'=>'第x个节点','Controller'=>'Index','Action'=>'abs')),1);
$mylist->insertNode(new CNode(array('father'=>'','name'=>'第三个节点','Controller'=>'Index','Action'=>'abs')),1,0,0);
$mylist->insertNode(new CNode(array('father'=>'','name'=>'第四个节点','Controller'=>'Index','Action'=>'abs')),0,1,0);

/*********  菜单  ***********/
class GDDMenu{
	
}

/***  比较 2个实例 ******/

/*
echo $b->node['name'];
echo "<br/>";

echo serialize($a);
echo "<br/>";

echo serialize($b);
*/
$c=new CNode(array('name'=>'第5个节点','Controller'=>'Index','Action'=>'abs'));


$d=new CNode(array('name'=>'第5个节点','Controller'=>'Index','Action'=>'abs'));



if($c==$d){echo "c=d";} else echo "c!=d";


$arr1=array('aa','bb');
$arr2=$arr1;
$arr2[0]='cc';
print_r($arr1);



?>
