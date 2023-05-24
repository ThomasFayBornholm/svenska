<?php
	$path = getcwd() ."/";
	$class = $_GET['class'];
	$word = $_GET['word'];
	$trail = "-more";
	$name = $class . $trail;
	$contents = file_get_contents($path . $name, 'UTF-8');
	$dict = json_decode($contents, JSON_UNESCAPED_UNICODE);	
	$out = "";
	$tmp = "";
	if ($dict) {
		if (array_key_exists($word, $dict)) {
			$tmp = $dict[$word];
		}
	}
	
	$tmpArr = explode("<br>",$tmp);
	
	$isCon = false;
	$del = "";	
	foreach($tmpArr as $l) {		
		if (str_contains($l,"KONSTRUKTION:")) {						
			$isCon = true;
		} else if (str_contains($l,"EXEMPEL:")) {
			$isCon = false;
		} else if (str_contains($l,"HISTORIK:")) {
			$isCon = false;
		}
		
		if ($isCon) {
			$out .= $del . "○ " . $l;
		} else {
			$out .= $del . $l;
		}
		$del = "<br>";
	}	
	// Hack 
	$out = str_replace("○ KONSTRUKTION:" ,"KONSTRUKTION: ",$out);
	$out = str_replace("○","&nbsp;&nbsp;○", $out);
	echo json_encode($out);
?>
