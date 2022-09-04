<?php
	$path = getcwd() ."/";
	$class = $_GET['class'];

	$size = filesize($path . $class);
	if ($size !== 0) {
		$infile = fopen($path . $class, "r");
		$contents = fread($infile, $size);
		$words = preg_split("/\r\n|\r|\n/", $contents);
	}
	$ind = rand(0, count($words));
	$w = $words[$ind];
	$w = str_replace(" 0","", $w);
	$w = str_replace(" 1","", $w);
	$w = str_replace(" 2","", $w);
	echo json_encode($w);
?>
