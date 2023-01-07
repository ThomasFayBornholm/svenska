<?php
	$path = getcwd() ."/";
	$word = $_GET['word'];
	$rest = "";	
	$nFind = 1;
	if (str_contains($word, " ")) {
		$tmp = explode(" ", $word);
		$word = $tmp[0];
		$rest = "";
		$nFind = count($tmp);
		for ($i = 1; $i < $nFind; $i++) {
			$rest = $rest . " " . $tmp[$i];
		}
	}

	$class = $_GET['class'];
	$trail = "-meta";
	if ($class === "all") {
		$classArr = array("verb", "adjektiv", "adverb", "substantiv_en", "substantiv_ett", "plural", "superlativ","preposition", "interjektion", "pronomen", "förled","slutled","räkneord","konjunktion","subjunktion");
	} else {
		$classArr = array($class);
	}
	
	$out = array();
	$out["class"]="";
	$out["word"]="";
	$meta = array(",","-","­","plural","singular","bestämd","ingen böjning");
	foreach ($classArr as $el) {
		$name = $el . $trail;
		// Get word list from "-only" listing
		$inList = $path . $el . "-only";
		$infile = fopen($inList, "r");
		$size = $size = filesize($inList);
		$contents = fread($infile, $size);
		$wordList = preg_split("/\r\n|\r|\n/", $contents);
		$contentsMeta = file_get_contents($path . $name, 'UTF-8');
		$dict = json_decode($contentsMeta, JSON_UNESCAPED_UNICODE);
		
		foreach($wordList as $key) {	
			if (array_key_exists($key, $dict)) {
				$lines = explode("<br>", $dict[$key]);
				
				$lineOne = $lines[0];
				if ($word != "eller") {
					$lineOne = str_replace("eller", "", $lineOne);
				}
				foreach($meta as $m) {
					$lineOne = str_replace($m,"",$lineOne);
				}	
				
				$words = explode(" ",$lineOne);
				$firstConj = $words[0];
				foreach($words as $w) {					
					$w = str_replace("~",$firstConj, $w);					
					$fuzzyE = str_replace("é","e",$w);
					$fuzzyE = str_replace("é","e",$fuzzyE);						
					if ($word === $w or $word === $fuzzyE or str_contains($lineOne,$word)) {		
						if (str_contains($lineOne,$rest)) {				
							$keyCount = count(explode(" ", $key));
							if ($nFind === $keyCount || $el === "adverb") {
								$out["class"] = $el;
								$out["word"] = $key;
								echo json_encode($out);
								return;
							}
						}
					}					
				}
			}
		}
	}
	echo json_encode($out);
?>
