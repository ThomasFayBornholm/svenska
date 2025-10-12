<?php

$txt = $_GET["txt"];
$path = getcwd()  . "/../lists/all-only";
$regex = "/^" . $txt . "/i";
$contents = file_get_contents($path);
$entries = explode("\n", $contents);
$contents_fraser = file_get_contents(getcwd() . "/../lists/fraser-only");
$entries_fraser = explode("\n",$contents_fraser);

$out["status"] = "init";
$out["matches"] = [];
$MAX_SUGGESTIONS = 16;
$MAX_FRASER = 16;
foreach($entries as $el) {
    if (preg_match($regex, $el) && ! in_array($el, $entries_fraser)) {
        array_push($out["matches"], $el);
    }
    if (count($out["matches"]) >= $MAX_SUGGESTIONS) break;
}

$regex = "/.*" . $txt .  "$/i";
foreach($entries as $el) {
    if (preg_match($regex,$el) && !in_array($el,$out["matches"]) && ! in_array($el,$entries_fraser)) {
        array_push($out["matches"], $el);
    }
    if (count($out["matches"]) >= $MAX_SUGGESTIONS) break;
}

$regex = "/\b" . $txt . "\b/i";
$n_fraser = 0;
array_push($out["matches"], "<br>*** Fraser ***");
foreach($entries_fraser as $el) {
    if (preg_match($regex,$el)) {
        array_push($out["matches"], $el);
        $n_fraser++;
    }
    if ($n_fraser > $MAX_FRASER) break;
}
$out["status"] = "done";
echo json_encode($out);