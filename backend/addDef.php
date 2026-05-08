<?php
	include "error_catch.php";
	$out["status"] = "init";
	$class = $_GET['class'];
	$class = preg_replace("/_.*/","",$class);
	$word = $_GET['word'];	
	// No characters that are not visible to user to be present in storage key
	$word = str_replace("­","",$word);
	// Internal enumeration takes form '-#'
	$word = str_replace("(","-",$word);
	$word = str_replace(")","",$word);
	$options = $_GET["options"];
	$def = $_GET['def'];
	#$more = $_GET['more']; TODO implement scraping of more info
	
	// Add word to listing if not already available
	$path = getcwd() . "/../lists/";
	if (! str_contains($word, "-")) {

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
		$tmp_out = "";	
		$placed = false;
		$del = "";
		// First in list case	
		
		$del = "";
		foreach($elements as $w) {				
			// Insertion point
			if ($w === $word) {			
				$tmp_out .= $del . $word;			
				$placed = true;
			} else if ($w > $word && !$placed) {
				$tmp_out .= $del . $word;				
				$tmp_out .= $del . $w;
				$placed = true;
			} else {				
				$tmp_out .= $del . $w;				
			}
			$del = "\n";
		}
		
		// For case where word is last in alphabetical sorting
		if ($placed === false) {
			$tmp_out .= $del . $word;
		}
		
		fwrite($outfile,$tmp_out);
		fclose($outfile);
		
		// Add word to all listing if needed
		$allName = $path . "all-only";
		$allFile = fopen($allName, "r") or die ("Could not open file: " . $allName);
		$contents = fread($allFile, filesize($allName));
		fclose($allFile);
		$elements = preg_split("/\r\n|\r|\n/", $contents);
		$tmp_out = "";
		$placed = false;
		$del = "";
		
		foreach($elements as $w) {
			// Insertion point
			if ($w === $word) {
				$tmp_out .= $del . $word;
				$placed = true;
			} else if ($w > $word && !$placed) {
				$tmp_out .= $del . $word;
				$del = "\n";
				$tmp_out .= $del. $w;
				$placed = true;
			} else {
				$tmp_out .= $del . $w;
				$del = "\n";
			}
		}
		// If word is alphabetically last in listing
		if ($placed === false) {
			$tmp_out .= $del . $word;
		}
		
		$outfileAll = fopen($allName, "w") or die("Could not open file: " . $allName);
		fwrite($outfileAll, $tmp_out);
		fclose($outfileAll);
	}
				
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
		$json = json_encode($arr,JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
		if (count($arr) > 50) {
			$out["def-bytes"] = file_put_contents($name, $json);
		} else {
			$out["def-bytes"] = 0;
		}
	}
	
	if (strlen($options) > 0) {			
		$name = $path . $class . "-conj";
		$contents = file_get_contents($name, 'UTF-8');
		$dict = json_decode($contents,true);
		$tmp = explode(",",$options);
		$dict[$word] = $tmp;
		ksort($dict);
		$out["conj-bytes"] = file_put_contents($name, json_encode($dict, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
	}
	$out["status"] = "done";
	echo json_encode($out);
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
