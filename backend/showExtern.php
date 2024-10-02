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
$tmp = "";
$fStart = "php?id=";
$lStart = strlen($fStart);
$i = 0;
$conjArr = array();
foreach ($responseArr as $el) {
        if (str_contains($el,"bojning")) {
            $tmp = " <i>" . strip_tags($el) . "</i>";
        } else if (str_contains($el,"ordklass") || str_contains($el, "wordclass")) {
            $tmp = strip_tags($el) . $tmp; 
            if (str_contains($el,"php?id=")) {
                $start = strpos($el,"php?id=") + $lStart;
                $end = strpos($el,"&ret=");
                $tmpID = substr($el,$start,$end-$start);
                if (str_contains($tmpID,"_")) {
                    $enum= $tmpID[-1];
                }
                $urlID = "https://svenska.se/tri/f_so.php?id=" . $tmpID;
                curl_setopt($ch, CURLOPT_URL,$urlID);
                $tmpConj = curl_exec($ch);
                $tmpConjArr = explode("\n",$tmpConj);
                $iLines = 0;
                foreach($tmpConjArr as $l) {
                    if (str_contains($l,"<sup>" . $enum)) {
                        if (str_contains($tmpConjArr[$iLines + 1],"bojning")) {
                            $tmpLine = strip_tags($tmpConjArr[$iLines + 1]);
                            array_push($conjArr,$tmpLine);
                        } else {
                            array_push($conjArr,"");
                        }
                    }
                    $iLines++;
                }

                $tmp .= " <i>" . $conjArr[$i] . "</i>";
                $i++;
                $tmp .= "; id=" . $tmpID;
            }
            $tmp = str_replace($word, " " . $word,$tmp);
            array_push($matchArr, $tmp);
            $tmp = "";
        }
}


echo json_encode($matchArr);
?>