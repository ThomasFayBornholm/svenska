install_path=/var/www/html/svenska
cp $install_path/lists/* ./lists
git add lists/*-only
git add lists/*-def
git add lists/*-meta
git add lists/*-score
git add lists/*-more
git commit -m "Updates word listings."
