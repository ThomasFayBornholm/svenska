$url="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js";
$file="jquery-3.4.1.min.js";
Invoke-WebRequest -Uri $url -OutFile $file;
Move-Item $file ../
