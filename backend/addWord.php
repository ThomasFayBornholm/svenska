<?php
	$res = -1;
	$path = getcwd() ."/../lists/";
	$class = $_GET['class'];

	$fileName = $path. $class . "-only";
	$infile = fopen($fileName, "r") or die("Could not open file: " . $fileName);
	if (filesize($fileName) == 0) {
		$contents = "";
	} else {
		$contents = fread($infile, filesize($fileName));
	}
	fclose($infile);

	$outfile = fopen($fileName, "w") or die("Could not open file: " . $fileName);
	$word = $_GET['word'];
	// No characters that are not visible to user in key
	$word = str_replace("­","",$word);
	$elements= preg_split("/\r\n|\r|\n/", $contents);

	// Previous action has checked that the word does not already exist in the listing 

	// Add word add correct position in file
	$out = "";
	$placed = false;
	$del = "";
	$i = 0;
	foreach($elements as $w) {
		// Insertion point
		if ($w == $word) {
			// No need to duplicate entry
			$out = $out . $del . $word;
			$placed = true;
			$res = $i;
		} else if ($w > $word && !$placed) {
			$out = $out . $del . $word;
			$res = $i;
			$del = "\n";
			$out = $out . $del . $w; 			
			$placed=true;
		} else {
			$out = $out . $del . $w; 
			$del = "\n";
		}
		$i++;
	}
	if ($placed === false) {
		$out = $out . $del . $word;
	}
	
	fwrite($outfile, $out);
	fclose($outfile);
	
	$allName = $path . "all-only";
	$allFile = fopen($allName, "r") or die("Could not open file: " . $allName);
	$contents = fread($allFile, filesize($allName));
	fclose($allFile);
	$elements= preg_split("/\r\n|\r|\n/", $contents);
	$out = "";
	$placed = false;
	$del="";
	foreach($elements as $w) {		
		// Insertion point
		if ($w === $word) {
			// No need to duplicate entry
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
	if ($placed === false) {
		$out = $out . $del . $word;
	}
	$outfileAll = fopen($allName, "w") or die("Could not open file: " . $allName);		
	fwrite($outfileAll, $out);
	fclose($outfileAll);		
	
	echo json_encode($res);
?>