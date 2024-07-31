<?php
	$name = "testJSON";
	$text = file_get_contents($name, "UTF-8");
	$dict = json_decode($text, JSON_UNESCAPED_UNICODE); 	
	echo json_encode($dict);
	return;
?>