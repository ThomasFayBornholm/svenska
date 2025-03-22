#!/usr/bin/bash

set -e
script_dir="$( cd "$( dirname "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"
post=${script_dir#*svenska}
root_path=${script_dir//$post/}

user=$(whoami)

install_path=/var/www/html/svenska
sudo mkdir -p $install_path
sudo chown -R $user:$user $install_path
cp -r $root_path/lists $install_path
sudo chown www-data:www-data $install_path/lists
cp -r $root_path/backend $install_path
cp -r $root_path/vocab $install_path
cp -r $root_path/svenska.css $install_path
cp -r $root_path/img $install_path
wget -O "$install_path/jquery-3.6.0.js" https://code.jquery.com/jquery-3.6.0.js
