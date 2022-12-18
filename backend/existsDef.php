<?php
	$res["stat"] = 0;
	$res["score"] = [];
	$path = getcwd() ."/";
	$class = $_GET['class'];
	// Not relevant for class of "all" words
	if ($class === "all") {
		echo json_encode($res);
		return;
	}
	$words = $_GET['words'];
	$trail = "-def";
	$name = $class . $trail;
	$metaName = $class . "-meta";
		
	$contents = file_get_contents($path . $name, 'UTF-8');
	$metaContents = file_get_contents($path . $metaName);
	
	$dict = json_decode($contents, JSON_UNESCAPED_UNICODE);
	$dictMeta = json_decode($metaContents, JSON_UNESCAPED_UNICODE);
	$scoreName = $class . "-score";
	$scoreContents = file_get_contents($path . $scoreName);
	$dictScore = json_decode($scoreContents, JSON_UNESCAPED_UNICODE);		
	$wordArr = preg_split ("/\,/", $words);

	$i =0;
	if ($dict) {
		foreach($wordArr as $w) {
			if (array_key_exists($w, $dict) && array_key_exists($w, $dictMeta)) {
				$res["stat"] += 1 << $i;			
			}
			else if (array_key_exists($w, $dict) && str_contains($dict[$w],"till '")) {
				$res["stat"] += 1 << $i;			
			}
			$i++;
			if ($dictScore != null) {
				if (array_key_exists($w, $dictScore)) {
					array_push($res["score"], $dictScore[$w]);
				} else {
					array_push($res["score"], 0);
				}
			}
		}
	} else {
		echo json_encode("Could not read json");
		return;
	}
	echo json_encode($res);
?>
