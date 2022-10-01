<?php
	$path = getcwd() ."/";
	$class = $_GET['class'];
	$word = $_GET['word'];
	$trail = "-def";
	$name = $class . $trail;
	$contents = file_get_contents($path . $name, 'UTF-8');
	$dict = json_decode($contents, JSON_UNESCAPED_UNICODE);
	if ($dict) {
		if (array_key_exists($word, $dict)) {
			echo json_encode($dict[$word]);
			return;
		} else {
			echo json_encode("No definition found");
		}
	} else {
		echo json_encode("Could not read json");
	}
?>
