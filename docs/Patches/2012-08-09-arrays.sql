create or replace function array_remove_sql(int8[], int8[]) returns int8[] as $$
SELECT array(select $1[i] from generate_series(array_lower($1,1), array_upper($1,1)) i WHERE $1[i] <> ALL($2));
$$ language sql immutable;