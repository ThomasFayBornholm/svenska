<?php
	$class = $_GET['class'];
	
	$path = getcwd() ."/";
	
	$score = array();
	$score['id'] = $_GET['id'];
	$score["total"] = 0;
	$score["redCount"] = 0;
	$score["blueCount"] = 0;
	$score["greenCount"] = 0;
	$score["percent"] = 0;
	$score["count"] = 0;
	
	if ($class === "all") {
		$classes = ["adjektiv", "verb", "adverb", "substantiv_en", "substantiv_ett", "fraser", "plural", "preposition", "pronomen", "interjektion", "förled", "slutled", "räkneord", "subjunktion", "konjunktion"];
	} else {
		$classes = array();
		array_push($classes,$class);
	}
	
	foreach ($classes as $class) {
		$scoreName = $class . "-score";	
		// "<class>-only" is the authoritative listing for total word count
		$f = $path . $class . "-only";
		$infile = fopen($f , "r") or die("Could not open file: " . $f);
		
		if (filesize($f) != 0) {
			$contents = fread($infile, filesize($f));
			$score["count"] += count(preg_split("/\r\n|\r|\n/", $contents));
		}
		
		if (file_exists($scoreName) && filesize($scoreName) != 0) {
			$scoreContents = file_get_contents($path . $scoreName);
			$dictScore = json_decode($scoreContents, JSON_UNESCAPED_UNICODE);		
			
			foreach(array_keys($dictScore) as $k) {
				$tmp = (int)$dictScore[$k];
				$score["total"] += $tmp;
				if ($tmp === 2) {
					$score["greenCount"]++;
				} else if ($tmp === 1) {
					$score["blueCount"]++;
				} else if ($tmp === 0) {
					$score["redCount"]++;
				}
			}
		}
	}
	
	if ($score["count"] != 0) {
		$score["percent"] = ($score["total"] / $score["count"] / 2 * 100);
		$score["percent"] = round($score["percent"],5);
	}
	echo json_encode($score);
?>
