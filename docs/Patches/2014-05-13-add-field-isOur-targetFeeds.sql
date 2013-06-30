alter table "targetFeeds" add column "isOur" boolean;

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
	, "public"."targetFeeds"."isOur"
 FROM "public"."targetFeeds"
	LEFT JOIN "public"."publishers" "publisher" ON
		"publisher"."publisherId" = "public"."targetFeeds"."publisherId"
	WHERE "public"."targetFeeds"."statusId" != 3
ORDER BY "title";