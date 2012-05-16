alter table "sourceFeeds" add column "type" Varchar(100) NOT NULL Default 'source';

DROP VIEW "getSourceFeeds";
CREATE OR REPLACE VIEW "getSourceFeeds" AS
SELECT "public"."sourceFeeds"."sourceFeedId"
	, "public"."sourceFeeds"."title"
	, "public"."sourceFeeds"."externalId"
	, "public"."sourceFeeds"."useFullExport"
	, "public"."sourceFeeds"."processed"
	, "public"."sourceFeeds"."targetFeedIds"
	, "public"."sourceFeeds"."type"
	, "public"."sourceFeeds"."statusId"
 FROM "public"."sourceFeeds"
	WHERE "public"."sourceFeeds"."statusId" != 3
ORDER BY "title";