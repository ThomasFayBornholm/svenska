<?php
	$res = -1;
	$path = getcwd() ."/../lists/";
	$class = $_GET['class'];
	$word = $_GET['word'];
	$trail = "-only";
	// Write a tmp file with the chosen word remove
	// Then overwrite the original file with the temp file
	// Return -1 if word to be removed is not found.

	$fileName = $path . $class . $trail;
	$defName = $path . $class . "-def";
	$metaName = $path . $class . "-meta";
	$scoreName = $path . $class . "-score";
	$moreName = $path . $class . "-more";
	$tmpName = $path . "tmp";

	$infile = fopen($fileName, "r") or die("Could not open file: " . $fileName);
	$tmpfile = fopen($tmpName, "w") or die("Could not open file: " . $tmpName);

	if (filesize($fileName) == 0) {
		$contents = "";
	} else {
		$contents = fread($infile, filesize($fileName));
	}

	$elements= preg_split("/\r\n|\r|\n/", $contents);
	$tmp = "";
	$wArr = array();
	foreach ($elements as $el) {
		$w = $el;
		if ($w === $word) {
			$res = 0;
		} else {
			array_push($wArr, $el);
		}
	}

	for ($i = 0; $i < count($wArr) - 1; $i++) {
		fwrite($tmpfile, $wArr[$i] . "\n");
	}
	fwrite($tmpfile, $wArr[$i]);
	
	fclose($infile);
	fclose($tmpfile);

	if ($res === 0) {
		copy ($tmpName, $fileName);
	}
	// Remove definition
	$contents = file_get_contents($defName, 'UTF-8');
	$arr = json_decode($contents,true);
	unset($arr[$word]);
	file_put_contents($defName, json_encode($arr));
	// Remove meta
	$contents = file_get_contents($metaName, 'UTF-8');
	$arr = json_decode($contents,true);
	unset($arr[$word]);
	file_put_contents($metaName, json_encode($arr));
	// Remove score
	$contents = file_get_contents($scoreName, 'UTF-8');
	$arr = json_decode($contents,true);
	unset($arr[$word]);
	file_put_contents($scoreName, json_encode($arr));

	// Remove more
	$contents = file_get_contents($moreName, 'UTF-8');
	$arr = json_decode($contents,true);
	unset($arr[$word]);
	file_put_contents($moreName, json_encode($arr));
	echo json_encode($res);
?>
