<?php
	$VERBOSE=false;
	$DEBUG=true;
	$path = getcwd() ."/";
	$name = $_GET["class"] . "-only";
	$fileName = $path . $name;
	$contents = file_get_contents($fileName, 'UTF-8');
	$outfile = fopen($fileName, "w") or die("Could not open file: " . $fileName);
	$arr = preg_split("/\r\n|\r|\n/", $contents);
	// Walk through the file and print the words
	$nl = "<br>";
	
	$N_EL = count($arr);
	$N_SORT=$N_EL;
	$START=0;
	$RANGE=500;
	$N_SORT = $START + $RANGE;
	$N_SORT = $N_EL;
	$moves = 0;
	$wordsMoved=0;
	for ($i = $START; $i < $N_SORT; $i++) {
		if ($VERBOSE) echo "$i<br>";
		// Move word backwards or forwards 
		// to suit required ordering
		$cur = $arr[$i];
		if ($VERBOSE) echo "cur= '$cur'<br>";
		// First try forwards
		$done = false;
		for ($j = $i + 1; $j < $N_SORT; $j++) {
			$oldPos = $i;
			$comp = $arr[$j];
			if ($VERBOSE) echo "F: comp = '$comp'<br>";
			if ($cur < $comp) {
				if ($j != $i+1) {
					$done = true;
				}
				break;
			} else {
				// Switch to match new order
				if ($VERBOSE) echo "Advance '$cur' past '$comp'<br>";
				$tmp = $arr[$j];
				$arr[$j] = $arr[$j-1];
				$arr[$j-1] = $tmp;	
				$moves++;
			}
		}
		// Then backwards
		if (!$done) {
			for ($j=$i -1; $j > 0; $j--) {
				$comp = $arr[$j];
				if ($VERBOSE) echo "B: comp = $comp<br>";
				if ($cur > $comp) {
					break;
				} else {
					if ($VERBOSE) echo "Retreat $cur past $comp<br>";
					$tmp = $arr[$j];
					$arr[$j] = $arr[$j-1];
					$arr[$j-1] = $tmp;	
					$moves++;
				}
			}
		}
		if ($DEBUG) {
			if ($j != $oldPos -1 && $j != $oldPos) {
				echo "Change over range $oldPos to $j<br>";
				$wordsMoved++;
			}
		}
	}
	$out = $arr[0]; 
	for ($i = 1; $i < $N_EL; $i++) {
		$out = $out . "\n" . $arr[$i];
	} 
	$res = fwrite($outfile, $out);
	fclose($outfile);
	echo "Move operations : $moves<br>";
	echo "Words moved: $wordsMoved";
?>
