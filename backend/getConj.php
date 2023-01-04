<?php
	$path = getcwd() ."/";
	$word = $_GET['word'];
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
	$meta = array(",","-","­","plural","singular","bestämd","ingen böjning");
	
	foreach ($classArr as $el) {
		$name = $el . $trail;
		$contents = file_get_contents($path . $name, 'UTF-8');
		$dict = json_decode($contents, JSON_UNESCAPED_UNICODE);
		foreach($dict as $key => $value) {			
			$lines = explode("<br>", $value);
			$lineOne = $lines[0];
			foreach($meta as $m) {
				$lineOne = str_replace($m,"",$lineOne);
			}	
			
			$words = explode(" ",$lineOne);
			foreach($words as $w) {
				if ($word === $w) {
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
