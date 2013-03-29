alter table "articleRecords" add column "repostArticleRecordId" Int Default NULL;
alter table "articleRecords" add column "repostExternalId" Character Varying Default NULL;

CREATE OR REPLACE VIEW "getArticleRecords" AS
 SELECT "articleRecords"."articleRecordId",
        "articleRecords".content,
        "articleRecords".likes,
        "articleRecords".link,
        "articleRecords".photos,
        "articleRecords".rate,
        "articleRecords".retweet,
        "articleRecords".video,
        "articleRecords".music,
        "articleRecords".map,
        "articleRecords".poll,
        "articleRecords".text_links,
        "articleRecords".doc,
        "articleRecords"."topfaceData",
        "articleRecords"."articleId",
        "articleRecords"."articleQueueId",
        "articleRecords"."repostArticleRecordId",
        "articleRecords"."repostExternalId"
   FROM "articleRecords";
