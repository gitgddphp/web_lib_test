<?php
	/*
		分页处理类 主要任务是完成 各种情况下 对数据进行分页计算
	*/
	class Page{
		public $allNum,$everyPageNum,$everyPageLinkNum,$nowPage=1;
		public function init($set){//array('allNum'=>100,'everyPageNum'=>5,'everyPageLinkNum'=>4,'nowPage'=>1)   
			$this->allNum=$set['allNum'];
			$this->everyPageNum=$set['everyPageNum'];
			$this->everyPageLinkNum=$set['everyPageLinkNum'];
			if($set['nowPage']>1)
				$this->nowPage=$set['nowPage'];
			$this->allPage=ceil($this->allNum/$this->everyPageNum);
			$cNum=ceil($this->nowPage/$this->everyPageLinkNum);
			$fNum=floor($this->nowPage/$this->everyPageLinkNum);
			if($cNum==$fNum)
				$i=$this->nowPage;
			else
				$i=$fNum*$this->everyPageLinkNum+1;
			for($i,$j=0;$i<$this->allPage&&$j<$this->everyPageLinkNum;$i++,$j++)
				$this->pageArr[]=$i;
		}
		public function getPageArr(){
			print_r($this->pageArr);
		}

	}
$pageA=new Page();
$pageSet=array('allNum'=>100,'everyPageNum'=>5,'everyPageLinkNum'=>5,'nowPage'=>9);
$pageA->init($pageSet);
$pageA->getPageArr();
?>