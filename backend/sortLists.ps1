$arr = @("all", "verb", "adjektiv", "adverb", "substantiv_en", "substantiv_ett");
$tmp = "tmp";
for ($i = 0; $i -lt $arr.count; $i++) {
	Get-Content $arr[$i] | Sort-Object | Get-Unique > $tmp;
	Copy-Item $tmp $arr[$i];
}
