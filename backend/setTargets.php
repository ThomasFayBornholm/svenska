<?php
	date_default_timezone_set('Europe/Stockholm');
	$todayDate = date("l");	
	$current_score = $_GET["cur_score"];
	$path = getcwd() ."/";	
	
	$f = "targets";
	
	$rate = 0;
	$f = "rate";
	$infile = fopen($path . "/" . $f , "r") or die("Could not open file: " . $f);
	if (filesize($f) != 0) {
		$rate = (float)fread($infile, filesize($f));				
	}

	$f = "current-rating";
	$mDate = date("l",filemtime($f));
	$rating = 0;
	// Only update 'rating' once per day	
	if ($mDate != $todayDate) {
		file_put_contents($path . $f, $current_score);		
		$rating = $current_score;
	}
	$day = 0;
	switch ($todayDate) {
		case "Monday":
			break;
		case "Tuesday":
			$day = 1;
			break;
		case "Wednesday":
			$day = 2;
			break;
		case "Thursday":
			$day = 3;
			break;			
		case "Friday":
			$day = 4;
			break;
		case "Saturday":
			$day = 5;
			break;		
		case "Sunday":
			$day = 6;
			break;
	}
	if ($rating === 0) {
		$infile = fopen($path . "/" . $f , "r") or die("Could not open file: " . $f);
		if (filesize($f) != 0 ) {
			$rating = (float)fread($infile, filesize($f));				
		}
	}
	$out["rate"] = $rate;
	$out["targetDaily"] = 0;
	$out["targetWeekly"] = 0;
	$out["endDate"] = "";
	$out["targetDaily"] = $rating + $rate;
	$out["targetWeekly"] = $rating + $rate * (7 - $day);
	$out["endDate"] = (int)((100 - $rating)/$rate);	
	$strDays = $out["endDate"];
	$Date=date('Y-m-d');
	$out["endDateStr"] = date('Y-m-d',strtotime($Date. ' + ' . $out["endDate"] . ' days'));
	
	echo json_encode($out);
?>