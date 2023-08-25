<?php
	$path =  $path = getcwd() . "/../sounds/";
	$class = $_GET['class'];
	$word = $_GET['word'];	
	$fullPath = $path . shortenClass($class) . "/" . $word;
	$res = "";
	if (file_exists($fullPath)) {
		$res = "exists";
	}
	echo json_encode($res);
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
?>
