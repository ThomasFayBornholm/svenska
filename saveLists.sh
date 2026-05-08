install_path=/var/www/html/svenska
cp $install_path/lists/* ./lists
git add lists/*
git commit -m "Update word listings"
git push
