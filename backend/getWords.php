<?php
	$path = getcwd() ."/";
	$start = $_GET['start'];
	$num = $_GET['num'];
	$class = $_GET['class'];
	$size = filesize($path . $class);
	if ($size !== 0) {
		$infile = fopen($path . $class, "r");
		$contents = fread($infile, $size);
		$words = preg_split("/\r\n|\r|\n/", $contents);
	}
	$outWords = array();
	$end = $start + $num;
	if ($end > count($words) - 1) {
		$end = count($words) - 1;
	}
	for ($i = $start; $i < $end; $i++) {
		$t = str_replace(" 0", "", $words[$i]);
		$t = str_replace(" 1", "", $t);
		$t = str_replace(" 2", "", $t);
		array_push($outWords, $t);
	}
	// Show all words in class that match the regex
	echo json_encode($outWords);
?>
