<?php

$out["status"] = "init";
$word = $_GET["word"];
$base_url = "https://svenska.se/so/?sok=";

$cmd = "curl $base_url$word";
$res = shell_exec($cmd);
$res_arr = explode("\n", $res);
$test = array_slice($res_arr,0,20);
$id_lines = array_filter($res_arr, function($item) {
	$regex_extras ="/_[2-9]&pz=3/";
    return str_contains($item, '"wordclass"') && !preg_match($regex_extras,$item);
});
if (count($id_lines) != 1) {
	$out["status"] = "Error: No unique ID match";	
	echo json_encode($out);
	return;
}

$id_line = reset($id_lines);
$regex_id = '/id=(\d+_\d+)/';
if (preg_match($regex_id, $id_line, $matches)) {
	$id = $matches[1];
} else {
	$out["status"] = "Error: No ID match";	
	echo json_encode($out);
	return;
}
$out["id"] = $id;
$out["status"] = "done";
echo json_encode($out);