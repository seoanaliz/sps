alter table "articleQueues" add column "author" Varchar(100);
alter table "articleQueues" add column "externalId" Varchar(100);
alter table "articleQueues" add column "externalLikes" Integer;
alter table "articleQueues" add column "externalRetweets" Integer;

DROP VIEW "getArticleQueues";

CREATE OR REPLACE VIEW "getArticleQueues" AS
SELECT "public"."articleQueues"."articleQueueId"
	, "public"."articleQueues"."startDate"
	, "public"."articleQueues"."endDate"
	, "public"."articleQueues"."createdAt"
	, "public"."articleQueues"."sentAt"
	, "public"."articleQueues"."type"
	, "public"."articleQueues"."author"
	, "public"."articleQueues"."externalId"
	, "public"."articleQueues"."externalLikes"
	, "public"."articleQueues"."externalRetweets"
	, "public"."articleQueues"."articleId"
	, "public"."articleQueues"."targetFeedId"
	, "public"."articleQueues"."statusId"
 FROM "public"."articleQueues"
	WHERE "public"."articleQueues"."statusId" != 3
ORDER BY "createdAt" DESC, "articleQueueId" DESC;