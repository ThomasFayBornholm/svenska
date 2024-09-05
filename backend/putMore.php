<?php
	$path = getcwd() ."/";
	$class = $_GET['class'];	
	$key = $_GET['key'];
	$more = $_GET['more'];
	$trail = "-more";
	// Existing definitions are overwritten
	if (strlen($more) > 0) {
		$name = $class . $trail;
		$contents = file_get_contents($path . $name, 'UTF-8');
		$arr = json_decode($contents,true);
		// Replicate the dictionary headers				
		$arr[$key]=$more;				
		file_put_contents($path. $name, json_encode($arr));
	}
	echo json_encode("");
?>