<?php
	// Match based either on regex or if all words in received key are also in listing key.
    $key = $_GET["key"];
	$regex = '/' . str_replace("/","\/",$key) . '/i';
    $allWords = explode(" ", $key);
    $cnt = count($allWords);
	$noPunc = str_replace("/"," ",$allWords);
	$noPunc = str_replace(")","",$noPunc);
	$noPunc = str_replace("(","",$noPunc);
	$noPunc = str_replace(",","",$noPunc);
	$noPunc = str_replace("?","",$noPunc);
	$noPunc = str_replace("!","",$noPunc);
	$allWords = $noPunc;
    $out["match"] = "";
    $out["status"] = "init";
	$name = "fraser-only";
	$contents = file_get_contents($name, 'UTF-8');
	$words = preg_split("/\r\n|\r|\n/", $contents);
    foreach($words as $w) {
		if (preg_match($regex,$w)) {
			$out["match"] = $w;
            $out["status"] = "success";
            echo json_encode($out);
            return;
		}
        $tmpCnt = 0;
        $noPunc = str_replace("/"," ",$w);
		$noPunc = str_replace(")","",$noPunc);
		$noPunc = str_replace("(","",$noPunc);
		$noPunc = str_replace(",","",$noPunc);
		$noPunc = str_replace("?","",$noPunc);
		$noPunc = str_replace("!","",$noPunc);
        $matchArr = explode(" ", $noPunc);
        foreach($allWords as $el) {
            if (in_array($el, $matchArr)) {
                $tmpCnt++;
            }
        }
        if ($tmpCnt === $cnt) {
            $out["match"] = $w;
            $out["status"] = "success";
            echo json_encode($out);
            return;
        }
    }
    echo json_encode($out);
?>
