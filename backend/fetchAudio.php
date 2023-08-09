<?php
$out["status"] = "Not found";
$class = $_GET["class"];
$word = $_GET["word"];
$mapping = "../sounds/mapping";

$contents = file_get_contents($mapping);
// If it exists in the mapping then download it
$lines = preg_split("/\r\n|\r|\n/", $contents);
$file = "";
$class = shortenClass($class);
foreach($lines as $l) {
    if (str_contains($l, $class . "/")) {
        $tmp = explode("/", $l);
        $tmpWord = $tmp[1];
        if ($tmpWord === $word) {
            $tmp = explode(",",$l);
            $file = $tmp[0];
            $file = str_replace(".mp3","_1.mp3",$file);
            if (!str_contains($file,".mp3")) $file .= "_1.mp3";
            break;
        }
    }
}
if (strlen($file) > 0) {
    $baseURL = "https://isolve-so-service.appspot.com/pronounce?id=";
    $path = "../sounds/" . $class . "/" . $word;
    $fh = fopen($path, "w");
    $url = $baseURL . $file;
	$ch = curl_init();		
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch,CURLOPT_FILE,$fh);
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/112.0");				
	$res = curl_exec($ch);			
	curl_close($ch);	
    $out["status"] = "Found";
}
echo json_encode($out);


function shortenClass($long) {
    switch($long) {
        case "adjektiv":
            return "adj";
        case "adverb":
            return "adv";
        case "substantiv_en":
            return "en";
        case "substantiv_ett":
            return "ett"; 
    }
    return $long;
}
?>
