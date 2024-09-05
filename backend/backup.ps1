
$dest="backup"
$name = "*-only";
Copy-Item $name $dest;
$name = "*-def";
Copy-Item $name $dest;
$name = "*-meta";
Copy-Item $name $dest;
$name = "*-more";
Copy-Item $name $dest;

Copy-Item -recurse -force C:\xampp\htdocs\svenska $HOME
