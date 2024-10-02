<?php
	$path = getcwd() ."/../lists/";
	$class = $_GET['class'];
	$word = $_GET['word'];
	
	// No characters that are not visible to user in key
	$word = str_replace("­","",$word);
	$def = $_GET['def'];
	$meta = $_GET['meta'];
	$score = $_GET['score'];
	$more = "";
	if (isset($_GET['more'])) {
		$more = $_GET['more'];
	}
			
	$trail = "-def";
	if (strlen($def) > 0) {
		$name = $class . $trail;
		$contents = file_get_contents($path . $name, 'UTF-8');
		$arr = json_decode($contents,true);		
		$arr[$word] = $def;
		ksort($arr);
		$outDef = "{" . PHP_EOL;
		$delimDef = "";
		foreach ($arr as $key => $value) {
			$val = str_replace('"',"'",$value);
 			$outDef .= $delimDef . '"' . $key . '": "' . $val . '"';
			$delimDef = ',' . PHP_EOL;
		}	
		$outDef .= PHP_EOL . "}";
		// Write as string rather than JSON to allow new line as delimiter
		file_put_contents($path . $name, $outDef);
	}
	
	if ($class != "fraser") {
		// Fraser class does not benefit from "more" or "meta" fields so exclude
		if (strlen($meta) > 0) {
			$name = $class . "-meta";
			$contents = file_get_contents($path . $name, 'UTF-8');
			$arr = json_decode($contents,true);
			ksort($arr);
			$outMeta = "{" . PHP_EOL;
			$delimMeta = "";
			foreach($arr as $key => $value) {
				$value = str_replace('"',"'",$value);
				$outMeta .= $delimMeta . '"' . $key . '": "' . $value . '"';
				$delimMeta = "," . PHP_EOL;
			}
			$outMeta .= PHP_EOL . "}";
			file_put_contents($path . $name, $outMeta);
		}
		if (strlen($more) > 0) {	
			$name = $class . "-more";
			$contents = file_get_contents($path . $name, 'UTF-8');
			$arr = json_decode($contents,true);
			$key = str_replace(" ","-", $word) . "_0";
			$arr[$key] = $more;
			ksort($arr);
			$outMore = "{" . PHP_EOL;
			$delimMore = "";
			foreach($arr as $key => $value) {
				$value = str_replace('"',"'",$value);
				$value = str_replace('\\','\\\\',$value);
				$outMore .= $delimMore . '"' . $key . '": "' . $value . '"';
				$delimMore = "," . PHP_EOL;
			}
			$outMore .= PHP_EOL . "}";
			file_put_contents($path. $name, $outMore);
		}
	}
	if (strlen($score) > 0) {
		$name = $class . "-score";
		$contents = file_get_contents($path . $name, 'UTF-8');
		$arr = json_decode($contents,true);
		$arr[$word] = $score;
		ksort($arr);
		$outScore = "{" . PHP_EOL;
		$delimScore = "";
		foreach ($arr as $key => $value)   {
			$outScore .= $delimScore . '"' . $key . '": "' . $value . '"';
			$delimScore = "," . PHP_EOL;
		}
		$outScore .= PHP_EOL . "}";
		file_put_contents($path . $name, $outScore);
	}
	echo json_encode("success");
?>
