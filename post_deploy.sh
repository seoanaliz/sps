#!/usr/local/bin/bash
echo "[PERM] Changing Directory Permissions"
echo -e "\t $1 to 644, dirs to 755"
chmod -f -R 664 $1
find $1 -type d -exec chmod 775 {} \;

echo -e "\t $1/web/cache/ to 777"
chmod -f -R 777 $1/web/cache/

echo -e "\t $1/web/shared/files/ to 777"
chmod -f -R 777 $1/web/shared/files/

echo -e "\t $1/web/shared/temp/ to 777"
chmod -f -R 777 $1/web/shared/temp/

echo -e "\t Production Version"
rm $1/web/cache/compiled.eaze
rm $1/web/eaze.php
mv $1/web/eaze.production.php $1/web/eaze.php

echo -e "Done!"
