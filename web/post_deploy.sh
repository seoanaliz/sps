#!/bin/sh

echo "[PERM] Changing Directory Permissions"
echo -e "\t $1 to 644, dirs to 755"
chmod -f -R 664 $1
find $1 -type d -exec chmod 775 {} \;

echo -e "\t $1/cache/ to 777"
chmod -f -R 777 $1/cache/

echo -e "\t $1/shared/files/ to 777"
chmod -f -R 777 $1/shared/files/

echo -e "\t $1/shared/temp/ to 777"
chmod -f -R 777 $1/shared/temp/

echo -e "\t Production Version"
rm -rf $1cache/
rm $1/eaze.php
mv $1/eaze.production.php $1/eaze.php

rm -rf /tmp/sps.beta.cache /tmp/sps.cache
mkdir -p /tmp/sps.beta.cache /tmp/sps.cache

sudo chmod -R a+r,g+w /tmp/sps.beta.cache /tmp/sps.cache
sudo chgrp -R www /tmp/sps.beta.cache /tmp/sps.cache

ln -s /tmp/sps.beta.cache /home/sps/www/beta/cache
ln -s /tmp/sps.cache /home/sps/www/current/cache

~/bin/rnginx

crontab $1/crontab/crontab

echo -e "Done!"
date
