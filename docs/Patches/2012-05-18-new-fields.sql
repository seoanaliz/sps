alter table "articleRecords" add column "rate" Integer;
alter table "articleRecords" add column "retweet" Text;
alter table "articleRecords" add column "video" Text;
alter table "articleRecords" add column "music" Text;
alter table "articleRecords" add column "map" Varchar(500);
alter table "articleRecords" add column "poll" Varchar(500);
alter table "articleRecords" add column "text_links" Text;
alter table "articleRecords" add column "doc" Varchar(500);

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
	, "public"."articleRecords"."articleId"
	, "public"."articleRecords"."articleQueueId"
 FROM "public"."articleRecords";