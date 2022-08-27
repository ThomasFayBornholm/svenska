<?php
	$path = getcwd() ."/";
	$fileName = "ordlista";
	$wordlist = fopen($path . $fileName, "r") or die("Could not open file: " . $fileName);
	if (filesize($fileName) > 0) { 
		$contents= fread($wordlist,filesize($fileName));
	} else {
		echo json_encode("");
		return;
	}
	$words = preg_split("/\r\n|\r|\n/", $contents);
	array_pop($words);
	echo json_encode($words);
?>
