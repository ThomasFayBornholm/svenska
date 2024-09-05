<?php
	$res = -1;
	$path = getcwd() ."/";
	$class = $_GET['class'];
	$word = $_GET['word'];
	
	$fileName = $path . $class . "-only";
	$infile = fopen($fileName, "r") or die("Could not open file: " . $fileName);
	if (filesize($fileName) == 0) {
		$contents = "";
	} else {
		$contents = fread($infile, filesize($fileName));
	}
	fclose($infile);
	$elements= preg_split("/\r\n|\r|\n/", $contents);
	$regex = "/^" . $word . "[_1-9]*$/";
	foreach($elements as $w) {
		if (preg_match($regex,$w)) {
			if ($res === -1) {
				$res = 2;
			} else {
				$res++;
			}
		}
	}	
	echo json_encode($res);
?>