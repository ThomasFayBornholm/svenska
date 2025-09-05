#!/usr/bin/bash

set -e
sudo apt install -y apache2 php php-curl
script_dir="$( cd "$( dirname "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"
post=${script_dir#*svenska}
root_path=${script_dir//$post/}

user=$(whoami)

# Main application components
install_path=/var/www/html/svenska
sudo mkdir -p $install_path
sudo chown -R $user:$user $install_path
cp -r $root_path/lists $install_path
sudo chown -R www-data:www-data $install_path/lists
cp -r $root_path/backend $install_path
cp -r $root_path/vocab $install_path
cp -r $root_path/main.js $install_path
cp -r $root_path/svenska.css $install_path
cp -r $root_path/img $install_path
sudo chown -R www-data:www-data $install_path/sounds/*

if [ ! -f $install_path/jquery-3.6.0.js ]; then
    wget -O "$install_path/jquery-3.6.0.js" https://code.jquery.com/jquery-3.6.0.js
fi

if [ ! -d $install_path/jquery-ui-1.14.1 ]; then
    wget -O "$install_path/jquery-ui-1.14.1.zip" https://jqueryui.com/resources/download/jquery-ui-1.14.1.zip
    eval "unzip $install_path/jquery-ui-1.14.1.zip -d $install_path"
fi

# Local English to Swedish translation agent
# python3 -m venv $install_path/venv-libretranslate/
# source $install_path/venv-libretranslate/bin/activate
# pip install libretranslate
# sudo chown www-data:www-data -R $install_path/venv-libretranslate
# sudo cp $root_path/backend/libretranslate.service /etc/systemd/system/libretranslate.service
# sudo systemctl daemon-reload
# sudo systemctl restart libretranslate

sudo systemctl restart apache2
