# Update id
$a = cat ./svsv.opf | Select-String ordlista

$b = $a.ToString().Substring(36,2)
$c = ([int]$b + 1).ToString()
if ($c.length -lt 2) {
  $c = "0" + $c
} elseif ($c.length -eq 3) {
  $c = "01"
}
$d = cat ./svsv.opf
$d = $d -replace "-$b", "-$c"
Write-Output "Updating id from $b to $c ..."
Write-Output $d > ./svsv.opf
./kindlegen.exe ./svsv.opf > prepLog
cat prepLog | Select-String Warning | Select-String -notmatch cover | select-string -notmatch "enhanced MOBI" | Select-String -notmatch WARNINGS
Copy-Item ./svsv.mobi ../tmp/
