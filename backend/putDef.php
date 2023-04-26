<?php
	$path = getcwd() ."/";
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
		
	// Delimet with either "||" or "<<" to give easy support for UK or SV keyboard
	if (strlen($more) > 0) {	
		$name = $class . "-more";
		$contents = file_get_contents($path . $name, 'UTF-8');
		$arr = json_decode($contents,true);
		$key = str_replace(" ","-", $word) . "_0";
		$arr[$key] = $more;
		file_put_contents($path. $name, json_encode($arr));
	}
	
	$trail = "-def";
	if (strlen($def) > 0) {
		$name = $class . $trail;
		$contents = file_get_contents($path . $name, 'UTF-8');
		$arr = json_decode($contents,true);		
		$arr[$word] = $def;
		file_put_contents($path. $name, json_encode($arr));
	}
	
	if ($class != "fraser") {
		if (strlen($meta) > 0) {
			$name = $class . "-meta";
			$contents = file_get_contents($path . $name, 'UTF-8');
			$arr = json_decode($contents,true);
			$arr[$word] = $meta;
			file_put_contents($path . $name, json_encode($arr));
		}
	}
	if (strlen($score) > 0) {
		$name = $class . "-score";
		$contents = file_get_contents($path . $name, 'UTF-8');
		$arr = json_decode($contents,true);
		$arr[$word] = $score;
		file_put_contents($path . $name, json_encode($arr));
	}
	echo json_encode("success");
?>
