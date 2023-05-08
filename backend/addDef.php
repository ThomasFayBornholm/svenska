<?php
	$class = $_GET['class'];
	$word = $_GET['word'];
	// No characters that are not visible to user to be present in storage key
	$word = str_replace("­","",$word);
	$meta = $_GET['meta'];
	$def = $_GET['def'];
	$more = $_GET['more'];
	
	// Add word to listing if not already available
	$path = getcwd() . "/";
	$fileName = $path . $class . "-only";
	$infile = fopen($fileName, "r") or die("Could not open file: " . $fileName);
	if (filesize($fileName) === 0) {
		$contents = "";
	} else {
		$contents = fread($infile, filesize($fileName));
	}
	fclose($infile);
	
	$outfile = fopen($fileName, 'w') or die("Could not open file: " . $fileName);
	$word = $_GET['word'];
	
	
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
			$out = $out . $del . $w;
			$placed = true;
		} else {
			$out = $out . $del . $w;
			$del = "\n";
		}
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
		$name = $class . $trail;
		$contents = file_get_contents($path . $name, 'UTF-8');
		$arr = json_decode($contents, true);
		$arr[$word] = $def;
		file_put_contents($path . $name, json_encode($arr));
	}
	
	// Do not replace any existing score by default	
	// If not existing score then default to "2". User can easily update this from interface	
	$name = $class . "-score";
	$contents = file_get_contents($path . $name, 'UTF-8');
	$arr = json_decode($contents, true);
	if (!array_key_exists($word,$arr)) {
		$arr[$word] = "2";
		file_put_contents($path . $name, json_encode($arr));
	}
	
	if ($class != "faser") {
		// "Fraser" class does not benefit from "more" or "meta" fields so exclude
		if (strlen($more) > 0) {
			$key_suffix = "_0";
			
			$moreSplit = explode("||",$more);	
			$name = $class . "-more";
			$contents = file_get_contents($path . $name, 'UTF-8');
			$arr = json_decode($contents,true);
			$i = 0;
			foreach($moreSplit as $m) {				
				// Don't forget to replace space(s) in key with dash
				$key = str_replace(" ","-", $word) . "_" . $i;
				
				$arr[$key] = $moreSplit[$i];
				$i++;
			}
			file_put_contents($path. $name, json_encode($arr));
		}
		
		if (strlen($meta) > 0) {
			$name = $class . "-meta";
			$contents = file_get_contents($path . $name, 'UTF-8');
			$arr = json_decode($contents,true);			
			$arr[$word] = $meta;			
			file_put_contents($path . $name, json_encode($arr));
		}
	}
	echo json_encode("success");
?>