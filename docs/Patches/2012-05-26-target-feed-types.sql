alter table "targetFeeds" ALTER COLUMN "publisherId" DROP NOT NULL;
alter table "targetFeeds" ADD COLUMN "type" Varchar(10) NOT NULL Default 'vk';
alter table "targetFeeds" ADD COLUMN "params" Text;

DROP VIEW "getTargetFeeds";

CREATE OR REPLACE VIEW "getTargetFeeds" AS
SELECT "public"."targetFeeds"."targetFeedId"
	, "public"."targetFeeds"."title"
	, "public"."targetFeeds"."externalId"
	, "public"."targetFeeds"."startTime"
	, "public"."targetFeeds"."period"
	, "public"."targetFeeds"."vkIds"
	, "public"."targetFeeds"."type"
	, "public"."targetFeeds"."params"
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
	LEFT JOIN "public"."publishers" "publisher" ON
		"publisher"."publisherId" = "public"."targetFeeds"."publisherId"
	WHERE "public"."targetFeeds"."statusId" != 3;
	
INSERT INTO "targetFeedPublishers"
SELECT "targetFeedId", "publisherId"
FROM "targetFeeds"
WHERE "publisherId" IS NOT NULL;