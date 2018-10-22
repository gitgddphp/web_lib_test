<html>
	<head>
		<title>
		<?php echo $this->title; ?>
		</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	</head>
	<body>
	<h1>表单模板测试页面1!</h1>
		<form>
			<?php
				ViewHelper::myForm($this->data,'type');
			?>
		</form>
	</body>
	
</html>
