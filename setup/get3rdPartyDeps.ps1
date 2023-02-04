$url="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js";
$file="jquery-3.4.1.min.js";
Invoke-WebRequest -Uri $url -OutFile $file;
Move-Item $file ../

$url="https://code.jquery.com/jquery-3.6.0.js";
$file="jquery-3.6.0.js";
Invoke-WebRequest -Uri $url -OutFile $file;
Move-Item $file ../

$url="https://code.jquery.com/ui/1.13.2/jquery-ui.js"
$file="jquery-ui.js";
Invoke-WebRequest -Uri $url -OutFile $file;
Move-Item $file ../

$url="http://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css"
$file="jquery-ui.css";
Invoke-WebRequest -Uri $url -OutFile $file;
Move-Item $file ../
