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
	
CREATE OR REPLACE VIEW "getArticles" AS
SELECT "public"."articles"."articleId"
	, "public"."articles"."importedAt"
	, "public"."articles"."createdAt"
	, "public"."articles"."externalId"
	, "public"."articles"."rate"
	, "public"."articles"."sourceFeedId"
	, "public"."articles"."authorId"
	, "public"."articles"."statusId"
 FROM "public"."articles"
	WHERE "public"."articles"."statusId" != 3
ORDER BY "createdAt" DESC, "articleId" DESC;

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

CREATE OR REPLACE VIEW "getArticleRecords" AS
SELECT "public"."articleRecords"."articleRecordId"
	, "public"."articleRecords"."content"
	, "public"."articleRecords"."likes"
	, "public"."articleRecords"."link"
	, "public"."articleRecords"."photos"
	, "public"."articleRecords"."rate"
	, "public"."articleRecords"."retweet"
	, "public"."articleRecords"."video"
	, "public"."articleRecords"."music"
	, "public"."articleRecords"."map"
	, "public"."articleRecords"."poll"
	, "public"."articleRecords"."text_links"
	, "public"."articleRecords"."doc"
	, "public"."articleRecords"."articleId"
	, "public"."articleRecords"."articleQueueId"
 FROM "public"."articleRecords";
 
CREATE OR REPLACE VIEW "getPublishers" AS
SELECT "public"."publishers"."publisherId"
	, "public"."publishers"."name"
	, "public"."publishers"."vk_id"
	, "public"."publishers"."vk_app"
	, "public"."publishers"."vk_token"
	, "public"."publishers"."vk_seckey"
	, "public"."publishers"."statusId"
 FROM "public"."publishers"
	WHERE "public"."publishers"."statusId" != 3
ORDER BY "publisherId";

CREATE OR REPLACE VIEW "getTargetFeedGrids" AS
SELECT "public"."targetFeedGrids"."targetFeedGridId"
	, "public"."targetFeedGrids"."startDate"
	, "public"."targetFeedGrids"."period"
	, "public"."targetFeedGrids"."targetFeedId"
 FROM "public"."targetFeedGrids"
ORDER BY "public"."targetFeedGrids"."startDate";

CREATE OR REPLACE VIEW "getTargetFeedPublishers" AS
SELECT "public"."targetFeedPublishers"."targetFeedId"
	, "public"."targetFeedPublishers"."publisherId"
	, "publisher"."publisherId" AS "publisher.publisherId"
	, "publisher"."name" AS "publisher.name"
	, "publisher"."vk_id" AS "publisher.vk_id"
	, "publisher"."vk_app" AS "publisher.vk_app"
	, "publisher"."vk_token" AS "publisher.vk_token"
	, "publisher"."vk_seckey" AS "publisher.vk_seckey"
	, "publisher"."statusId" AS "publisher.statusId"
 FROM "public"."targetFeedPublishers"
	INNER JOIN "public"."publishers" "publisher" ON
		"publisher"."publisherId" = "public"."targetFeedPublishers"."publisherId";
		
CREATE OR REPLACE VIEW "getGridLines" AS
SELECT "public"."gridLines"."gridLineId"
	, "public"."gridLines"."startDate"
	, "public"."gridLines"."endDate"
	, "public"."gridLines"."time"
	, "public"."gridLines"."type"
	, "public"."gridLines"."targetFeedId"
 FROM "public"."gridLines"
ORDER BY "time" DESC;

CREATE OR REPLACE VIEW "getGridLineItems" AS
SELECT "public"."gridLineItems"."gridLineItemId"
	, "public"."gridLineItems"."date"
	, "public"."gridLineItems"."gridLineId"
 FROM "public"."gridLineItems";