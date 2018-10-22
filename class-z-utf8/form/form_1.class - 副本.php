<?php

function showForm($config){
	foreach($config as $item)
		switch($item['type']){
			case 'text':echo '<input type="text" value="" />';echo "<br />";break;
			case 'password':echo '<input type="password" value="" />';echo "<br />";break;
			case 'textarea':echo '<textarea value="" ></textarea>';echo "<br />";break;
			case 'radio':	foreach($item['option'] as $key=>$value)
								echo '<label class="'.$item['class'].'">'.$key.'</label><input type="radio" name="'.$item['name'].'" value="'.$value.'" />';
							echo "<br />";break;
			case 'checkbox':	foreach($item['option'] as $key=>$value)
								echo '<label class="'.$item['class'].'">'.$key.'</label><input type="checkbox" name="'.$item['name'].'" value="'.$value.'" />';
							echo "<br />";break;
			case 'select':	echo "<select name='".$item['name']."' class='".$item['class']."'>";
							foreach($item['option'] as $key=>$value)
								echo '<option value="'.$value.'" />'.$key.'</option>';
							echo "</select>";
							echo "<br />";break;
			case 'date':echo '<input type="date" value="" />';echo "<br />";break;
			case 'image':echo '<input type="file" value="" />';echo "<br />";break;
		}
}
$formConfig=array(
		array('name'=>'name','type'=>'password','length'=>20,'option'=>array('name'=>'aa','value'=>2),'class'=>'text','id'=>2,'prompt'=>'提示信息!'),
		array('name'=>'title','type'=>'text','length'=>20,'option'=>array('name'=>'aa','value'=>2),'class'=>'text','id'=>2),
		array('name'=>'title','type'=>'checkbox','length'=>20,'option'=>array('name'=>'aa','value'=>2),'class'=>'text','id'=>2),
		array('name'=>'title','type'=>'textarea','length'=>20,'option'=>array('name'=>'aa','value'=>2),'class'=>'text','id'=>2),
		array('name'=>'title','type'=>'image','length'=>20,'option'=>array('name'=>'aa','value'=>2),'class'=>'text','id'=>2),
		array('name'=>'title','type'=>'date','length'=>20,'option'=>array('name'=>'aa','value'=>2),'class'=>'text','id'=>2),
		array('name'=>'title','type'=>'radio','length'=>20,'option'=>array('name'=>2,'cdname'=>3),'class'=>'text','id'=>2),
		array('name'=>'title','type'=>'select','length'=>20,'option'=>array('name'=>2,'cdname'=>3),'class'=>'text','id'=>2),
	); 
showForm($formConfig);
?>
