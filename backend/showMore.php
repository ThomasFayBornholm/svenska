<?php
	$path = getcwd() ."/";
	$class = $_GET['class'];
	$word = $_GET['word'];
	$trail = "-more";
	$name = $class . $trail;
	$contents = file_get_contents($path . $name, 'UTF-8');
	$dict = json_decode($contents, JSON_UNESCAPED_UNICODE);	
	$out = "";
	if ($dict) {
		if (array_key_exists($word, $dict)) {
			$out = $dict[$word];
		}
	}
	echo json_encode($out);
?>
