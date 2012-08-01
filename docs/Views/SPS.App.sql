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
	, "author"."authorId" AS "author.authorId"
	, "author"."vkId" AS "author.vkId"
	, "author"."firstName" AS "author.firstName"
	, "author"."lastName" AS "author.lastName"
	, "author"."avatar" AS "author.avatar"
	, "editor"."editorId" AS "editor.editorId"
	, "editor"."vkId" AS "editor.vkId"
	, "editor"."firstName" AS "editor.firstName"
	, "editor"."lastName" AS "editor.lastName"
	, "editor"."avatar" AS "editor.avatar"
 FROM "public"."comments"
	LEFT JOIN "public"."authors" "author" ON
		"author"."authorId" = "public"."comments"."authorId"
	LEFT JOIN "public"."editors" "editor" ON
		"editor"."editorId" = "public"."comments"."editorId"
	WHERE "public"."comments"."statusId" != 3
ORDER BY "public"."comments"."createdAt" DESC;