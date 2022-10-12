$arr = @("all", "verb", "adjektiv", "adverb", "substantiv_en", "substantiv_ett");

for ($i = 0; $i -lt $arr.count; $i++) {
	$name = $arr[$i];
	$src = "backup/$name"; 
	$dest = "./$name"; 
	Copy-Item $src $dest;
	$name = $arr[$i] + "-only"; 
	$src = "backup/$name";
	$dest = "./$name";
	Copy-Item $src $dest;
	$name = $arr[$i] + "-def"; 
	$src = "backup/$name";
	$dest = "./$name";
	Copy-Item $src $dest;

}
