<?php
	$word = $_GET["word"];
	$name = "listing.txt";
	$path = getcwd() . "../lists/";
	$myfile = file_put_contents($name, $word.PHP_EOL , FILE_APPEND | LOCK_EX);
	echo json_encode("appended '" . $word . "'");
?>