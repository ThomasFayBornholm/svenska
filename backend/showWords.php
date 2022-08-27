<?php
	$path = getcwd() ."/";
	$class = $_GET['class'];
	$regex= $_GET['regex'];
	$grade = $_GET['grade'];
	$regex = "/" . $regex . "/i";
	$size = filesize($path . $class);
	if ($size !== 0) {
		$infile = fopen($path . $class, "r");
		$contents = fread($infile, $size);
		$words = preg_split("/\r\n|\r|\n/", $contents);
	}
	$matches = array();
	array_pop($words);
	foreach($words as $w) {
		$w_g = substr($w, -1);
		$not2 =	($grade === "-1" and $w_g !== "2"); 
		if ($grade === "3" or $grade === $w_g or $not2) {
			$t = str_replace(" 0", "", $w);
			$t = str_replace(" 1", "", $t);
			$t = str_replace(" 2", "", $t);

			if(preg_match($regex, $t)) {
				array_push($matches, $w);
			}
		}
	}
	// Show all words in class that match the regex
	sort($matches);
	$test = $matches;
	echo json_encode($matches);
?>
