ALTER TABLE "articleQueues" ADD COLUMN "deleteAt" timestamp without time zone;
COMMENT ON COLUMN "articleQueues"."deleteAt" IS 'Время, когда нужно удалить пост';

ALTER TABLE "articleQueues" ADD COLUMN "isDeleted" boolean;
ALTER TABLE "articleQueues" ALTER COLUMN "isDeleted" SET NOT NULL;
ALTER TABLE "articleQueues" ALTER COLUMN "isDeleted" SET DEFAULT false;
COMMENT ON COLUMN "articleQueues"."isDeleted" IS 'Удален ли пост';

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
	, "public"."articleQueues"."deleteAt"
	, "public"."articleQueues"."isDeleted"
	, "public"."articleQueues"."statusId"
 FROM "public"."articleQueues"
	WHERE "public"."articleQueues"."statusId" != 3
ORDER BY "createdAt" DESC, "articleQueueId" DESC;