<?php
	$class = $_GET['class'];
	$path = getcwd() ."/";
	
	$score = array();
	$score["total"] = 0;
	$score["redCount"] = 0;
	$score["blueCount"] = 0;
	$score["greenCount"] = 0;
	if ($class === "all") {
		$classes = ["adjektiv", "verb", "adverb", "substantiv_en", "substantiv_ett", "plural", "preposition", "pronomen", "interjektion", "förled", "slutled", "räkneord", "subjunktion", "konjunktion"];
	} else {
		$classes = array();
		array_push($classes,$class);
	}
	foreach ($classes as $class) {
		$scoreName = $class . "-score";	
		if (file_exists($scoreName)) {
			$scoreContents = file_get_contents($path . $scoreName);
			$dictScore = json_decode($scoreContents, JSON_UNESCAPED_UNICODE);		
			
			foreach(array_keys($dictScore) as $k) {
				$tmp = (int)$dictScore[$k];
				$score["total"] += $tmp;
				if ($tmp === 2) {
					$score["greenCount"]++;
				} else if ($tmp === 1) {
					$score["blueCount"]++;
				} else {
					$score["redCount"]++;
				}
			}
		}
	}
	echo json_encode($score);
?>
