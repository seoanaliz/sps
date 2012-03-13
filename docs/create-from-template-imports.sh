if [ -z $1 ] 
then
	echo "Usage $0 mysql|pgsql";
	exit 1;
else 
	if [ $1 == "mysql" -o $1 == "pgsql" ]
	then
		echo "Using $1"; 
	else 
		echo "$1 not supported. pgsql|mysql";	
		exit 1;
	fi;
fi

# Making dirs
mkdir Model
mkdir ../web
mkdir ../web/lib
mkdir ../web/cache
mkdir ../web/etc
mkdir ../web/etc/errors
mkdir ../web/etc/templates
mkdir ../web/etc/templates/vt
mkdir ../web/etc/templates/vt/elements
mkdir ../web/etc/templates/mail
mkdir ../web/shared
mkdir ../web/shared/images
mkdir ../web/shared/js
mkdir ../web/shared/css
mkdir ../web/shared/temp
mkdir ../web/shared/files

# Adding to svn
svn add ../web/
svn add ../docs/

# Lib export
svn export 'https://svn.1adw.com/1adw/Eaze/trunk/web/lib/Base.Tree/' ../web/lib/Base.Tree
svn export 'https://svn.1adw.com/1adw/Eaze/trunk/web/lib/Base.VFS/' ../web/lib/Base.VFS
svn export 'https://svn.1adw.com/1adw/Eaze/trunk/web/lib/Eaze.Core/' ../web/lib/Eaze.Core
svn export 'https://svn.1adw.com/1adw/Eaze/trunk/web/lib/Eaze.Database/' ../web/lib/Eaze.Database
svn export 'https://svn.1adw.com/1adw/Eaze/trunk/web/lib/Eaze.Helpers/' ../web/lib/Eaze.Helpers
svn export 'https://svn.1adw.com/1adw/Eaze/trunk/web/lib/Eaze.Model/' ../web/lib/Eaze.Model
svn export 'https://svn.1adw.com/1adw/Eaze/trunk/web/lib/Eaze.Modules/' ../web/lib/Eaze.Modules
svn export 'https://svn.1adw.com/1adw/Eaze/trunk/web/lib/Eaze.Site/' ../web/lib/Eaze.Site
svn export 'https://svn.1adw.com/1adw/Eaze/trunk/web/lib/Project.Common/' ../web/lib/Project.Common
svn export 'https://svn.1adw.com/1adw/Eaze/trunk/web/lib/Project.Site/' ../web/lib/Project.Site

# JS export
svn export 'https://svn.1adw.com/1adw/Eaze/trunk/web/shared/js/vt/' ../web/shared/js/vt
svn export 'https://svn.1adw.com/1adw/Eaze/trunk/web/shared/js/fe/' ../web/shared/js/fe
svn export 'https://svn.1adw.com/1adw/Eaze/trunk/web/shared/js/vfs/' ../web/shared/js/vfs
svn export 'https://svn.1adw.com/1adw/Eaze/trunk/web/shared/js/ext/' ../web/shared/js/ext

# CSS export
svn export 'https://svn.1adw.com/1adw/Eaze/trunk/web/shared/css/vt/' ../web/shared/css/vt
svn export 'https://svn.1adw.com/1adw/Eaze/trunk/web/shared/css/fe/' ../web/shared/css/fe
svn export 'https://svn.1adw.com/1adw/Eaze/trunk/web/shared/css/vfs/' ../web/shared/css/vfs

# Images export
svn export 'https://svn.1adw.com/1adw/Eaze/trunk/web/shared/images/vt/' ../web/shared/images/vt
svn export 'https://svn.1adw.com/1adw/Eaze/trunk/web/shared/images/fe/' ../web/shared/images/fe
svn export 'https://svn.1adw.com/1adw/Eaze/trunk/web/shared/images/fe/' ../web/shared/images/vfs

