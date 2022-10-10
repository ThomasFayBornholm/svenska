<?php
	$path = getcwd() ."/";
	$class = $_GET['class'];
	$word = $_GET['word'];
	$name = $class . "-only";
	$contents = file_get_contents($path . $name, 'UTF-8');
	$words = preg_split("/\r\n|\r|\n/", $contents);
	$ind = 0;
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
			// Force match on first char
			if ($word[0] === '^') {
				$start=substr($word,1);
				if ($start[0] === $t[0]) {
					if (str_contains($t, $start)) {
						echo json_encode($ind);
						return;
					}
				}
			} else {
				if (str_contains($t, $word)) {
					echo json_encode($ind);
					return;
				}
			}
		}
		$ind++;
	}
	echo json_encode(-1);
?>
