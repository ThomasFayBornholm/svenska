<?php
	function fmtPercent($a1, $a2) {
		if ($a2 !== 0) {
			return round($a1/$a2 * 100, 1);		
		} else {
			return 0;
		}
	}

	$path = getcwd() ."/";
	$files = ["adjektiv", "verb", "adverb", "substantiv_en", "substantiv_ett", "plural","superlativ", "preposition", "pronomen", "interjektion", "förled", "slutled", "räkneord", "subjunktion", "konjunktion"];

	$nTot = 0;
	$nAdj = 0;
	$nVerb = 0;
	$nAdverb = 0;
	$nSub_en= 0;
	$nSub_ett= 0;
	$nPlural = 0;
	$nSuperlativ = 0;
	$nPreposition = 0;
	$nInterjektion = 0;
	$nPronomen = 0;	
	$nPrefix = 0;
	$nSlutled = 0;
	$nNummer = 0;
	$nKonjunktion = 0;	
	$nSubjunktion = 0;	

	foreach($files as $f) {
		$f_base = $f;
		$f = $f . "-only";
		$infile = fopen($path . "/" . $f , "r") or die("Could not open file: " . $f);
		if (filesize($f) != 0) {
			$contents = fread($infile, filesize($f));
			$words = preg_split("/\r\n|\r|\n/", $contents);

			if ($f_base === "adjektiv") {
				$nAdj = count($words) - 1;	 
			} else if ($f_base === "verb") {
				$nVerb = count($words) - 1;
			} else if ($f_base === "adverb") {
				$nAdverb = count($words) - 1;
			} else if ($f_base === "substantiv_en") {
				$nSub_en = count($words) - 1;
			} else if ($f_base === "substantiv_ett") {
				$nSub_ett = count($words) - 1;
			} else if ($f_base === "plural") {
				$nPlural = count($words) - 1;
			} else if ($f_base === "superlativ") {
				$nSuperlativ = count($words) - 1;
			} else if ($f_base === "preposition") {
				$nPreposition= count($words) -1;
			} else if ($f_base === "interjektion") {
				$nInterjektion= count($words) -1;
			} else if ($f_base === "pronomen") {
				$nPronomen = count($words) -1;
			} else if ($f_base === "förled") {
				$nPrefix = count($words) -1;
			} else if ($f_base === "räkneord") {
				$nNummer = count($words) - 1;
			} else if ($f_base === "konjunktion") {
				$nKonjunktion = count($words) - 1;
			} else if ($f_base === "subjunktion") {
				$nSubjunktion = count($words) - 1;
			} else if ($f_base === "slutled") {
				$nSlutled = count($words) - 1;			
			}
		}
		fclose($infile);
	}
	
	$nTot = $nAdj + $nVerb + $nAdverb + $nSub_en + $nSub_ett + $nPlural + $nSuperlativ + $nPreposition + $nInterjektion + $nPronomen + $nPrefix + $nSlutled + $nKonjunktion + $nSlutled;
	$count["total"] = $nTot;
	$count["adj"] = $nAdj;
	$count["verb"] = $nVerb;
	$count["adverb"] = $nAdverb;
	$count["substantiv_en"] = $nSub_en;
	$count["substantiv_ett"] = $nSub_ett;
	$count["plural"] = $nPlural;
	$count["superlativ"] = $nSuperlativ;
	$count["preposition"] = $nPreposition;
	$count["interjektion"] = $nInterjektion;
	$count["pronomen"] = $nPronomen;
	$count["prefix"] = $nPrefix;
	$count["nummer"] = $nNummer;
	$count["konjunktion"] = $nKonjunktion;
	$count["slutled"] = $nSlutled;
	$count["subjunktion"] = $nSubjunktion;
	echo json_encode($count);
?>
