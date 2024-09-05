<?php
	$path = getcwd() ."/";
	$class = $_GET['class'];	
	$key = $_GET['key'];	
	$trail = "-more";
	if (strlen($key) > 0) {
		$name = $class . $trail;
		$contents = file_get_contents($path . $name, 'UTF-8');
		$arr = json_decode($contents,true);
		if (array_key_exists($key,$arr)) {
			echo "2<br>";
			$arr[$key] = "";
			file_put_contents($path. $name, json_encode($arr));
		}			
	}
	echo json_encode("");
?>