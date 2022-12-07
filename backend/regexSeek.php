<?php
	$path = getcwd() ."/";
	$class = $_GET['class'];
	$regex = $_GET['regex'];
	$regex = "/" . $regex . "/";	
	$name = $class . "-only";
	$contents = file_get_contents($path . $name, 'UTF-8');
	$words = preg_split("/\r\n|\r|\n/", $contents);
	$ind = 0;
	// Loop from current position
	// First match is returned

	$out = array();
	foreach($words as $w) {
		if (preg_match($regex,$w)) {
			array_push($out,$w);
		}
	}
	
	echo json_encode($out);
?>
