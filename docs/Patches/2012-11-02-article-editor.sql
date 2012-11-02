alter table "articles" add column "editor" Varchar(100);

DROP VIEW "getArticles";

CREATE OR REPLACE VIEW "getArticles" AS
SELECT "public"."articles"."articleId"
	, "public"."articles"."importedAt"
	, "public"."articles"."createdAt"
	, "public"."articles"."queuedAt"
	, "public"."articles"."sentAt"
	, "public"."articles"."externalId"
	, "public"."articles"."rate"
	, "public"."articles"."sourceFeedId"
	, "public"."articles"."targetFeedId"
	, "public"."articles"."authorId"
	, "public"."articles"."editor"
	, "public"."articles"."statusId"
 FROM "public"."articles"
	WHERE "public"."articles"."statusId" != 3
ORDER BY "createdAt" DESC, "articleId" DESC;