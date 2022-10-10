<?php
	$path = getcwd() ."/";
	$class = $_GET['class'];
	$words = $_GET['words'];
	$trail = "-def";
	$name = $class . $trail;
	$contents = file_get_contents($path . $name, 'UTF-8');
	$dict = json_decode($contents, JSON_UNESCAPED_UNICODE);
	$wordArr = preg_split ("/\,/", $words);
	$res = 0;
	$i =0;
	if ($dict) {
		foreach($wordArr as $w) {
			if (array_key_exists($w, $dict)) 
			$res += 1 << $i;
			$i++;	
		}
	} else {
		echo json_encode("Could not read json");
		return;
	}
	echo json_encode($res);
?>
