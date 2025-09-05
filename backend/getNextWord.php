<?php
	$class = $_GET['class'];
	$start = $_GET["start"];
	$back = $_GET["back"];
	$path = getcwd() ."/../lists/";
	
	$size = filesize($path . $class . "-only");
	if ($size !== 0) {
		$infile = fopen($path . $class . "-only", "r");
		$contents = fread($infile, $size);
		$words = preg_split("/\r\n|\r|\n/", $contents);
	}

	$res = $words[$start];
	$end = sizeof($words);
	$inc = 1;	
	if ($back === "true") {
		$inc = -1;
		$end = 0;
	}
	
	$start = $start + $inc;
	if ($start < 0) {
		$start = 0;
	}
	$end = 50000;
	for ($i = $start; $i < $end; $i = $i + $inc) {			
		if ($i === -1) {
			$res = $words[0];
			break;
		}
	}
	echo json_encode($res);
?>
