pg_dump -U sps -O -f /tmp/tst.dump.sql tst
echo "drop schema public cascade; create schema public" | psql -U sps tst-beta
psql -U sps -f /tmp/tst.dump.sql tst-beta

pg_dump -U sps -O -f /tmp/sps.dump.sql sps
echo "drop schema public cascade; create schema public" | psql -U sps sps-beta
psql -U sps -f /tmp/sps.dump.sql sps-beta