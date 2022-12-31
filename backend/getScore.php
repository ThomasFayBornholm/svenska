<?php
	$class = $_GET['class'];
	$path = getcwd() ."/";
	
	$score = 0;
	$scoreName = $class . "-score";
	if (file_exists($scoreName)) {
		$scoreContents = file_get_contents($path . $scoreName);
		$dictScore = json_decode($scoreContents, JSON_UNESCAPED_UNICODE);		
		
		foreach(array_keys($dictScore) as $k) {
			$score += $dictScore[$k];
		}
	}
	echo json_encode($score);
?>
