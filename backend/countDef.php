<?php
	$path = getcwd() ."/";
	$class = $_GET['class'];
	$name = $class . "-def";
	$contents = file_get_contents($path . $name, 'UTF-8');
	$arr=json_decode($contents,JSON_UNESCAPED_UNICODE);
	if ($arr) {	
		echo json_encode(count($arr));
	} else {
		echo json_encode("Failed to read definition file.");
	}
?>
