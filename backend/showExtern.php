<?php
$word = $_GET["word"];

$base_url = "http://svenska.se?sok=" . $word;
$urlBase = 'https://svenska.se/tri/f_so.php?sok=';		
$url = str_replace(" ", "%20", $urlBase . $word);	
$url = str_replace("ä", "%C3%A4", $url);		

$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch,CURLOPT_URL,$url);
curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/112.0");				
$response = curl_exec($ch);			
        
$responseArr = explode("\n", $response);
$matchArr = [];
foreach ($responseArr as $el) {
        if (str_contains($el,"ordklass") || str_contains($el, "wordclass")) {
            array_push($matchArr, strip_tags($el));
        }
}

echo json_encode($matchArr);
?>