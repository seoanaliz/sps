DROP VIEW "getStat_users_authorities";
alter table "stat_users_authorities" RENAME TO "stat_ranks";

CREATE OR REPLACE VIEW "get_stat_ranks" AS
  SELECT "stat_ranks"."vkId" AS user_id,
        "stat_ranks".source,
        "stat_ranks".rank
  FROM "stat_ranks";