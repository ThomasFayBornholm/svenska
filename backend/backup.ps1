$arr = @("all", "verb", "adjektiv", "adverb", "substantiv_en", "substantiv_ett", "test");

$dest="backup"
for ($i = 0; $i -lt $arr.count; $i++) {
	$name = $arr[$i] + "-only";
	Copy-Item $name $dest;
	$name = $arr[$i] + "-def";
	Copy-Item $name $dest;
	$name = $arr[$i] + "-meta";
	Copy-Item $name $dest;
}
