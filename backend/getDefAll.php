<?php
function get_details($word, $dict,$class) {
	$tmp["word"] = $word;
	$tmp["def"] = $dict[$word];
	$tmp["class"] = $class;
	$path = getcwd() ."/../lists/";
	if ($class === "fraser") {
		$tmp["options"] = $word;						
	} else {
		$conj_name = $path . $class . "-conj";
		$contents_conj = file_get_contents($conj_name);
		$dict_conj = json_decode($contents_conj, JSON_UNESCAPED_UNICODE);
		if ($dict_conj) {
			if (array_key_exists($word, $dict_conj)) {
				$tmp["options"] = $dict_conj[$word];						
			}
		}
	}
	return $tmp;
}
	$path = getcwd() ."/../lists/";
	$word = $_GET['word'];
	$trail = "-def";
	$classArr = array("verb", "adjektiv", "adverb", "substantiv", "fraser", "plural", "preposition", "interjektion", "pronomen", "förled","slutled","räkneord","konjunktion","subjunktion","infinitiv");
	// Should it be associate array. Probably yes
	$outArr = array();
	foreach ($classArr as $el) {
		$name = $el . $trail;
		$contents = file_get_contents($path . $name, 'UTF-8');
		$dict = json_decode($contents, JSON_UNESCAPED_UNICODE);
		if ($dict) {
			if (array_key_exists($word, $dict)) {
				$tmp = get_details($word, $dict,$el);
				$outArr[$el . "_" . str_replace(" ","_",$word)] =  $tmp;
			}
			$word2 = $word . "-2";
			if (array_key_exists($word2,$dict)) {
				$tmp = get_details($word2, $dict,$el);
				$outArr[$el . "_" . str_replace(" ","_",$word2)] = $tmp;
			}
			$word3 = $word . "-3";
			if (array_key_exists($word3,$dict)) {
				$tmp = get_details($word3, $dict,$el);
				$outArr[$el . "_" . str_replace(" ","_",$word3)] = $tmp;
			} 
		}
	}
	$out["count"] = count($outArr);
	$out["matches"] = $outArr;
	echo json_encode($out);
?>
