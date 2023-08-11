Get-ChildItem tmp -Filter *.mp3 | Foreach-Object {
	$name = $_.Name
	$file = Get-Item "./tmp/$name";
	if ($file.length -eq 0) {
		Write-Output "Removing ./tmp/$name";
		Remove-Item "./tmp/$name";
	}
}
