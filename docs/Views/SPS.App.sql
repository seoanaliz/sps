CREATE OR REPLACE VIEW "getAuthors" AS
SELECT "public"."authors"."authorId"
	, "public"."authors"."vkId"
	, "public"."authors"."firstName"
	, "public"."authors"."lastName"
	, "public"."authors"."avatar"
	, "public"."authors"."targetFeedIds"
	, "public"."authors"."statusId"
 FROM "public"."authors"
	WHERE "public"."authors"."statusId" != 3
ORDER BY "firstName", "lastName";

CREATE OR REPLACE VIEW "getComments" AS
SELECT "public"."comments"."commentId"
	, "public"."comments"."text"
	, "public"."comments"."createdAt"
	, "public"."comments"."articleId"
	, "public"."comments"."authorId"
	, "public"."comments"."editorId"
	, "public"."comments"."statusId"
 FROM "public"."comments"
	WHERE "public"."comments"."statusId" != 3;