<?php
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
			if (in_array($word, $conjugations)) {					
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
	echo json_encode($out);
?>