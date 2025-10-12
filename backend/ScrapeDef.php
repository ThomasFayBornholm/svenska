<?php

include "error_catch.php";
$out["status"] = "init";
$word = $_GET["word"];
$base_url = "https://svenska.se/so/?sok=";

$cmd = "curl $base_url" . urlencode($word);
$res = shell_exec($cmd);
if ($res) {
	if (!preg_match('/<span class="orto">.*?<\/span>/',$res)) {
		// Result is not a word entry
		// Need to find id to get word entries
		$res_arr = explode("\n", $res);
		$id_lines = array_filter($res_arr, function($item) {
			$regex_extras ="/_[2-9]&pz=3/";
			return str_contains($item, '"wordclass"') && !preg_match($regex_extras,$item);
		});

		if (count($id_lines) != 1) {
			$out["status"] = "No unique ID match";	
			$out["ids"] = $id_lines;
			echo json_encode($out);
			return;
		}

		$id_line = reset($id_lines);
		$regex_id = '/id=(\d+[_\d]{2}?)/';
		if (preg_match($regex_id, $id_line, $matches)) {
			$id = $matches[1];
		} else {
			$out["status"] = "No ID match";	
			echo json_encode($out);
			return;
		}
		$out["id"] = $id;
		$res = shell_exec("curl 'https://svenska.se/so/?id=$id&pz=3'");
	}
} else {
	$out["status"] = "No match for '" . $word . "'";
	echo json_encode($out);
	return;
}

$out["matches"] = [];
$res_arr = explode("lemvarhuvud", $res);
$tmp_arr = array_filter($res_arr, function($item) {
	return str_contains($item,'"orto"');
});
$regex_class = '/<div class="ordklass">(.*?)<\/div>/';
$regex_def='/(<span class="kbetydelse".*?<\/div>)/s';
$regex_pre = '/<span class="kbetydelse".*>(.*?)<\/span>/';
$regex_context = '/<span class="hkom">(.*?)<\/span>/';
$regex_grammar = '/<span class="fkomblock">(.*?)<\/a><\/span>/s';
$regex_def_txt = '/<span class="def">(.*?)<\/span/s';
$regex_def_aux= '/<span class="deft">(.*?)<\/span/s';
$regex_dec = '/(<span class="bojning_inline".*?<\/span><\/span>)/';
$regex_related='/<span class="hvtyp">(.*?)<\/span>.*?>(.*?)<\/a>/s';
$regex_compound = '/<div class="mxblocklx">(<span class="mx">.*?)<\/div>/';
$regex_history = '/<span class="etymologi".*?>(.*?)<\/div>/s';
$regex_usage = '/(<span class="vt">.*?)<\/div>/s';
$regex_example = '/(<span class="syntex">.*?)<\/div>/s';
$regex_extra = '/<div class="cykel">(.*?)<\/div>/s';
$out_arr = [];

foreach($tmp_arr as $el) {
	$tmp = [];
	$class = $dec = $context = $grammar = $def = $def_aux = $rel = "";
	$history = $usage = $example = $extra = "";
	$compound = [];
	if (preg_match($regex_class,$el,$matches)) $class = $matches[1];
	if (preg_match($regex_def,$el,$matches)) {
		$def_chunk = $matches[1];
		if (preg_match($regex_pre,$def_chunk,$matches)) $pre = $matches[1];
		if (preg_match($regex_context,$def_chunk,$matches)) $context = process_context($matches[1]);
		if (preg_match($regex_grammar,$def_chunk,$matches)) $grammar = process_grammar($matches[1]);
		if (preg_match($regex_def_txt,$def_chunk,$matches)) $def = process_def($matches[1]);
		if (preg_match($regex_def_aux,$def_chunk,$matches)) $def_aux = process_aux($matches[1]);
		if (preg_match($regex_related,$def_chunk,$matches)) $related = $matches[1];
	}
	if (preg_match($regex_dec,$el,$matches)) $dec = $matches[1];
	if (preg_match($regex_compound,$el,$matches)) $compound = process_compound($matches[1]);
	if (preg_match($regex_history,$el,$matches)) $history = process_history($matches[1]);
	if (preg_match($regex_usage,$el,$matches)) $usage = process_usage($matches[1]);
	if (preg_match($regex_example,$el,$matches)) $example = strip_tags($matches[1]);
	if (preg_match($regex_extra,$el,$matches)) $extra= strip_tags($matches[1]);
	$extra = str_replace("○\n","○ ",$extra);
	$extra = str_replace("\n\n\n","<br>EXEMPEL: ",$extra);
	$tmp["class"] = $class;
	if (strlen($pre) > 0) $pre = $pre . " ";
	$tmp["def"] = $pre . $context . $grammar . $def . $def_aux . "<br>" . $rel;
	$tmp["options"] = process_options($dec);
	$dec = process_dec(trim(strip_tags($dec)));
	$tmp["meta"] = $word . " " . $dec;
	$tmp["more"] ="";
	if (count($compound) > 0) $tmp["more"] .= "SAMMANSÄTTN./AVLEDN.: " . implode("; ",$compound);
	if (strlen($usage > 0)) $tmp["more"].= "<br>KONSTRUKTION: <br>" . $usage;
	if (strlen($example > 0)) $tmp["more"].= "<br>EXEMPEL: " . $example;
	if (strlen($extra) > 0) $tmp["more"].= "<br>" . $extra;
	$tmp["more"].= "<br>HISTORIK: " .  $history;
	array_push($out["matches"],$tmp);
}
$out["status"]="done";
echo json_encode($out);

