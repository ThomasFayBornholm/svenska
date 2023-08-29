<?php
	$word = $_GET["word"];
	$class = $_GET["class"];
	$diff = $_GET["diff"];
	$path = getcwd() ."/";
	$fName = $class . "-score";
	$contents = file_get_contents($path . $fName, 'UTF-8');
	$dict = json_decode($contents, JSON_UNESCAPED_UNICODE);
	$out = array();
	$out["res"] = "fail";
	$old = 0;
	if (array_key_exists($word, $dict)) {
		$old = $dict[$word];
	}
	$new = (int)$old + $diff;
	if ($new > 2) $new = 0;
	if ($new < 0) $new = 2;
	$out["old"]=$old;
    $out["new"]=$old;
	$dict[$word]=$new;
	$res = file_put_contents($path. $fName, json_encode($dict));
    if ($res) {
        $out["res"] = "success";		
        $out["new"]=$new;
    }
	echo json_encode($out);
?>
