<?php
	include "error_catch.php";
	$class = $_GET['class'];
	$class = preg_replace("/_.*/","",$class);
	$word = $_GET['word'];	
	// No characters that are not visible to user to be present in storage key
	$word = str_replace("­","",$word);
	// Internal enumeration takes form '-#'
	$word = str_replace("(","-",$word);
	$word = str_replace(")","",$word);
	$meta = $_GET['meta'];
	$def = $_GET['def'];
	$more = $_GET['more'];
	
	// Add word to listing if not already available
	$path = getcwd() . "/../lists/";
	$fileName = $path . $class . "-only";
	$infile = fopen($fileName, "r") or die("Could not open file: " . $fileName);
	if (filesize($fileName) === 0) {
		$contents = "";
	} else {
		$contents = fread($infile, filesize($fileName));
	}
	fclose($infile);
	$outfile = fopen($fileName, 'w') or die("Could not open file: " . $fileName);
	
	$elements = preg_split("/\r\n|\r|\n/", $contents);
	$out = "";	
	$placed = false;
	$del = "";
	// First in list case	
	
	$del = "";
	foreach($elements as $w) {				
		// Insertion point
		if ($w === $word) {			
			$out .= $del . $word;			
			$placed = true;
		} else if ($w > $word && !$placed) {
			$out .= $del . $word;				
			$out .= $del . $w;
			$placed = true;
		} else {				
			$out .= $del . $w;				
		}
		$del = "\n";
	}
	
	// For case where word is last in alphabetical sorting
	if ($placed === false) {
		$out = $out . $del . $word;
	}
	
	fwrite($outfile,$out);
	fclose($outfile);
	
	// Add word to all listing if needed
	$allName = $path . "all-only";
	$allFile = fopen($allName, "r") or die ("Could not open file: " . $allName);
	$contents = fread($allFile, filesize($allName));
	fclose($allFile);
	$elements = preg_split("/\r\n|\r|\n/", $contents);
	$out = "";
	$placed = false;
	$del = "";
	
	foreach($elements as $w) {
		// Insertion point
		if ($w === $word) {
			$out = $out . $del . $word;
			$placed = true;
		} else if ($w > $word && !$placed) {
			$out = $out . $del . $word;
			$del = "\n";
			$out = $out . $del. $w;
			$placed = true;
		} else {
			$out = $out . $del . $w;
			$del = "\n";
		}
	}
	// If word is alphabetically last in listing
	if ($placed === false) {
		$out = $out . $del . $word;
	}
	
	$outfileAll = fopen($allName, "w") or die("Could not open file: " . $allName);
	fwrite($outfileAll, $out);
	fclose($outfileAll);
				
	// Populate 'meta', 'def' and 'more' listings, overwrite performed if entry already exists
	$trail = "-def";
	if (strlen($def) > 0) {
		$name = $path . $class . $trail;
		if (!file_exists($name)) {
			fopen($name, "w");
		}
		$contents = file_get_contents($name, 'UTF-8');
		$arr = json_decode($contents, true);
		$arr[$word] = $def;
		ksort($arr);
		$outDef = "{" . PHP_EOL;
		$delimDef = "";
		foreach ($arr as $key => $value) {
			$val = str_replace('"',"'",$value);
 			$outDef .= $delimDef . '"' . $key . '": "' . $val . '"';
			$delimDef = ',' . PHP_EOL;
		}	
		$outDef .= PHP_EOL . "}";
		// Write as string rather than JSON to allow new line as delimiter
		file_put_contents($name, $outDef);
	}
	
	if ($class != "fraser") {
		// "Fraser" class does not benefit from "more" or "meta" fields so exclude
		if (strlen($more) > 0) {
			$key_suffix = "_0";
			
			$moreSplit = explode("||",$more);	
			$name = $path . $class . "-more";
			if (!file_exists($name)) fopen($name, "w");			
			$contents = file_get_contents($name, 'UTF-8');
			$arr = json_decode($contents,true);
			$i = 0;
			foreach($moreSplit as $m) {				
				// Don't forget to replace space(s) in key with dash
				$key = str_replace(" ","-", $word) . "_" . $i;
				
				$arr[$key] = $moreSplit[$i];
				$i++;
			}
			ksort($arr);
			$outMore = "{" . PHP_EOL;
			$delimMore = "";
			foreach($arr as $key => $value) {
				$value = str_replace('"',"'",$value);
				$value = str_replace('\\','\\\\',$value);
				$outMore .= $delimMore . '"' . $key . '": "' . $value . '"';
				$delimMore = "," . PHP_EOL;
			}
			$outMore .= PHP_EOL . "}";
			file_put_contents($name, $outMore);
		}
		
		if (strlen($meta) > 0) {			
			/*
			$name = $path . $class . "-meta";
			$contents = file_get_contents($name, 'UTF-8');
			$arr = json_decode($contents,true);			
			$arr[$word] = $meta;			
			ksort($arr);
			$outMeta = "{" . PHP_EOL;
			$delimMeta = "";
			foreach($arr as $key => $value) {
				$value = str_replace('"',"'",$value);
				$outMeta .= $delimMeta . '"' . $key . '": "' . $value . '"';
				$delimMeta = "," . PHP_EOL;
			}
			$outMeta .= PHP_EOL . "}";
			file_put_contents($name, $outMeta);
			*/
			$name = $path . $class . "-conj";
			$contents = file_get_contents($name, 'UTF-8');
			$dict = json_decode($contents,true);
			$dict[$word] = explode(" ",$meta);
			ksort($dict);
			$res = file_put_contents($name, json_encode($dict, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
		}
	}
	echo json_encode("success");
	function shortClassName($long) {
		$short = $long;
		switch ($long) {
		case 'adjektiv':
			$short = "adj";
			break;		
		case 'substantiv_en':
			$short = "en";
			break;
		case 'substantiv_ett':
			$short = "ett";
			break;
		case 'adverb':
			$sjort = "adv";
			break;
		}
		return $short;
	}
	function cmp($a, $b)
	{
		var_dump($a);
		echo "<br>";
		var_dump($b);
    	return key($a) > key($b);
	}
?>
