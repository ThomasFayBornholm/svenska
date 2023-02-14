<?php
	$path = getcwd() ."/";
	$class = $_GET['class'];	
	$name = $class . "-score";
	$contents = file_get_contents($path . $name, 'UTF-8');
	$dict = json_decode($contents, JSON_UNESCAPED_UNICODE);
	$out = array();
	foreach($dict as $key=>$val) {		
		$score = (int)$val;
		if ($score === 1) {
			array_push($out, $key);
		}
	}
	
	
	
	$randNum = rand(0,count($out));
	$tmpWord = $out[$randNum];

	$arr = array();
	$nameAll = $path . "all-only";
	$contents = file_get_contents($nameAll, 'UTF-8');
	$words = preg_split("/\r\n|\r|\n/", $contents);
	
	$i = 0;
	foreach($words as $w) {
		
		$i++;		
		if ($w === $tmpWord) {			
			echo json_encode($i);	
			return;
		}
	}
	echo json_encode(0);
?>
