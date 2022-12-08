<?php
	$path = getcwd() ."/";
	$class = $_GET['class'];
	$words = $_GET['words'];
	$trail = "-def";
	$name = $class . $trail;
	$metaName = $class . "-meta";
	$contents = file_get_contents($path . $name, 'UTF-8');
	$metaContents = file_get_contents($path . $metaName);
	$dict = json_decode($contents, JSON_UNESCAPED_UNICODE);
	$dictMeta = json_decode($metaContents, JSON_UNESCAPED_UNICODE);
	$wordArr = preg_split ("/\,/", $words);
	$res = 0;
	$i =0;
	if ($dict) {
		foreach($wordArr as $w) {
			if (array_key_exists($w, $dict) && array_key_exists($w, $dictMeta)) {
				$res += 1 << $i;			
			}
			else if (array_key_exists($w, $dict) && str_contains($dict[$w],"till '")) {
				$res += 1 << $i;			
			}
			$i++;	
			
		}
	} else {
		echo json_encode("Could not read json");
		return;
	}
	echo json_encode($res);
?>
