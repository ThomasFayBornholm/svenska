$arr = @("all", "verb", "adjektiv", "adverb", "substantiv_en", "substantiv_ett", "test");

$dest="backup"
for ($i = 0; $i -lt $arr.count; $i++) {
	Copy-Item $arr[$i] $dest;
	$name = $arr[$i] + "-only";
	Copy-Item $name $dest;
	$name = $arr[$i] + "-def";
	Copy-Item $name $dest;
}
