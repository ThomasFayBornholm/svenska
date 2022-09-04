$arr = @("all", "verb", "adjektiv", "adverb", "substantiv_en", "substantiv_ett");

$dest="backup"
for ($i = 0; $i -lt $arr.count; $i++) {
	Copy-Item $arr[$i] $dest;
}
