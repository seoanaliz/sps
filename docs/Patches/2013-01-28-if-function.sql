CREATE or REPLACE function IF(bool, anyelement, anyelement)
RETURNS anyelement language sql immutable as $$
SELECT CASE WHEN $1 THEN $2 ELSE $3 END
$$;