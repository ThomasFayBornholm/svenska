<?php
	$contents = file_get_contents("listing.txt");
	$words = preg_split("/\r\n|\r|\n/", $contents);	
	echo json_encode($words);
?>