<?php
	$path = getcwd() ."/../lists/";
	$class = $_GET['class'];
	$out["cnt"] = 0;
	$out["status"] = "";
	$out["cntScore"] = 0;
	$name = $class . "-def";
	
	if (file_exists($name)) {
		$contents = file_get_contents($path . $name, 'UTF-8');
		$arr=json_decode($contents,JSON_UNESCAPED_UNICODE);
		
		if ($arr) {	
			$out["cnt"] = count($arr);
			$name = $class . "-score";
			$contents = file_get_contents($path . $name, 'UTF-8');
			$out["cnt"] = count($arr);
			$arr=json_decode($contents,JSON_UNESCAPED_UNICODE);
			if ($arr) {
				$out["cntScore"] = count($arr);			
				$out["status"] = "good";
			}
		} else {
			$out["status"] = "Failed to read definition file.";
		}
	}
	echo json_encode($out);
?>
