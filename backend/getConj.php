<?php

function is_particle_verb_conj($word, $conjugations,$el) {
	if ($el != "verb") return false;
	foreach($conjugations as $el) {
		if (preg_match("/^" . $word . " /", $el)) return true;
	}
	return false;
}

include "error_catch.php";
	$word = $_GET["word"];
	$path = getcwd() ."/../lists/";	
	$trail = "-conj";
	
	$out = array();
	$classArr = array("substantiv","adjektiv","pronomen","verb");
	$out["matches"] = [];
	foreach ($classArr as $el) {			
		$name = $el . $trail;
		// Get word list from "-only" listing
		$contents= file_get_contents($path . $name, 'UTF-8');
		$dict = json_decode($contents, JSON_UNESCAPED_UNICODE);
		$key_arr = array_keys($dict);
		foreach($key_arr as $key) {
			$conjugations = $dict[$key];
			if ($key === $conjugations[0]) {
				$conjugations = array_slice($conjugations,1); 
			}
			if (in_array($word, $conjugations) || is_particle_verb_conj($word, $dict[$key],$el && $word != $key)) {					
				// Do not match particles for verb, e.g. "in" shall not match "löpa in"
				$tmp = [];
				$tmp["conj"]= $conjugations;
				$tmp["class"] = $el;
				$name = $el . "-def";
				$contents_def = file_get_contents($path . $name, 'UTF-8');
				$defDict = json_decode($contents_def, JSON_UNESCAPED_UNICODE);
				if (array_key_exists($key, $defDict)) {
					$tmp["def"] = $defDict[$key];
				}
				$name = $el . "-meta";
				$contents_meta = file_get_contents($path . $name, 'UTF-8');
				$metaDict = json_decode($contents_meta, JSON_UNESCAPED_UNICODE);
				if (array_key_exists($key, $metaDict)) {
					$tmp["meta"] = $metaDict[$key];
				}	
				$tmp["word"] = $key;
				$out["matches"][$el . "_" . $key] = $tmp;
			}
		}
	}

	// Idioms
	if (str_contains($word," ")) {
		$match_arr = explode(" ", $word);
		$contents = file_get_contents($path . "fraser-def");
		$idiom_dict = json_decode($contents, JSON_UNESCAPED_UNICODE);
		foreach($idiom_dict as $key => $value) {
			$key = preg_replace("/[()]/","",$key);
			$idiom_words = preg_split("/[ -.,!?\/]/",$key);
			$all_match = true;
			foreach($match_arr as $w) {
				if (!in_array($w,$idiom_words)) {
					$all_match = false;
				}
			}
			if ($all_match && $word != $key) {
				$tmp["word"] = $key;
				$tmp["meta"] = "";
				$tmp["conj"] = [$key];
				$tmp["class"] = "fraser";
				$tmp["def"] = $value;
				$out["matches"]["fraser_" . str_replace(" ","_",$key)] = $tmp;
				break;
			}
		}
	}
	echo json_encode($out);
?>