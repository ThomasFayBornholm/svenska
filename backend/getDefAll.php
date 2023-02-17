<?php
	$path = getcwd() ."/";
	$word = $_GET['word'];
	$trail = "-def";
		$classArr = array("verb", "adjektiv", "adverb", "substantiv_en", "substantiv_ett", "fraser", "plural", "preposition", "interjektion", "pronomen", "förled","slutled","räkneord","konjunktion","subjunktion");
	$out = "";
	// HTML escape chars
	// Choose to do dynamic HTML formatting here as easier to handle each item directly
	$tmp = "_c_:<br>";
	// Should it be associate array. Probably yes
	$outArr = array();
	foreach ($classArr as $el) {
		$name = $el . $trail;
		$contents = file_get_contents($path . $name, 'UTF-8');
		$dict = json_decode($contents, JSON_UNESCAPED_UNICODE);
		if ($dict) {
			if (strlen($out) > 0) { 
				$del = "<br>";
			} else {
				$del = "";
			}
			if (array_key_exists($word, $dict)) {
				$outArr[$el] = $dict[$word];			
			// Handle trailing special character 'é' when the user enters plain 'e'
			} else if (str_contains($word,'e')) {				
				$pos = strpos($word,'e');
				$wordFuzzy = substr($word,0,$pos) . 'é';
				if (array_key_exists($wordFuzzy, $dict)) {
					$outArr[$el] = $dict[$wordFuzzy];
				}
			}
		}
	}
	echo json_encode($outArr);
?>
