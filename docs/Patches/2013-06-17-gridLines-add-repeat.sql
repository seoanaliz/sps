alter table "gridLines" add column "repeat" boolean;

CREATE OR REPLACE VIEW "getGridLines" AS
SELECT "public"."gridLines"."gridLineId"
	, "public"."gridLines"."startDate"
	, "public"."gridLines"."endDate"
	, "public"."gridLines"."time"
	, "public"."gridLines"."type"
	, "public"."gridLines"."targetFeedId"
        , "public"."gridLines"."repeat"
 FROM "public"."gridLines"
ORDER BY "time" DESC;
