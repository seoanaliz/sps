DROP VIEW "getAuthors";

ALTER TABLE "authors" DROP COLUMN "targetFeedIds";
ALTER TABLE "authors" ADD COLUMN "targetFeedIds" Integer[];

CREATE OR REPLACE VIEW "getAuthors" AS
SELECT "public"."authors"."authorId"
	, "public"."authors"."vkId"
	, "public"."authors"."firstName"
	, "public"."authors"."lastName"
	, "public"."authors"."avatar"
	, "public"."authors"."targetFeedIds"
	, "public"."authors"."statusId"
 FROM "public"."authors"
	WHERE "public"."authors"."statusId" != 3;