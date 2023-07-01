<?php
	$word = $_GET["word"];
	$name = "listing.txt";
	$myfile = file_put_contents($name, $word.PHP_EOL , FILE_APPEND | LOCK_EX);
	echo json_encode("appended '" . $word . "'");
?>