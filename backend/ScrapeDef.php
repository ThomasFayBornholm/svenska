<?php

$out["status"] = "init";
$word = $_GET["word"];
$base_url = "https://svenska.se/so/?sok=";

$cmd = "curl $base_url$word";
$res = shell_exec($cmd);
$res_arr = explode("\n", $res);
$id_lines = array_filter($res_arr, function($item) {
	$regex_extras ="/_[2-9]&pz=3/";
    return str_contains($item, '"wordclass"') && !preg_match($regex_extras,$item);
});

if (count($id_lines) != 1) {
	$out["status"] = "No unique ID match";	
	echo json_encode($out);
	return;
}

$id_line = reset($id_lines);
$regex_id = '/id=(\d+_\d+)/';
if (preg_match($regex_id, $id_line, $matches)) {
	$id = $matches[1];
} else {
	$out["status"] = "No ID match";	
	echo json_encode($out);
	return;
}
$out["id"] = $id;
$out["matches"] = [];
$res = shell_exec("curl 'https://svenska.se/so/?id=$id&pz=3'");
$res_arr = explode("<sup>", $res);
$tmp_arr = array_filter($res_arr, function($item) {
	return str_contains($item,'"orto"');
});
$regex_class = '/<div class="ordklass">(.*?)<\/div>/';
$regex_def = '/<span class="def">(.*?)</';
$regex_dec = '/(<span class="bojning_inline".*?<\/span><\/span>)/';
$regex_related='/<span class="hvtyp">(.*?)<\/span>.*?>(.*?)<\/a>/s';
$regex_compound = '/(<span class="mx">.*?<\/span>)<\/div>/';
$regex_history = '/<span class="etymologi".*?>(.*?)<\/div>/s';
$regex_usage = '/<span class="vt">(.*?<\/span>)<\/span>/';
$regex_example = '/<span class="syntex">(.*?)<\/span>/';
$regex_extra = '/<div class="cykel">(.*?)<\/div>/s';
$out_arr = [];

foreach($tmp_arr as $el) {
	$tmp = [];
	$class = $dec = $dec = $rel = $compound = $history = $usage = $example = $extra = "";
	if (preg_match($regex_class,$el,$matches)) $class = $matches[1];
	if (preg_match($regex_def,$el,$matches)) $def = $matches[1];
	if (preg_match($regex_dec,$el,$matches)) $dec = $matches[1];
	if (preg_match($regex_related,$el,$matches)) $rel = $matches[1] . " " . $matches[2];
	if (preg_match($regex_compound,$el,$matches)) $compound = $matches[1]; 
	if (preg_match($regex_history,$el,$matches)) $history = rtrim(strip_tags($matches[1]));
	if (preg_match($regex_usage,$el,$matches)) $usage = strip_tags($matches[1]);
	if (preg_match($regex_example,$el,$matches)) $example = strip_tags($matches[1]);
	if (preg_match($regex_extra,$el,$matches)) $extra= strip_tags($matches[1]);
	$extra = str_replace("○\n","○ ",$extra);
	$extra = str_replace("\n\n\n","\nEXEMPEL: ",$extra);
	$dec = trim(strip_tags($dec));
	$tmp["class"] = $class;
	$tmp["def"] = $def . "\n" . $rel;
	$compound = process_compound($compound);
	$tmp["meta"] = $dec . "\n" . $class;
	$tmp["more"] = "COMPOUND: " . implode(";",$compound) . "\nKONSTRUKTION: " . $usage . "\nEXEMPEL: " . $example . "\n" . $extra . "\nHISTORIK: " .  $history;
	array_push($out["matches"],$tmp);
}
$out["status"]="done";
echo json_encode($out);

function process_compound($raw) {
	$tmp_arr = explode(";",$raw);
	$out_arr = [];
	foreach($tmp_arr as $el) {
		if (str_contains($el,"hvtag")) {
			array_push($out_arr,"<l>" . trim(strip_tags($el)) . "</l>");
		} else {
			array_push($out_arr, trim(strip_tags($el)));
		}
	}
	return $out_arr;
}