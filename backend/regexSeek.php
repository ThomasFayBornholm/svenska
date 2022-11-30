<?php
	$path = getcwd() ."/";
	$class = $_GET['class'];
	$regex = $_GET['regex'];
	$regex = "/" . $regex . "/";	
	$INC = $_GET['inc'];
	$name = $class . "-only";
	$contents = file_get_contents($path . $name, 'UTF-8');
	$words = preg_split("/\r\n|\r|\n/", $contents);
	$ind = 0;
	// Loop from current position
	// First match is returned

	$ind = 0;
	foreach($words as $w) {
		if ($ind > $INC) {
			if (preg_match($regex,$w)) {
				echo json_encode($ind);
				return;
			}
		}
		$ind++;
	}
	$ind = 0;
	foreach($words as $w) {
		if ($ind > $INC) {
			echo json_encode(-1);
			return;
		} else {
			if (preg_match($regex,$w)) {
				echo json_encode($ind);
				return;
			}
		}
		$ind++;
	}
	
	echo json_encode($match);
?>
