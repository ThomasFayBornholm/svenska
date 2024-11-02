<?php
$text = $_GET["text"];
$template = "curl -X 'POST' ";
$template .= "'http://127.0.0.1:5000/translate' ";
$template .= " -H 'accept: application/json' -H 'Content-Type: application/x-www-form-urlencoded' ";
$template .= " -d 'q=" . $text . "&source=en&target=sv&format=text&alternatives=3&api_key=xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx'";
$res = json_decode(shell_exec($template),true);
echo json_encode($res["translatedText"]);
?>