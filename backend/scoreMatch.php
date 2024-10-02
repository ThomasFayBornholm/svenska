<?php
	$path = getcwd() ."/../lists/";
	$class = $_GET['class'];
	// Match all words with this score
	$score = intval($_GET['score']);
	$name = $class . "-only";
	$contents = file_get_contents($path . $name, 'UTF-8');
	$words = preg_split("/\r\n|\r|\n/", $contents);
	
	$scoreName = $class . "-score";	
	if (file_exists($scoreName)) {
		$scoreContents = file_get_contents($path . $scoreName) or die("Could not open score listing");
	}
	$dictScore = json_decode($scoreContents, JSON_UNESCAPED_UNICODE);	
	$matches = array();
	foreach($words as $w) {
		if (array_key_exists($w, $dictScore)) {
			if (intval($dictScore[$w]) === $score) {
				array_push($matches, $w);	
			}
		}
	}	
	$out["cnt"] = count($matches);
	$out["matches"] = $matches;
	echo json_encode($out);
?>
