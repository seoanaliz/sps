alter table "articleRecords" add column "topfaceData" Text;

DROP VIEW "getArticleRecords";

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
	, "public"."articleRecords"."topfaceData"
	, "public"."articleRecords"."articleId"
	, "public"."articleRecords"."articleQueueId"
 FROM "public"."articleRecords";

