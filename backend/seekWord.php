<?php
	$path = getcwd() ."/";
	$class = $_GET['class'];
	$word = $_GET['word'];
	$name = $class;
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
			if (str_contains($t, $word)) {
				echo json_encode($ind);
				return;
			}
		}
		$ind++;
	}
	echo json_encode(-1);
?>
