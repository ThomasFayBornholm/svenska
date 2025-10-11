<?php

$txt = $_GET["txt"];
$path = getcwd()  . "/../lists/all-only";
$regex = "/^" . $txt . "/i";
$contents = file_get_contents($path);
$entries = explode("\n", $contents);

$out["status"] = "init";
$out["matches"] = [];
$MAX_SUGGESTIONS = 5;
foreach($entries as $el) {
    if (preg_match($regex, $el)) {
        array_push($out["matches"], $el);
        if (count($out["matches"]) >= $MAX_SUGGESTIONS) break;
    }
}

$out["status"] = "done";
echo json_encode($out);