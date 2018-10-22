<meta charset="utf-8" />
<?php 

class GddList{
	public $everyPageNum,$everyPage_showPageNum,$allPageNum;
	public $basePageLink,$dataNum,$dataArray,$showDataFields,$model;
	
	public function __construct(){
	
	}
	
	public function displayLinks($linkTags){
		
	}
	
	public function setdataNum(){
	
	
	}
	public function setdataArray(){
	
	}
	public function setshowDataFields(){
	
	
	}
	public function Zlist(){
        $table_field=array(
        						array('name'=>'id','width'=>'100px','class'=>'tr_left','field-name'=>'id'),
        						array('name'=>'用户名','width'=>'300px','class'=>'','field-name'=>'name'),
                                array('name'=>'密码','width'=>'200px','class'=>'','field-name'=>'password'),
								array('name'=>'用户组','width'=>'100px','class'=>'','field-name'=>'group')
                                ); 
        $data=array(
              			   array('id'=>'1','name'=>'gdd','password'=>'222','group'=>'管理员组'),
                           array('id'=>'2','name'=>'gdd','password'=>'222','group'=>'管理员组'),
                           array('id'=>'3','name'=>'gdd','password'=>'222','group'=>'管理员组'),
                           array('id'=>'4','name'=>'gdd','password'=>'222','group'=>'管理员组')
                         );                       
                                
         
        echo '<div class="myblack" style="height:auto; margin:20px; margin-top:0;"><table cellpadding="0" cellspacing="0"><tr class="table_title">';
        	
            	foreach($table_field as $field){
                	echo '<td class="'.$field['class'].'" style="width:'.$field['width'].'">'.$field['name'].'</td>';
                }
          echo '<td class="tr_right">操作(Edit)</td></tr>';
        
            	foreach($data as $fields){
                	$i=0;
                    echo '<tr class="tr_1">';
                    for($i=0;$i<count($table_field);$i++){
                        $str=$i==0?'<input type="checkbox" value="'.$fields[$table_field[$i]['field-name']].'" />':'';
                        echo '<td>'.$str.$fields[$table_field[$i]['field-name']].'</td>';
                    }
                    echo '<td><span>[编辑]&nbsp;[删除]&nbsp;[扩展]&nbsp;</span></td></tr>';
                }

		echo '<tr class="tr_1"><td colspan="6">
		<div class="Edit-menu" style=" padding-top:5px; padding-bottom:5px; height:30px">
		<span>全选</span>
		<span>反选</span>
		<a href=""><span>批量修改</span></a>
		<a href=""><span>批量删除</span></a>
		<a href=""><span>添加</span></a>
		</div>
		</td></tr>
	
	</table>
	<div class="Edit-menu" style=" padding-top:5px; padding-bottom:5px; height:30px">
		<span style="margin-left:0">分页菜单</span>
		<a href=""><span>首页</span></a>
		<span><a href="">[1]</a>&nbsp;[2]&nbsp;[1]&nbsp;[3]&nbsp;[4]&nbsp;[5]&nbsp;[1]&nbsp;[6]&nbsp;</span>
		<a href=""><span>尾页</span></a>
		<span>跳转 
			<select>
				<option>1</option><option>2</option><option>3</option>
			</select> 
			<input type="text" size="1" />
			<input type="submit" value="GO" />
		</span>
	</div>
        </div>';
	
		
	}

}
	$a=new GddList();
	$a->Zlist();

?>
