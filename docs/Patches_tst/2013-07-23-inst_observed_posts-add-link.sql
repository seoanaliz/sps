alter table "inst_observed_posts" add column link character varying;
CREATE INDEX inst_observed_posts_link_idx
  ON inst_observed_posts
  USING btree
  (link COLLATE pg_catalog."default" );

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
        inst_observed_posts.author_id,
        inst_observed_posts.link
FROM inst_observed_posts;