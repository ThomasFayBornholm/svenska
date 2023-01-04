$a = cat ./svsv.opf | Select-String ordlista
$b = $a.ToString().Substring(36,2)
$dest = "D:\documents\"
Write-Output "Deploying version $b to $dest"
Copy-Item ../tmp/svsv.mobi "D:\documents\"
