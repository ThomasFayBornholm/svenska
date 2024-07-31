<?php
	$contents = file_get_contents("testLongDialog");		
	$lines = preg_split("/\r\n|\r|\n/", $contents);
	
	// Irvine Welsh mode, no punctuation is required.
	$contents = strtolower($contents);
	$contents = preg_replace("/[.,!()]/"," ",$contents);
	$contents = preg_replace("/  /"," ",$contents);
	$words = preg_split("/ /", $contents);
	
	
	
	$name = "verb-meta";
	$metaContents = file_get_contents($name, 'UTF-8');
	$metaDict = json_decode($metaContents, JSON_UNESCAPED_UNICODE);
	$metaKeys = array_keys($metaDict);	
	$metaVals = array();
	$excluded = ["på", "i", "eller", "av", "fram", "med", "för","av","och","om","en","ett","de","från","till","vid","presens","efter","under"];
	$excB = ["sig","in","ut","gången","ihåg","även","särskilt","ned","ner","upp","kära"];
	$excluded = array_merge($excluded, $excB);
	$dodgy = ["så","andra","hela"];
	
	$cntFound = 0;
	foreach($metaKeys as $key) {
		$tmp = $metaDict[$key];
		$tmp = preg_split("/<br>/",$tmp);
		$tmp = $tmp[0];
		$tmp = preg_split("/ /", $tmp);
		foreach($tmp as $t) {			
			if (!in_array($t,$excluded) && strlen($t) >= 2) {
				array_push($metaVals,$t);

			}
		}		
	}
	
	$i = 0;
	$cnt = count($words);
	foreach($words as $w) {		
		if (in_array($w,$metaKeys)) {
			$cntFound++;
			if (in_array($w,$dodgy)) {
				echo "<br>?-1 " . $words[$i-1];	
				echo "<br>??? " . $w;							
				if ($i + 1 < $cnt) echo "<br>?+1 " . $words[$i + 1];				
			} else {
				echo "<br>*** " . $w;				
			}
		} 
		else if (in_array($w, $metaVals)) {
			$cntFound++;
			if (in_array($w,$dodgy)) {
				echo "<br>??? " . $w;				
			} else {
				echo "<br>^^^ " . $w;
			}
		}	
		$i++;
	}
	echo "<br>found = " . $cntFound . "<br>";
function isConjugation($word, $metaDict) {
	$res = false;
	
	foreach($metaDict as $val) {
		$conjs = preg_split("/<br>/",$val);	
		foreach($excluded as $esc) {
			$conjs = str_replace($esc,"",$conjs);
		}
		
		$conjWords = preg_split("/ /",$conjs[0]);
		
		foreach($conjWords as $comp) {
			if ($comp === $word && strlen($word) >= 2) {
				echo "<br>" . $conjs[0];
				$res = true;
				break;
			}
		}		
	}
	return $res;
}
?>