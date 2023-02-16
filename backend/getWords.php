<?php
	$path = getcwd() ."/";
	$start = $_GET['start'];
	$num = $_GET['num'];
	$class = $_GET['class'] . "-only";
	$size = filesize($path . $class);
	if ($size !== 0) {
		$infile = fopen($path . $class, "r");
		$contents = fread($infile, $size);
		$words = preg_split("/\r\n|\r|\n/", $contents);
	}
	
	$outWords = array();
	$end = $start + $num;
	if ($end > count($words)) {
		$end = count($words);
	}
	for ($i = $start; $i < $end; $i++) {
		$w = $words[$i];
		if (strlen($w) > 0) {
			array_push($outWords, $words[$i]);
		}
	}
	// Show all words in class that match the regex
	echo json_encode($outWords);
?>
