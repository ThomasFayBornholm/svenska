<?php
	$path = getcwd() ."/";
	$class = $_GET['class'];
	$word = $_GET['word'];
	$def = $_GET['def'];
	$trail = "-def";
	$name = $class . $trail;
	$contents = file_get_contents($path . $name, 'UTF-8');
	$arr = json_decode($contents,true);
	$arr[$word] = $def;
	file_put_contents($path. $name, json_encode($arr));
?>
