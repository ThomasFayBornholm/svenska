$arr = @("all", "verb", "adjektiv", "adverb", "substantiv_en", "substantiv_ett");

for ($i = 0; $i -lt $arr.count; $i++) {
	$src = "backup/" + $arr[$i];
	$dest = "./" + $arr[$i];
	echo "Copy-Item $src $dest";
	Copy-Item $src $dest;
}
