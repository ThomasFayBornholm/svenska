$file = Get-ChildItem tmp | Where {!$_.PSIsContainer } | Select-Object -First 1
while ($file) {
	$name = $file.Name
	echo $name
	if ($file.length -eq 0) {
		Remove-Item "./tmp/$name";
	} else {
		vlc -I dummy --dummy-quiet "./tmp/$name"
		$class = Read-Host "Word Class (default = en)"
		if ($class.length -eq 0) {
			$class="en";
		}
		$word = Read-Host "Word"
		if ($word.length -ne 0) {
			Copy-Item "./tmp/$name" "./processed/$name"
			Sleep 0.25
			Move-Item -Force "./tmp/$name" "./$class/$word"			
			Write-Output "$name, $class/$word" >> mapping
			Write-Output "Moving ./tmp/$name to $class/$word"		
		}
	}
	$file = Get-ChildItem tmp | Where {!$_.PSIsContainer } | Select-Object -First 1
}
