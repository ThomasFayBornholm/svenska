<?php
	$word = $_GET['word'];
	$class = $_GET['class'];
	$grade = $_GET['grade'];
	// Known word listings to check against
	$path = getcwd() ."/../lists//;
	$inName = $path . $class;
	$outName = $path . "tmp";
	$infile = fopen($inName, "r") or die("Could not open file: " . $inName);
	$outfile = fopen($outName, "w") or die("Could not open file: " . $outName);
	// Word is retrieved from storage so expect a match
	$contents = fread($infile, filesize($inName));
	$elements= preg_split("/\r\n|\r|\n/", $contents);
	$tmp="";
	foreach($elements as $el) {
		// Remove any trailing number
		$w = str_replace(" 0", "", $el);
		$w = str_replace(" 1", "", $w);
		$w = str_replace(" 2", "", $w);
		// Update grading on matched word
		if ($word === $w) {
			$tmp = $tmp . $w . " " . $grade . "\n";
		} else {
			$tmp = $tmp . $el . "\n";
		}
	}
	$tmp = substr($tmp, 0, -1);
	fwrite($outfile, $tmp);
	fclose($infile);
	$res=fclose($outfile);
	copy($outName, $inName);

	echo json_encode($res);
?>
