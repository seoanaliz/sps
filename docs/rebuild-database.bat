@ECHO OFF
SET PGSQL_ROOT="D:\usr\postgres\bin"
SET DATABASE_NAME="sps"
SET DATABASE_USER="postgres"

REM BYDLOCODE WITHOUT FOR
net stop Apache2.2
%PGSQL_ROOT%\dropdb.exe -U %DATABASE_USER% %DATABASE_NAME%
%PGSQL_ROOT%\createdb.exe -U %DATABASE_USER% -E UTF8 %DATABASE_NAME%

%PGSQL_ROOT%\psql.exe  -U %DATABASE_USER% -f SPS.sql  %DATABASE_NAME%

%PGSQL_ROOT%\psql.exe  -U %DATABASE_USER% -f Views\Base.VFS.sql  %DATABASE_NAME%
%PGSQL_ROOT%\psql.exe  -U %DATABASE_USER% -f Views\Base.Common.sql  %DATABASE_NAME%
%PGSQL_ROOT%\psql.exe  -U %DATABASE_USER% -f Views\SPS.Common.sql  %DATABASE_NAME%
%PGSQL_ROOT%\psql.exe  -U %DATABASE_USER% -f Views\SPS.Articles.sql  %DATABASE_NAME%
%PGSQL_ROOT%\psql.exe  -U %DATABASE_USER% -f init.sql  %DATABASE_NAME%
%PGSQL_ROOT%\psql.exe  -U %DATABASE_USER% -f test.sql  %DATABASE_NAME%

net START Apache2.2
pause