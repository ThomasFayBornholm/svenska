<?php
// /*
error_reporting(E_ALL);
ini_set("display_errors", 1);
// */
$out["status"] = "Not found";
$class = $_GET["class"];
$word = $_GET["word"];
$mapping = "../sounds/mapping";
$contents = file_get_contents($mapping);
// If it exists in the mapping then download it
$lines = preg_split("/\r\n|\r|\n/", $contents);
$file = "";
$shortClass = shortenClass($class);
foreach($lines as $l) {
    if (str_contains($l, $shortClass . "/")) {
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
    $path = "../sounds/" . $shortClass . "/" . $word;
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
} else {
	$urlBase = 'https://svenska.se/tri/f_so.php?sok=';	
	$urlBaseId = 'https://svenska.se/tri/f_so.php?id=';
	$url = str_replace(" ", "%20", $urlBase . $word);	
	$url = str_replace("ä", "%C3%A4", $url);
	$ch = curl_init();		
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:129.0) Gecko/20100101 Firefox/129.0");
	$def = curl_exec($ch);			
	curl_close($ch);	
	// Remove all "mdr" class lines from def. These are not interesting to us
	$defArr = explode("\n", $def);
	//var_dump($defArr);
	$del = "";
	$def = "";
	foreach($defArr as $el) {
		if (!str_contains($el,'class="mdr"')) {						
			$def .= $del . $el;
		}
		$del = "\n";
	}
	$defArr = explode("\n", $def);
	if (strlen($def) === 0) {
		error("Failed to retrieve content from Svenska Ordlista server #1.");
	}
	// Check this resolves to the correct word
	$find = 'class="slank" href="';
	$find = 'class="slank';
	if (strpos($def,$find)) {
		// Need to do some more work to resolve to word matches
		// Use id instead of word as key
		$tmpLines = explode("\n", $def);
		$tmpClass = "";
		$isMatch = false;
		foreach($tmpLines as $l ) {		
			echo $l . "<br>";
			// Discard slutled is slutled is not desired
			$keep = $class === "slutled" || !str_contains($l, "</span>-");
			if (str_contains($l, "wordclass") && $keep) {												
				$tmpClass = getClass($l,"wordclass", true); // Discard trai
				if ($tmpClass) {					
					$tmpClass = str_replace("</span>","",$tmpClass);
					$tmpClass = substr($tmpClass[0],1);
					// Remove leading space from word class entry
					$tmpClass = getLongClassName($tmpClass);
					if (matchClass($tmpClass, $class)) {
						$def = $l;
						$isMatch = true;						
						break;
					}
				}
			}
		}		
		
		if ($isMatch) {			
			$find = "/?id=";
			$pos1 = strpos($def,$find);
			$find2="&pz=3";
			$pos2 = strpos($def,$find2);
			echo "1<br>";
			if ($pos1 && $pos2 && $pos2 > $pos1) {
				echo "2<br>";
				$pos1 = $pos1 + strlen($find);
				$lenFound = $pos2 - $pos1;
				$id = substr($def,$pos1,$lenFound);
				$url = $urlBaseId . $id;
				$ch = curl_init();		
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch,CURLOPT_URL,$url);
				curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
				curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/112.0");			
				$def = curl_exec($ch);			
				curl_close($ch);			
			}
		}
	}	
	
	if (strlen($def) === 0) {
		error("Failed to retrieve content from Svenska Ordlista server #2.");		
	}
	$defArr = explode("\n", $def);
	// Retrieve lemma number (for audio file)
	foreach($defArr as $line) {
		if (str_contains($line,'id="lnr')) {
			$tmp = str_replace('<span class="lemvarhuvud" id="lnr',"",$line);
			$tmp = str_replace('">',"",$tmp);
			$lemnr = $tmp;
			$tmp = "\n" . $lemnr . ".mp3" . ", " . shortenClass($class) . "/" . $word;
			file_put_contents("../sounds/mapping", $tmp, FILE_APPEND);
			$out["status"] = "Found";
		}
	}
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
		case "interjektion":
			return "int";
		case "plural":
			return "plu";
    }
    return $long;
}

function getClass($def,$find,$isSpan=false) {
	$found = array();
	if ($isSpan) {
		$end = "</span>";
	} else {			
		$end = "</div>";		
	}				
	$find = 'class="' . $find . '">';
	$len = strlen($find);
	$lenEnd = strlen($end);		
	$pos1 = strpos($def, $find);				
	if ($pos1 || $pos1 === 0) {
		$pos2 = strpos($def, $end, $pos1);
		if ($pos2) {
			$pos2 += $lenEnd;
				
			$start = $pos1 + $len;
			$lenFound = $pos2 - $start;			
			if ($len > 3) {						
				array_push($found, substr($def, $start, $lenFound));
			}
			
			while ($pos1 >= 0) {									
				// Advance to find any remaining matches
				$pos1 = strpos($def, $find, $pos1 + 1);
				if (!$pos1) break;
				$pos2 = strpos($def, $end, $pos2 + 1);						
				$start = $pos1 + $len;
				$lenFound = $pos2 - $start;
				if ($lenFound > 3) {						
					array_push($found, substr($def, $start, $lenFound));				
				}					
			}
		}
				
		return $found;
	}
}

function matchClass($read, $given) {
	$res = false;
	if ($read === "substantiv") {
		$res =  ($given === "substantiv_en" || $given === "substantiv_ett");
	} else {
		$res = $read === $given;
	}		
	if ($given === "plural" && $read === "substantiv") {
		$res = true;
	}
	return $res;
}

function getLongClassName($short) {
	// Default is no change
	$long = $short;
	switch ($short) {
		case "adj.":
			$long = "adjektiv";
			break;			
		case "subst.":
			$long = "substantiv";
			break;
		case "adv.":
			$long = "adverb";
			break;
	}
	return $long;
}
?>
