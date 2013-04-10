ALTER TABLE "public"."articleRecords" ADD COLUMN "repostPublicImage" character varying;
ALTER TABLE "public"."articleRecords" ADD COLUMN "repostPublicTitle" character varying;

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
    , "public"."articleRecords"."repostArticleRecordId"
    , "public"."articleRecords"."repostExternalId"
    , "public"."articleRecords"."createdVia"
    , "public"."articleRecords"."repostPublicImage"
    , "public"."articleRecords"."repostPublicTitle"
 FROM "public"."articleRecords";