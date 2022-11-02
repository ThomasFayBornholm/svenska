<?php
	$path = getcwd() ."/";
	$word = $_GET['word'];
	$trail = "-def";
	$classArr = array("verb", "adjektiv", "adverb", "substantiv_en", "substantiv_ett");
	$out = "";
	// HTML escape chars
	// Choose to do dynamic HTML formatting here as easier to handle each item directly
	$tmp = "<span onclick=setClass(&#34;_c_&#34;,&#34;$word&#34;)><i>_c_</i>:</span><br>";
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
			$out = $out . $del . str_replace("_c_",$el,$tmp) . $dict[$word];
		}
	}
	if (strlen($out) === 0) {	
		echo json_encode("Could not find definition.");
	} else {
		echo json_encode($out);
	}
?>
