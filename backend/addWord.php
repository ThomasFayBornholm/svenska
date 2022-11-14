<?php
	$res = 0;
	$path = getcwd() ."/";
	$class = $_GET['class'];

	$fileName = $path . $class . "-only";
	$infile = fopen($fileName, "r") or die("Could not open file: " . $fileName);
	if (filesize($fileName) == 0) {
		$contents = "";
	} else {
		$contents = fread($infile, filesize($fileName));
	}
	fclose($infile);

	$outfile = fopen($fileName, "w") or die("Could not open file: " . $fileName);
	$word = $_GET['word'];
	$elements= preg_split("/\r\n|\r|\n/", $contents);

	// Previous action has checked that the word does not already exist in the listing 

	// Add word add correct position in file
	$out = "";
	$placed = false;
	$del = "";
	foreach($elements as $w) {
		// Insertion point
		if ($w == $word) {
			// No need to duplicate entry
			$out = $out . $del . $word;
			$placed = true;
		} else if ($w > $word && !$placed) {
			$out = $out . $del . $word;
			$del = "\n";
			$out = $out . $del . $w; 
			$placed=true;
		} else {
			$out = $out . $del . $w; 
			$del = "\n";
		}
	}
	if ($placed === false) {
		$out = $out . $del . $word;
	}
	
	$res = fwrite($outfile, $out);
	fclose($outfile);

	$allName = $path . "all-only";
	$allFile = fopen($allName, "r") or die("Could not open file: " . $allName);
	$contents = fread($allFile, filesize($allName));
	fclose($allFile);
	$elements= preg_split("/\r\n|\r|\n/", $contents);
	var_dump(count($elements));
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
	$outfileAll = fopen($allName, "w") or die("Could not open file: " . $allName);
	$res = fwrite($outfileAll, $out);

	fclose($outfileAll);
	echo json_encode($res);
?>
