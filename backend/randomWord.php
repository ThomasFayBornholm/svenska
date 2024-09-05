<?php
	$path = getcwd() ."/";
	$class = $_GET['class'];	
	$name = $class . "-score";
	$contents = file_get_contents($path . $name, 'UTF-8');
	$tmp = array();
	if ($class != "all") {
		$dict = json_decode($contents, JSON_UNESCAPED_UNICODE);

		foreach($dict as $key=>$val) {		
			$score = (int)$val;
			if ($score < 2 ) {
				array_push($tmp, $key);
			}
		}
	} else {
		// How should "all" class be displayed at random
		// Just pick a random spot in dictionary
		$fileName = $path . $class . "-only";
		$contents = file_get_contents($fileName, 'UTF-8');
		$words = preg_split("/\r\n|\r|\n/", $contents);
		$cnt = count($words);
		$rand = random_int(0,$cnt-1);
		$res["ind"] = $rand;
		$res["word"] = $words[$rand];
		echo json_encode($res);
		return;
	}

	$randNum = rand(0,count($tmp));
	$tmpWord = $tmp[$randNum];

	$arr = array();
	$fileName = $path . $class . "-only";
	$contents = file_get_contents($fileName, 'UTF-8');
	$words = preg_split("/\r\n|\r|\n/", $contents);
	
	$i = 0;
	$res["ind"]=0;
	$res["word"]="not found";
	foreach($words as $w) {		
		$i++;		
		if ($w === $tmpWord) {
			$res["ind"]=$i;
			$res["word"] = $tmpWord;
			echo json_encode($res);	
			return;
		}
	}
	echo json_encode($res);
?>
