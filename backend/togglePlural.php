<?php
	$path = getcwd() ."/../lists/";
	$class = $_GET["class"];
	$word = $_GET['word'];
	// $word comes from store listing rather than user input so no cleaning required

	$trail = "-meta";
	$name = $class . $trail;
	$contents = file_get_contents($path . $name, 'UTF-8');
	$arr = json_decode($contents,true);
	
	if (array_key_exists($word, $arr)) {		
		$meta = $arr[$word];
		// Prevent dubble-replacement on wraparound
		if (str_contains($meta,"~or")) {
			$meta = str_replace("~or", "~ar",$meta);			
		} else {			
			$meta = str_replace("~er", "~or",$meta);
			$meta = str_replace("~ar", "~er",$meta);
		}		
		$arr[$word] = $meta;
		file_put_contents($path. $name, json_encode($arr));
		$out["status"] = "updated";
	} else {
		$out["status"] = "Word not found.";
	}				
	echo json_encode($out);
?>
