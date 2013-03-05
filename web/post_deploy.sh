#!/usr/local/bin/bash
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
rm $1cache/compiled.eaze
rm $1cache/*
rm $1/eaze.php
mv $1/eaze.production.php $1/eaze.php

echo -e "Done!"
date
