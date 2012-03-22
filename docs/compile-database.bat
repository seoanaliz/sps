@ECHO OFF
SET FILE="d:\www\SPS\docs\init-dump.sql"
If Not Exist "%FILE%" Echo.>"%FILE%"
copy /b SPS.sql+Views\*.*+init.sql+test.sql %FILE%
pause