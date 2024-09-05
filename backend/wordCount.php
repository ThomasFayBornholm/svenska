<?php
	function fmtPercent($a1, $a2) {
		if ($a2 !== 0) {
			return round($a1/$a2 * 100, 1);		
		} else {
			return 0;
		}
	}

	$path = getcwd() ."/";
	$files = ["all","adjektiv", "verb", "adverb", "substantiv_en", "substantiv_ett", "fraser", "plural", "preposition", "pronomen", "interjektion", "förled", "slutled", "räkneord", "subjunktion", "konjunktion","infinitiv","artikel"];

	$nTot = 0;
	$nAll = 0;
	$nAdj = 0;
	$nVerb = 0;
	$nAdverb = 0;
	$nFraser = 0;
	$nSub_en= 0;
	$nSub_ett= 0;
	$nPlural = 0;
	$nPreposition = 0;
	$nInterjektion = 0;
	$nPronomen = 0;	
	$nPrefix = 0;
	$nSlutled = 0;
	$nNummer = 0;
	$nKonjunktion = 0;	
	$nSubjunktion = 0;	
	$nInfinitiv = 0;
	$nArtikel = 0;

	foreach($files as $f) {
		$f_base = $f;
		$f = $f . "-only";
		$infile = fopen($path . "/" . $f , "r") or die("Could not open file: " . $f);
		if (filesize($f) != 0) {
			$contents = fread($infile, filesize($f));
			$words = preg_split("/\r\n|\r|\n/", $contents);
			if ($f_base === "all") {
				$nAll = count($words);
			} else if ($f_base === "adjektiv") {
				$nAdj = count($words);	 
			} else if ($f_base === "verb") {
				$nVerb = count($words);
			} else if ($f_base === "adverb") {
				$nAdverb = count($words);
			} else if ($f_base === "substantiv_en") {
				$nSub_en = count($words);
			} else if ($f_base === "substantiv_ett") {				
				$nSub_ett = count($words);
			} else if ($f_base === "fraser") {
				$nFraser = count($words);			
			} else if ($f_base === "plural") {
				$nPlural = count($words);
			} else if ($f_base === "preposition") {
				$nPreposition= count($words);
			} else if ($f_base === "interjektion") {
				$nInterjektion= count($words);
			} else if ($f_base === "pronomen") {
				$nPronomen = count($words);
			} else if ($f_base === "förled") {
				$nPrefix = count($words);
			} else if ($f_base === "räkneord") {
				$nNummer = count($words);
			} else if ($f_base === "konjunktion") {
				$nKonjunktion = count($words);
			} else if ($f_base === "subjunktion") {
				$nSubjunktion = count($words);
			} else if ($f_base === "slutled") {
				$nSlutled = count($words);			
			} else if ($f_base === "infinitiv") {
				$nInfinitiv = count($words);
			} else if ($f_base === "artikel") {
				$nArtikel = count($words);
			}
		}
		fclose($infile);
	}
		
	
	$count["all"] = $nAll;
	$count["adj"] = $nAdj;
	$nTot += $nAdj;
	$count["verb"] = $nVerb;
	$nTot += $nVerb;
	$count["adverb"] = $nAdverb;
	$nTot += $nAdverb;
	$count["fraser"] = $nFraser;
	$nTot += $nFraser;
	$count["substantiv_en"] = $nSub_en;
	$nTot += $nSub_en;
	$count["substantiv_ett"] = $nSub_ett;
	$nTot += $nSub_ett;
	$count["plural"] = $nPlural;
	$nTot += $nPlural;
	$count["preposition"] = $nPreposition;
	$nTot += $nPreposition;
	$count["interjektion"] = $nInterjektion;
	$nTot += $nInterjektion;
	$count["pronomen"] = $nPronomen;
	$nTot += $nPronomen;
	$count["prefix"] = $nPrefix;
	$nTot += $nPrefix;
	$count["nummer"] = $nNummer;
	$nTot += $nNummer;
	$count["konjunktion"] = $nKonjunktion;
	$nTot += $nKonjunktion;
	$count["slutled"] = $nSlutled;
	$nTot += $nSlutled;
	$count["subjunktion"] = $nSubjunktion;
	$nTot += $nSubjunktion;
	$count["infinitiv"] = $nInfinitiv;
	$nTot += $nInfinitiv;
	$count["artikel"] = $nArtikel;
	$nTot += $nArtikel;
	
	$count["total"] = $nTot;
	echo json_encode($count);
?>
