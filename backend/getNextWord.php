<?php
	$class = $_GET['class'];
	$start = $_GET["start"];
	$score = $_GET["score"];
	$back = $_GET["back"];
	$path = getcwd() ."/";
	
	$scoreName = $class . "-score";
	$size = filesize($path . $class . "-only");
	if ($size !== 0) {
		$infile = fopen($path . $class . "-only", "r");
		$contents = fread($infile, $size);
		$words = preg_split("/\r\n|\r|\n/", $contents);
	}

	if (file_exists($scoreName)) {
		$scoreContents = file_get_contents($path . $scoreName);
		$dictScore = json_decode($scoreContents, JSON_UNESCAPED_UNICODE);						
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
		if (!array_key_exists($words[$i],$dictScore)) {
			if ($score == 2) {
				$res = $words[$i];
				break;
			}
		} else if ($dictScore[$words[$i]] === $score) {			
			$res = $words[$i];
			break;
		}
	}
	echo json_encode($res);
?>
