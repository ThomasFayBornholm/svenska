<?php
	function fmtPercent($a1, $a2) {
		if ($a2 !== 0) {
			return round($a1/$a2 * 100, 1);		
		} else {
			return 0;
		}
	}

	$path = getcwd() ."/";
	$files = ["adjektiv", "verb", "adverb", "substantiv_en", "substantiv_ett"];

	$nTot = 0;
	$nAdj = 0;
	$nVerb = 0;
	$nAdverb = 0;
	$nSub_en= 0;
	$nSub_ett= 0;
	
	$score= 0;
	$scoreAdj = 0;
	$scoreVerb = 0;
	$scoreAdverb = 0;
	$scoreSub_en = 0;
	$scoreSub_ett = 0;

	foreach($files as $f) {
		$infile = fopen($path . "/" . $f, "r") or die("Could not open file: " . $f);
		if (filesize($f) != 0) {
			$score = 0;
			$contents = fread($infile, filesize($f));
			$words = preg_split("/\r\n|\r|\n/", $contents);
			foreach($words as $w) {
				if (strpos($w, " 1")) {
					$score += 1;
				} else if (strpos($w, " 2")) {
					$score += 2;
				}
			}

			if ($f === "adjektiv") {
				$nAdj = count($words) - 1;	 
				$scoreAdj = $score;
			} else if ($f === "verb") {
				$nVerb = count($words) - 1;
				$scoreVerb = $score;
			} else if ($f === "adverb") {
				$nAdverb = count($words) - 1;
				$scoreAdverb = $score;
			} else if ($f === "substantiv_en") {
				$nSub_en = count($words) - 1;
				$scoreSub_en = $score;
			} else if ($f === "substantiv_ett") {
				$nSub_ett = count($words) - 1;
				$scoreSub_ett = $score;
			}
		}

		fclose($infile);

	}
	$nTot = $nAdj + $nVerb + $nAdverb + $nSub_en + $nSub_ett;
	$count["total"] = $nTot;
	$count["adj"] = $nAdj;
	$count["verb"] = $nVerb;
	$count["adverb"] = $nAdverb;
	$count["substantiv_en"] = $nSub_en;
	$count["substantiv_ett"] = $nSub_ett;
	$score = $scoreAdj + $scoreVerb + $scoreAdverb + $scoreSub_en + $scoreSub_ett;
	$count["score"] = fmtPercent($score, $nTot * 2);
	$count["scoreAdj"] = fmtPercent($scoreAdj, $nAdj * 2);
	$count["scoreVerb"] = fmtPercent($scoreVerb, $nVerb * 2); 
	$count["scoreAdverb"] = fmtPercent($scoreAdverb, $nAdverb * 2); 
	$count["scoreSub_en"] = fmtPercent($scoreSub_en, $nSub_en * 2); 
	$count["scoreSub_ett"] = fmtPercent($scoreSub_ett, $nSub_ett * 2);
	echo json_encode($count);
?>
