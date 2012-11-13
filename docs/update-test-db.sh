pg_dump -U sps -O -f /tmp/tst.dump.sql tst
echo "drop schema public cascade; create schema public" | psql -U sps tst-beta
psql -U sps -f /tmp/tst.dump.sql tst-beta