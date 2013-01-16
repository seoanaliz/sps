alter table "articleQueues" add column "collectLikes" Boolean NOT NULL Default TRUE;

DROP VIEW "getArticleQueues";

CREATE OR REPLACE VIEW "getArticleQueues" AS
SELECT  "public"."articleQueues"."articleQueueId"
      , "public"."articleQueues"."startDate"
      , "public"."articleQueues"."endDate"
      , "public"."articleQueues"."createdAt"
      , "public"."articleQueues"."sentAt"
      , "public"."articleQueues".type
      , "public"."articleQueues".author
      , "public"."articleQueues"."externalId"
      , "public"."articleQueues"."externalLikes"
      , "public"."articleQueues"."externalRetweets"
      , "public"."articleQueues"."articleId"
      , "public"."articleQueues"."targetFeedId"
      , "public"."articleQueues"."statusId"
      , "public"."articleQueues"."collectLikes"
FROM "public"."articleQueues"
WHERE "public"."articleQueues"."statusId" != 3
ORDER BY "articleQueues"."createdAt" DESC, "articleQueues"."articleQueueId" DESC;
