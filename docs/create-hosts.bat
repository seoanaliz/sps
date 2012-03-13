SET HOSTS_ROOT="C:\Windows\System32\drivers\etc\hosts"
SET APACHE_ROOT="D:\usr\Apache\conf\extra\httpd-vhosts.conf"

echo.>> %HOSTS_ROOT%
echo 127.0.0.1  		sps>> %HOSTS_ROOT%

echo.>> %APACHE_ROOT%
echo ^<VirtualHost *:80^>>> %APACHE_ROOT%
echo 	DocumentRoot "d:\www\SPS\">> %APACHE_ROOT%
echo 	ServerName sps>> %APACHE_ROOT%
echo ^</VirtualHost^>>> %APACHE_ROOT%