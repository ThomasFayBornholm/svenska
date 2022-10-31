<?php
	$path = getcwd() ."/";
	$word = $_GET['word'];
	$trail = "-def";
	$classArr = array("verb", "adjektiv", "adverb", "substantiv_en", "substantiv_ett");
	$out = "";
	foreach ($classArr as $el) {
		$name = $el . $trail;
		$contents = file_get_contents($path . $name, 'UTF-8');
		$dict = json_decode($contents, JSON_UNESCAPED_UNICODE);
		if (strlen($out) > 0) { 
			$del = "<br>";
		} else {
			$del = "";
		}
		if (array_key_exists($word, $dict)) {
			$out = $out . $del . $el . ":<br>" . $dict[$word];
		}
	}
	if (strlen($out) === 0) {	
		echo json_encode("Could not find definition.");
	} else {
		echo json_encode($out);
	}
?>
