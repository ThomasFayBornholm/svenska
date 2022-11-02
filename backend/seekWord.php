<?php
	$path = getcwd() ."/";
	$class = $_GET['class'];
	$word = $_GET['word'];
	$name = $class . "-only";
	$contents = file_get_contents($path . $name, 'UTF-8');
	$words = preg_split("/\r\n|\r|\n/", $contents);
	$ind = 0;
	$found = false;
	$match = -1;
	foreach($words as $w) {
		$t = str_replace(" 0", "", $w);
		$t = str_replace(" 1", "", $t);
		$t = str_replace(" 2", "", $t);
		if (strlen($word) === 1) {
			if ($word === $t[0]) {
				echo json_encode($ind);
				return;
			}
		} else {
			// Prefer exact matches
			if (str_contains($t, $word)) {
				if ($match === -1) {
					$match = $ind;
				} else {
					if ($t === $word) {
						$match = $ind;
					}
				}	
			}
		}
		$ind++;
	}
	echo json_encode($match);
?>
