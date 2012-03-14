CREATE OR REPLACE VIEW "getSourceFeeds" AS
SELECT "public"."sourceFeeds"."sourceFeedId"
	, "public"."sourceFeeds"."title"
	, "public"."sourceFeeds"."statusId"
 FROM "public"."sourceFeeds"
	WHERE "public"."sourceFeeds"."statusId" != 3
ORDER BY "title";
	
CREATE OR REPLACE VIEW "getTargetFeeds" AS
SELECT "public"."targetFeeds"."targetFeedId"
	, "public"."targetFeeds"."title"
	, "public"."targetFeeds"."statusId"
 FROM "public"."targetFeeds"
	WHERE "public"."targetFeeds"."statusId" != 3
ORDER BY "title";
	
CREATE OR REPLACE VIEW "getArticles" AS
SELECT "public"."articles"."articleId"
	, "public"."articles"."importedAt"
	, "public"."articles"."sourceFeedId"
	, "public"."articles"."statusId"
 FROM "public"."articles"
	WHERE "public"."articles"."statusId" != 3
ORDER BY "importedAt" DESC;

CREATE OR REPLACE VIEW "getArticleQueues" AS
SELECT "public"."articleQueues"."articleQueueId"
	, "public"."articleQueues"."startDate"
	, "public"."articleQueues"."endDate"
	, "public"."articleQueues"."createdAt"
	, "public"."articleQueues"."sentAt"
	, "public"."articleQueues"."articleId"
	, "public"."articleQueues"."targetFeedId"
	, "public"."articleQueues"."statusId"
 FROM "public"."articleQueues"
	WHERE "public"."articleQueues"."statusId" != 3
ORDER BY "createdAt" DESC, "articleQueueId" DESC;

CREATE OR REPLACE VIEW "getArticleRecords" AS
SELECT "public"."articleRecords"."articleRecordId"
	, "public"."articleRecords"."content"
	, "public"."articleRecords"."likes"
	, "public"."articleRecords"."articleId"
	, "public"."articleRecords"."articleQueueId"
 FROM "public"."articleRecords";