function process_compound($raw) {
	$tmp_arr = explode(";",$raw);
	$out_arr = [];
	foreach($tmp_arr as $el) {
		if (str_contains($el,"hvtag")) {
			array_push($out_arr,"<b>" . trim(strip_tags($el)) . "</b>");
		} else {
			array_push($out_arr, trim(strip_tags($el)));
		}
	}
	return $out_arr;
}

function process_history($raw) {
	$out = $raw;
	$out = preg_replace('/<span class="hvhomo">[\d+]<\/span>/'," ",$out);
	$out = preg_replace_callback('/<a class="hvtag".*?>(.*?)<\/a>/', function($matches) {
		$tmp = preg_replace("/\d/", "",$matches[1]);
		$nbsp = "\xC2\xA0"; 
		return " _b_" . trim(str_replace($nbsp,"",$tmp)) . "_be_"; // '_b_' denotes start of a bold linked word and '_be_' the end of it
	}, $out);
	$out = str_replace("\n","",$out);
	$out = strip_tags($out);
	$out = trim($out);
	$out = str_replace("!!","",$out);
	$out = str_replace("_b_","<b>",$out);
	$out = str_replace("_be_","</b>",$out);
	return $out;
}
function process_usage($raw) {
	$out = strip_tags($raw);
	$out = str_replace("\n","<br>",$out);
	$out = str_replace("någon","NÅGON",$out);
	$out = str_replace("något","NÅGOT",$out);
	$out = str_replace("några","NÅGRA",$out);
	$out = str_replace("sats","SATS",$out);
	return $out;
}

function process_def($raw) {
	$out = $raw;
	$out = preg_replace('/<a class="hvtag".*>(.*?)<\/a>/',"<b>" . "$1" . "</b>",$out);
	return $out;
}

function process_grammar($raw) {
	$out = $raw;
	$out = str_replace('<span class="fkom2">',"",$out);
	$out = preg_replace('/<a class="hvtag".*>(.*?)<\/a>/'," <b>" . "$1" . "</b>",$out);
	$out = "(" . $out . ") ";
	return $out;
}

function process_dec($raw) {
	$tmp = $raw;
	// Replace all formatting invisible spaces
	$tmp = str_replace("\xC2\xA0", "", $tmp);
	$tmp = str_replace('&nbsp;', '', $tmp);
	$tmp = preg_replace('/\x{00A0}/u', '', $tmp);
	$tmp= str_replace("\xC2\xAD", '', $tmp); // Soft hyphen
	return $tmp;
}

function process_options($raw) {
	$tmp = $raw;
	// Replace all formatting invisible spaces
	$tmp = str_replace("\xC2\xA0", "", $tmp);
	$tmp = str_replace('&nbsp;', '', $tmp);
	$tmp = preg_replace('/\x{00A0}/u', '', $tmp);
	$tmp= str_replace("\xC2\xAD", '', $tmp); // Soft hyphen
	$pattern = '/<span class="bojning">(.*?)<\/span>/';
	preg_match_all($pattern, $tmp, $matches);
	return $matches[1];
}

function process_aux($raw) {
	$tmp = $raw;
	if (strlen($tmp) > 0) {
		$tmp = " {" . $tmp. "}";
	}
	return $tmp;
}

function process_context($raw) {
	$tmp = $raw;
	if (strlen($tmp) > 0) $tmp = "<" . $tmp . "> ";
	return $tmp;
}