# Templates export
svn export 'https://svn.1adw.com/1adw/Eaze/trunk/web/etc/templates/fe/' ../web/etc/templates/fe
svn export 'https://svn.1adw.com/1adw/Eaze/trunk/web/etc/templates/vt/daemons/' ../web/etc/templates/vt/daemons
svn export 'https://svn.1adw.com/1adw/Eaze/trunk/web/etc/templates/vt/meta-details/' ../web/etc/templates/vt/meta-details
svn export 'https://svn.1adw.com/1adw/Eaze/trunk/web/etc/templates/vt/site-params/' ../web/etc/templates/vt/site-params
svn export 'https://svn.1adw.com/1adw/Eaze/trunk/web/etc/templates/vt/users/' ../web/etc/templates/vt/users
svn export 'https://svn.1adw.com/1adw/Eaze/trunk/web/etc/templates/vt/navigations/' ../web/etc/templates/vt/navigations
svn export 'https://svn.1adw.com/1adw/Eaze/trunk/web/etc/templates/vt/static-pages/' ../web/etc/templates/vt/static-pages
svn export 'https://svn.1adw.com/1adw/Eaze/trunk/web/etc/templates/vt/footer.tmpl.php' ../web/etc/templates/vt/footer.tmpl.php
svn export 'https://svn.1adw.com/1adw/Eaze/trunk/web/etc/templates/vt/header.tmpl.php' ../web/etc/templates/vt/header.tmpl.php
svn export 'https://svn.1adw.com/1adw/Eaze/trunk/web/etc/templates/vt/index.tmpl.php' ../web/etc/templates/vt/index.tmpl.php
svn export 'https://svn.1adw.com/1adw/Eaze/trunk/web/etc/templates/vt/login.tmpl.php' ../web/etc/templates/vt/login.tmpl.php
svn export 'https://svn.1adw.com/1adw/Eaze/trunk/web/etc/templates/vt/elements/datagrid/' ../web/etc/templates/vt/elements/datagrid
svn export 'https://svn.1adw.com/1adw/Eaze/trunk/web/etc/templates/vt/elements/vfs/' ../web/etc/templates/vt/elements/vfs
svn export 'https://svn.1adw.com/1adw/Eaze/trunk/web/etc/templates/vt/elements/menu/' ../web/etc/templates/vt/elements/menu

# System files export
svn export 'https://svn.1adw.com/1adw/Eaze/trunk/web/etc/conf/' ../web/etc/conf
svn export 'https://svn.1adw.com/1adw/Eaze/trunk/web/etc/locale/' ../web/etc/locale
svn export 'https://svn.1adw.com/1adw/Eaze/trunk/web/cache/.htaccess' ../web/cache/.htaccess 
svn export 'https://svn.1adw.com/1adw/Eaze/trunk/web/shared/.htaccess' ../web/shared/.htaccess 
svn export 'https://svn.1adw.com/1adw/Eaze/trunk/web/shared/.revision' ../web/shared/.revision
svn export 'https://svn.1adw.com/1adw/Eaze/trunk/web/shared/temp/.htaccess' ../web/shared/temp/.htaccess 
svn export 'https://svn.1adw.com/1adw/Eaze/trunk/web/shared/files/.htaccess' ../web/shared/files/.htaccess 
svn export 'https://svn.1adw.com/1adw/Eaze/trunk/web/shared/minify.php' ../web/shared/minify.php
svn export 'https://svn.1adw.com/1adw/Eaze/trunk/web/eaze.php' ../web/eaze.php
svn export 'https://svn.1adw.com/1adw/Eaze/trunk/web/eaze.production.php' ../web/eaze.production.php
svn export 'https://svn.1adw.com/1adw/Eaze/trunk/web/.htaccess' ../web/.htaccess

# Model files export
svn export 'https://svn.1adw.com/1adw/Eaze/trunk/docs/create-hosts.bat' create-hosts.bat
svn export 'https://svn.1adw.com/1adw/Eaze/trunk/docs/settings.xml' settings.xml
svn export 'https://svn.1adw.com/1adw/Eaze/trunk/docs/Model/%project%.mfd' Model/Project.mfd
svn export 'https://svn.1adw.com/1adw/Eaze/trunk/docs/Model/%project%.Common.xml' Model/Project.Common.xml
svn export 'https://svn.1adw.com/1adw/Eaze/trunk/docs/Model/%project%.Common.EazeEntity.xml' Model/Project.Common.EazeEntity.xml
svn export "https://svn.1adw.com/1adw/Eaze/trunk/docs/$1/Views" Views
svn export "https://svn.1adw.com/1adw/Eaze/trunk/docs/$1/rebuild-database.bat" rebuild-database.bat
svn export "https://svn.1adw.com/1adw/Eaze/trunk/docs/$1/Project.dm2" Project.dm2
svn export "https://svn.1adw.com/1adw/Eaze/trunk/docs/$1/init.sql" init.sql
svn export "https://svn.1adw.com/1adw/Eaze/trunk/docs/$1/Project.sql" Project.sql

# Ignore
svn propset svn:ignore '*.~m2' ../docs/

svn propset svn:ignore 'pages_*
sites_*
minify_*
tiny_mce_*.gz
*.inc
*.lng' ../web/cache/

svn propset svn:ignore '.cache
.settings
.project' ../web/

# Access
cacls ../web/cache /P Everyone:F
cacls ../web/shared/temp /P Everyone:F
cacls ../web/shared/files /P Everyone:F