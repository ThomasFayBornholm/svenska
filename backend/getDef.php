<?php
	$path = getcwd() ."/";
	$class = $_GET['class'];
	$word = $_GET['word'];
	$trail = "-def";
	$name = $class . $trail;
	$contents = file_get_contents($path . $name, 'UTF-8');
	$dict = json_decode($contents, JSON_UNESCAPED_UNICODE);	
	$out = array("def"=>"", "meta"=>"");
	if ($dict) {
		if (array_key_exists($word, $dict)) {
			$out["def"] = $dict[$word];
			// Check for meta data also
			$name = $class . "-meta";
			$metaContents = file_get_contents($path . $name, 'UTF-8');
			$metaDict = json_decode($metaContents, JSON_UNESCAPED_UNICODE);
			$name = $class . "-score";
			$scoreContents = file_get_contents($path . $name, 'UTF-8');
			$scoreDict = json_decode($scoreContents, JSON_UNESCAPED_UNICODE);
			if ($metaDict != null) {
				if (array_key_exists($word, $metaDict)) {
					$out["meta"] = $metaDict[$word];
				}
			}
			if ($scoreDict != null) {
				if (array_key_exists($word, $scoreDict)) {
					$out["score"] = $scoreDict[$word];
				}
			}
			echo json_encode($out);
			return;
		} else {
			$out["def"] = "No definition found";
			echo json_encode($out);
		}
	} else {
		$out["def"] = "Could not read json";
		echo json_encode($out);
	}
?>
