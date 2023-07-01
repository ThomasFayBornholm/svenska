$url="https://isolve-so-service.appspot.com/pronounce?id="

$start=102949
$end=$start + 100000
for ($i = $start; $i -lt $end; $i++) {
	$id = $i.ToString();	
	while ($id.length -le 4) {
		$id = "0" + $id;
	}
	
	$full=$url + $id + "_1.mp3"
	echo $full
	Invoke-WebRequest $full -Out "$id.mp3"
}
