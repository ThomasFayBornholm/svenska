<?php
	$path = getcwd() . "/../lists/";
	$class = $_GET['class'];
	$tmpWord = $_GET['word'];
	$word = "";
	$regex = "/[a-zäöå\/() ,.\-éè!?]/i";
	for ($i = 0; $i < strlen($tmpWord); $i++) {
		if (preg_match($regex,$tmpWord[$i])) {
			$word .= $tmpWord[$i];
		}
	}
	// Word after current hightlighted word
	$INC = $_GET['inc']+3;
	$name = $class . "-only";
	$contents = file_get_contents($path . $name, 'UTF-8');
	$words = preg_split("/\r\n|\r|\n/", $contents);
	$ind = 0;
	$found = false;
	$match = -1;
	// Loop from current position
	// First non-exact match is prioritised over later ones
	// Exact match is always returned when present 
	$match2 = -1;
	$fuzzyWord = str_replace("é","e",$word);
	$fuzzyWord = str_replace("è","e",$fuzzyWord);
	$fuzzyWord = str_replace("à","a",$fuzzyWord);
	
	foreach($words as $w) {
		// Why the special case for single character word?
		if (strlen($word) === 1) {
			if ($word === $w[0]) {
				echo json_encode($ind);
				return;
			}
		} else {
			$fuzzyE = str_replace("é","e",$w);
			$fuzzyE = str_replace("è","e",$fuzzyE);
			$fuzzyE = str_replace("à", "a",$fuzzyE);


			// Prefer exact matches		
			if ($w === $word || $fuzzyE === $fuzzyWord) {
				echo json_encode($ind);
				return;
			}
				/* No fuzzy matches now, prefer to match conjugations instead
				// First non-exact match after current selection prioritised 
				if ($ind > $INC) {
					if ($match === -1) {
						$match = $ind;
					}
				// First non-exact match before current selection
				} else if ($ind < $INC) {
					if ($match2 === -1) {
						$match2 = $ind;
					}
				} 
				*/
		}
		$ind++;
	}
	
	if ($match === -1 && $match2 != -1) {
		$match = $match2;
	}
	
	echo json_encode($match);
?>
