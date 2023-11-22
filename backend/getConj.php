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
	
	function wordMatch($word, $comp) {
		$match1 = $word === $comp;
		$fuzzyE = str_replace("é","e",$comp);
		$fuzzyE = str_replace("é","e",$fuzzyE);
		$match2 = $word === $fuzzyE;				
		$res = $match1 || $match2;
		return $res;
	}
	
	function containsWord($word, $conjArr) {
		$res = false;		
		foreach($conjArr as $el) {			
			if (wordMatch($word, $el)) {
				$res = true;
				break;
			}
		}		
		return $res;
	}
	
	function containsAllWords($wordList, $conjArr) {
		$nWords = count($wordList);
		if (count($conjArr) === 0) {
			return false;
		}
		
		$nMatches = 0;
		
		foreach($wordList as $w) {			
			foreach ($conjArr as $el) {
				if (wordMatch($el,$w)) {
					$nMatches++;
					break;
				}
			}
		}
		$res = $nMatches === $nWords;
		return $res;
	}
	
	function getConjugations($key, $dict) {				
		if (array_key_exists($key, $dict)) {
			$lines = explode("<br>", $dict[$key]);					
			$lineOne = $lines[0];
			$lineOne = repText($lineOne,$GLOBALS['replacements'],$key);
			// First explosion just to get root word
			$conjugations = explode(" ", $lineOne);				
			$root = $conjugations[0];			
			$lineOne = str_replace(", presens", "", $lineOne);
			$lineOne = str_replace("eller ", "", $lineOne);
			$lineOne = str_replace("~", $root, $lineOne);
			$conjugations = explode(" ", $lineOne);
		} else {
			// Empty, no conjugations could be retrieved;
			$conjugations = array();
		}
		if (count($conjugations) > 10) {
			$conjugations = array();
		}		
		return $conjugations;
	}
	
	$path = getcwd() ."/";
	$word = $_GET['word'];
	$rest = "";		
	$nFind = 1;
	if (str_contains($word, " ")) {
		$tmp = explode(" ", $word);
		$wordRoot = $tmp[0];
		$rest = "";
		$nFind = count($tmp);
		$del = "";
		for ($i = 1; $i < $nFind; $i++) {
			$rest .= $del . $tmp[$i];
			$del = " ";
		}
	}
	
	$class = $_GET['class'];
	$trail = "-meta";
	if ($class === "all") {
		$classArr = array("verb", "adjektiv", "adverb", "substantiv_en", "substantiv_ett", "plural", "preposition", "interjektion", "pronomen", "förled","slutled","räkneord","konjunktion","subjunktion");
	} else {
		$classArr = array($class);
	}
	
	$out = array();
	$out["class"]="";
	$out["word"]="";
	$GLOBALS["replacements"] = array("eller ",", komparativ","bestämd form ",", superlativ","supinum ","objektsform ","i vissa stelnade uttryck används ","presens ","även åld. ",",","-","­","plural","singular","bestämd","ingen böjning","<i>","</i>","genitiv ","dativ ");	

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
		if ($el === "verb" && $nFind > 1) {
			$restList = explode(" ", $rest);			
			foreach($wordList as $key) {				
				$conjugations = getConjugations($key, $dict);					
				if (containsWord($wordRoot, $conjugations)) {												
					if (containsAllWords($restList, $conjugations)) {
						$out["class"] = $el;
						$out["word"] = $key;
						echo json_encode($out);
						return;
					}
				}
			}
		// Adverbs need to give the following behaviour:
		// "i morse": 'i morse eller imorse'
		// "imorse": 'i morse eller imorse'
		} else if ($el === "adverb" && strlen($rest) > 0 && $word[0] === 'i') {			
			$restList = explode(" ", $rest);						
			foreach($wordList as $key) {				
				$conjugations = getConjugations($key, $dict);									
				if (containsAllWords($restList, $conjugations) && $key[0] === "i") {					
					$out["class"] = $el;
					$out["word"] = $key;
					echo json_encode($out);
					return;
				}
			}
		} else {			
			foreach($wordList as $key) {
				$conjugations = getConjugations($key, $dict);					
				if (containsWord($word, $conjugations)) {					
					$out["class"] = $el;
					$out["word"] = $key;
					echo json_encode($out);
					return;
				}
			}
		}
	}
	echo json_encode($out);
?>
