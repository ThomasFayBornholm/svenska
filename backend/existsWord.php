<?php
	$word = $_GET['word'];
	$class =$_GET['class'];
	// Known word listings to check against
	$path = getcwd() ."/../lists/";
	$trail = "-only";
	$checkfile = $path . $class . $trail;
	if (filesize($checkfile) > 0) {
		$infile = fopen($checkfile,"r");
		$contents = fread($infile, filesize($checkfile));
		$elements = preg_split("/\r\n|\r|\n/", $contents);
		foreach($elements as $el) {
			if ($el === $word) {
				echo json_encode(-1);
				return;
			}
		}
	}
	fclose($infile);
	echo json_encode($word);
	?>
