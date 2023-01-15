<?php
	$path = getcwd() ."/";
	$class = $_GET['class'];
	$header = $_GET['header'];
	$word = $_GET['word'];
	$more = $_GET['more'];
	$trail = "-more";
	if (strlen($more) > 0) {
		$name = $class . $trail;
		$contents = file_get_contents($path . $name, 'UTF-8');
		$arr = json_decode($contents,true);
		// Replicate the dictionary headers		
		$existing = "";
		if ($arr != null) {
			if (array_key_exists($word,$arr)) {
				$existing = $arr[$word] . "<br>";
			}
		}
		$hFind = $header . ":<br>";
		// Existing information is overwritten
		if (str_contains($existing,$hFind)) {
			$start = strpos($existing, $hFind);
			$nextHeader = strpos($existing,":<br>",$start);
			/*
			$len = strpos($existing, ":<br>",$start + strlen($hFind)) - $start;			
			$replace = substr($existing, $start, $len);
			
			$existing = str_replace($replace,$hFind . $more,$existing);			
			*/
			$str = "";
		} else {
		// New information is added
			$str = $header . ":<br>" . $more;
		}
		if (substr($str,-4) === "<br>") {
			$str = substr($str,0,(strlen($str)-4));
		}
		// Crude hack for now.
		$existing = str_replace("<br><br>","<br>",$existing);
		$arr[$word] = $existing . $str;
		file_put_contents($path. $name, json_encode($arr));
	}
	echo json_encode("");
?>
