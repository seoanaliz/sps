CREATE TABLE inst_observed_accounts
(
  id integer NOT NULL,
  name character varying,
  link character varying,
  avatara character varying,
  status integer,
  CONSTRAINT inst_observed_accounts_pkey PRIMARY KEY (id )
);

CREATE TABLE inst_observed_posts
(
  id numeric NOT NULL,
  posted_at timestamp without time zone,
  reference_id numeric,
  likes integer,
  comments integer,
  ref_start_subs integer,
  ref_end_subs integer,
  status integer,
  updated_at timestamp without time zone,
  author_id integer,
  CONSTRAINT inst_observed_posts_pkey PRIMARY KEY (id )
);

CREATE OR REPLACE VIEW "getInst_observed_accounts" AS
SELECT   inst_observed_accounts.id,
         inst_observed_accounts.name,
         inst_observed_accounts.link,
         inst_observed_accounts.avatara,
         inst_observed_accounts.status
FROM inst_observed_accounts;

CREATE OR REPLACE VIEW "getInst_observed_posts" AS
SELECT  inst_observed_posts.id,
        inst_observed_posts.posted_at,
        inst_observed_posts.reference_id,
        inst_observed_posts.likes,
        inst_observed_posts.comments,
        inst_observed_posts.ref_start_subs,
        inst_observed_posts.ref_end_subs,
        inst_observed_posts.status,
        inst_observed_posts.updated_at,
        inst_observed_posts.author_id
FROM inst_observed_posts;