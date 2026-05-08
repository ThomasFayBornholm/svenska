<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$txt = $_GET["txt"];
$path = getcwd()  . "/../lists/all-only";
$include_phrases = true;
if (str_contains($txt,"_")) $include_phrases = false;
$txt = str_replace("_"," ",$txt);
$regex = "/^" . $txt . "/i";
$out["regex"] = $regex;
$contents = file_get_contents($path);
$entries = explode("\n", $contents);
$contents_fraser = file_get_contents(getcwd() . "/../lists/fraser-only");
$entries_fraser = explode("\n",$contents_fraser);

$out["status"] = "init";
$out["matches"] = [];
if ($include_phrases) { 
    $MAX_SUGGESTIONS = 16;
} else {
    $MAX_SUGGESTIONS = 256;
}

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

if ($include_phrases) {
    $MAX_FRASER = 16;
    $regex = "/\b" . $txt . "/i";
    $n_fraser = 0;
    array_push($out["matches"], "<br>*** Fraser ***");
    foreach($entries_fraser as $el) {
        $fmt_el = preg_replace("/[()]/","",$el);
        if (preg_match($regex,$fmt_el)) {
            array_push($out["matches"], $el);
            $n_fraser++;
        } else {
            $user_words = preg_split("/[ ,\/]/",$txt);
            if (count($user_words) > 1) {
                $el_words = preg_split("/[ ,\/]/",$el);
                $n_match = 0;
                foreach($user_words as $u_w) {
                    if (in_array($u_w,$el_words)) $n_match++;
                }
                if ($n_match === count($user_words)) {
                    array_push($out["matches"],$el);
                    $n_fraser++;
                }
            }
        }
        if ($n_fraser > $MAX_FRASER) break;
    }
}
$out["status"] = "done";
echo json_encode($out);