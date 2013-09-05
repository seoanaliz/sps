alter table "authors" add column "isBot" boolean default FALSE;
alter table "authors" add column "postFromBot" boolean default FALSE;

CREATE OR REPLACE VIEW "getAuthors" AS
SELECT "public"."authors"."authorId"
	, "public"."authors"."vkId"
	, "public"."authors"."firstName"
	, "public"."authors"."lastName"
	, "public"."authors"."avatar"
	, "public"."authors"."targetFeedIds"
	, "public"."authors"."statusId"
	, "public"."authors"."isBot"
	, "public"."authors"."postFromBot"
 FROM "public"."authors"
	WHERE "public"."authors"."statusId" != 3
ORDER BY "firstName", "lastName";