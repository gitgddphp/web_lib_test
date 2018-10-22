<?php
	class Menu{
		public $menuArray,$staticMenu;
		
		function __construct($MenuConfig){
			$this->menuArray=$MenuConfig;
		}
		
		function setStaticMenu($menu){
			$this->staticMenu=$menu;		
		}
		
		function addMenu($menu){
			$this->menuArray[$menu['menuname']]=$menu['menuArr'];
		}
		
		function getMenu($id){
			print_r($this->menuArray[$id]);
		}
	}
	//menux=
	$MenuConfig=array(
		'mainMenu'=>array('a'=>'cc/dd','d'=>'ee/ff'),
		'leftMenu'=>array('a'=>'cc/dd','d'=>'ee/ff')
	);
	$addmenu=array('menuname'=>'addmenu','menuArr'=>array('aa'=>'x/x','c'=>'x/d'));
	$x=new Menu($MenuConfig);
	$x->getMenu('mainMenu');
	$x->addMenu($addmenu);
	
	$x->getMenu('addmenu');
	
	
	
	
?>
