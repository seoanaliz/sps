CREATE TABLE "BadooUsers"
(
  external_id integer,
  country character varying,
  city character varying,
  age integer,
  registered_at integer,
  updated_at integer,
  is_vip boolean,
  name character varying,
  shortname character varying,
  CONSTRAINT badoo_users_external_id_key UNIQUE (external_id )
);

CREATE INDEX badoo_users_external_id_idx
  ON "BadooUsers"
  USING btree
  (external_id );

CREATE TABLE "BadooUsersVips"
(
  user_id integer,
  "timestamp" integer,
  get_vip boolean
);

CREATE TABLE "BadooUsersVisits"
(
  user_id integer,
  "timestamp" integer
);

CREATE OR REPLACE VIEW "GetBadooUsers" AS
 SELECT "BadooUsers".external_id
        , "BadooUsers".country
        , "BadooUsers".city
        , "BadooUsers".age
        , "BadooUsers".registered_at
        , "BadooUsers".updated_at
        , "BadooUsers".is_vip
        , "BadooUsers".name
        , "BadooUsers".shortname
 FROM "BadooUsers";

CREATE OR REPLACE VIEW "GetBadooUsersVips" AS
 SELECT "BadooUsersVips".user_id
      , "BadooUsersVips"."timestamp"
      , "BadooUsersVips".get_vip
 FROM "BadooUsersVips";

CREATE OR REPLACE VIEW "GetBadooUsersVisits" AS
 SELECT "BadooUsersVisits".user_id
      , "BadooUsersVisits"."timestamp"
 FROM "BadooUsersVisits";