<?php
	$path = getcwd() ."/../lists/";
	$class = $_GET['class'];
	$word = $_GET['word'];
	$trail = "-def";
	$name = $class . $trail;
	$out = array("def"=>"", "meta"=>"");
	if (file_exists($path . $name)) {
		$contents = file_get_contents($path . $name, 'UTF-8');
		$dict = json_decode($contents, JSON_UNESCAPED_UNICODE);	
		
		if ($dict) {
			if (array_key_exists($word, $dict)) {
				$out["def"] = $dict[$word];
				// Check for meta data also
				if ($class != "fraser") {
					$name = $class . "-meta";
					$metaContents = file_get_contents($path . $name, 'UTF-8');
					$metaDict = json_decode($metaContents, JSON_UNESCAPED_UNICODE);
				}				
				$name = $class . "-score";
				$scoreContents = file_get_contents($path . $name, 'UTF-8');
				$scoreDict = json_decode($scoreContents, JSON_UNESCAPED_UNICODE);
				if ($class != "fraser") {
					if ($metaDict != null) {
						if (array_key_exists($word, $metaDict)) {							
							$out["meta"] = $metaDict[$word];					
						}
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
				
			}
		} else {
			$out["def"] = "Could not read json";		
		}
	}
	echo json_encode($out);
?>
