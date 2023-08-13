<?php
	$path =  $path = getcwd() . "/../sounds/";
	$class = $_GET['class'];
	$word = $_GET['word'];	
	$fullPath = $path . $class . "/" . $word;
	$res = "";
	if (file_exists($fullPath)) {
		$res = "exists";
	}
	echo json_encode($res);
?>
