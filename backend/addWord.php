<?php
	$res = 0;
	$path = getcwd() ."/";
	$class = $_GET['class'];

	$fileName = $path . $class;
	$infile = fopen($fileName, "r") or die("Could not open file: " . $fileName);
	$allName = $path . "all";
	$allFile = fopen($allName, "r") or die("Could not open file: " . $allName);
	if (filesize($fileName) == 0) {
		$contents = "";
	} else {
		$contents = fread($infile, filesize($fileName));
	}
	fclose($infile);

	$outfile = fopen($fileName, "a") or die("Could not open file: " . $fileName);
	$outfileAll = fopen($allName, "a") or die("Could not open file: " . $allName);
	// Check the word is not already listed
	$word = $_GET['word'];
	$elements= preg_split("/\r\n|\r|\n/", $contents);

	$addTheWord = true;	
	foreach ($elements as $el) {
		if ($el == $word) {
			$addTheWord = false;
		}
	}

	if ($addTheWord) {	
		// Append word to known words listing
		$res = fwrite($outfile, $word . " 0\n");
		$res = fwrite($outfileAll, $word . "\n");
	}

	fclose($outfile);
	fclose($outfileAll);
	echo json_encode($res);
?>
