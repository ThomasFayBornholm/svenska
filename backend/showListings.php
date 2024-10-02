<?php
	$path = getcwd() ."/../lists/";
	$class = $_GET['class'];
	
	$out["list"] = 0;
	
	$out["def"] = 0;
	$out["score"] = 0;
	$out["meta"] = 0;
	$out["more"] = 0;
	$f = $class . "-only";
	$infile = fopen($path . "/" . $f , "r") or die("Could not open file: " . $f);
	if (filesize($f) != 0) {
		$contents = fread($infile, filesize($f));
		$words = preg_split("/\r\n|\r|\n/", $contents);
		$out["list"] = count($words);
	}
	
	$jasons = ["def","score","meta","more"];	
	
	
	foreach($jasons as $j) {
		$name = $class . "-" . $j;
		$contents = file_get_contents($path . $name, 'UTF-8');
		$arr=json_decode($contents,JSON_UNESCAPED_UNICODE);	
		$out[$j] = count($arr);
	}
	
	echo json_encode($out);
?>
