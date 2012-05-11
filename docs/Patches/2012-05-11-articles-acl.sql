alter table "sourceFeeds" add column "targetFeedIds" Text;
alter table "targetFeeds" add column "vkIds" Text;

DROP VIEW "getSourceFeeds";
DROP VIEW "getTargetFeeds";

CREATE OR REPLACE VIEW "getSourceFeeds" AS
SELECT "public"."sourceFeeds"."sourceFeedId"
	, "public"."sourceFeeds"."title"
	, "public"."sourceFeeds"."externalId"
	, "public"."sourceFeeds"."useFullExport"
	, "public"."sourceFeeds"."processed"
	, "public"."sourceFeeds"."targetFeedIds"
	, "public"."sourceFeeds"."statusId"
 FROM "public"."sourceFeeds"
	WHERE "public"."sourceFeeds"."statusId" != 3
ORDER BY "title";

CREATE OR REPLACE VIEW "getTargetFeeds" AS
SELECT "public"."targetFeeds"."targetFeedId"
	, "public"."targetFeeds"."title"
	, "public"."targetFeeds"."externalId"
	, "public"."targetFeeds"."startTime"
	, "public"."targetFeeds"."period"
	, "public"."targetFeeds"."vkIds"
	, "public"."targetFeeds"."publisherId"
	, "public"."targetFeeds"."statusId"
	, "publisher"."publisherId" AS "publisher.publisherId"
	, "publisher"."name" AS "publisher.name"
	, "publisher"."vk_id" AS "publisher.vk_id"
	, "publisher"."vk_app" AS "publisher.vk_app"
	, "publisher"."vk_token" AS "publisher.vk_token"
	, "publisher"."vk_seckey" AS "publisher.vk_seckey"
	, "publisher"."statusId" AS "publisher.statusId"
 FROM "public"."targetFeeds"
	INNER JOIN "public"."publishers" "publisher" ON
		"publisher"."publisherId" = "public"."targetFeeds"."publisherId"
	WHERE "public"."targetFeeds"."statusId" != 3;