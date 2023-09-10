<?php
    $key = $_GET["key"];
    $allWords = explode(" ", $key);
    $cnt = count($allWords);
    $out["match"] = "";
    $out["status"] = "init";
	$name = "fraser-only";
	$contents = file_get_contents($name, 'UTF-8');
	$words = preg_split("/\r\n|\r|\n/", $contents);
    foreach($words as $w) {
        $tmpCnt = 0;
        $noSlashes = str_replace("/"," ",$w);
        $matchArr = explode(" ", $noSlashes);
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
