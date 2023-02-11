<?php
	function repText($inText,$replacements,$word) {
		$tmp = $inText;
		foreach($replacements as $r) {
			if ($word != $r) {
				$tmp = str_replace($r,"",$tmp);
			}
		}
		return $tmp;
	}
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
	$replacements = array("eller ","komparitiv ","superlativ ","supinum ","objektsform ","i vissa stelnade uttryck används ","presens ","även åld. ",",","-","­","plural","singular","bestämd","ingen böjning","<i>","</i>");
	$test = "ge gav, gett, given givna, presens ger även åld. giva gav, givit, given givna, presens giver";

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
				$lineOne = repText($lineOne,$replacements,$word);
							
				
				$words = explode(" ",$lineOne);
				
				$firstConj = $words[0];
				foreach($words as $w) {

					$w = str_replace("~",$firstConj, $w);					
					$fuzzyE = str_replace("é","e",$w);
					$fuzzyE = str_replace("é","e",$fuzzyE);
					
					if ($word === $w or $word === $fuzzyE or (str_contains($lineOne,$word) and $el === "adverb")) {		
						if (str_contains($lineOne,$rest)) {				
							$keyCount = count(explode(" ", $key));	
							// Enforce same number of words in "stored key" (from file) and "search word" (from user)
							// Exception of above for adverbs, e.g. "i kväll" and "ikväll" shall be equivalent.
							if ($nFind === $keyCount || $el === "adverb") {
								if ($el === "adverb") {
																									
								}
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
