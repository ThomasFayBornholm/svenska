<?php

include "error_catch.php";
$out["status"] = "init";
$word = $_GET["word"];
$out["word"] = $word;
$cmd = "/srv/venv/scrape/bin/python /srv/venv/t.py '" . $word . "' 2> /tmp/log_scrape";
$out["cmd"] = $cmd;
$out["cmd_2"] = "sudo -u www-data -s " . $cmd;
$res = shell_exec($cmd);
$out["debug"] = $res;
$out["matches"] = json_decode($res, true);
$out["status"]="done";
echo json_encode($out);
