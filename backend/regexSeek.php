<?php
	$path = getcwd() ."/../lists/";
	$class = $_GET['class'];
	$regex = $_GET['regex'];
	$regex = "/" . $regex . "/";	
	$name = $class . "-only";
	$contents = file_get_contents($path . $name, 'UTF-8');
	$words = preg_split("/\r\n|\r|\n/", $contents);

	$out["matches"] = array();
	$out["count"] = 0;
	foreach($words as $w) {
		if (preg_match($regex,$w)) {
			array_push($out["matches"],$w);
		}
	}
	$out["count"] = count($out["matches"]);
	echo json_encode($out);
?>
