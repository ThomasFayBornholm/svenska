<?php
	$res = -1;
	$path = getcwd() ."/";
	$class = $_GET['class'];
	$word = $_GET['word'];
	// Write a tmp file with the chosen word remove
	// Then overwrite the original file with the temp file
	// Return -1 if word to be removed is not found.

	$fileName = $path . $class;
	$tmpName = $path . "tmp";

	$infile = fopen($fileName, "r") or die("Could not open file: " . $fileName);
	$tmpfile = fopen($tmpName, "w") or die("Could not open file: " . $tmpName);

	if (filesize($fileName) == 0) {
		$contents = "";
	} else {
		$contents = fread($infile, filesize($fileName));
	}

	$elements= preg_split("/\r\n|\r|\n/", $contents);
	$tmp = "";
	$wArr = array();
	foreach ($elements as $el) {
		$w = str_replace(" 0", "", $el);
		$w = str_replace(" 1", "", $w);
		$w = str_replace(" 2", "", $w);
		if ($w === $word) {
			$res = 0;
		} else {
			array_push($wArr, $el);
		}
	}

	for ($i = 0; $i < count($wArr) - 1; $i++) {
		fwrite($tmpfile, $wArr[$i] . "\n");
	}
	fwrite($tmpfile, $wArr[$i]);
	
	fclose($infile);
	fclose($tmpfile);

	if ($res === 0) {
		copy ($tmpName, $fileName);
	}

	echo json_encode($res);
?>
