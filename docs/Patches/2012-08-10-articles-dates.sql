DROP VIEW "getArticles";

alter table "articles" add column "queuedAt" Timestamp;
alter table "articles" add column "sentAt" Timestamp;

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
	, "public"."articles"."statusId"
 FROM "public"."articles"
	WHERE "public"."articles"."statusId" != 3
ORDER BY "createdAt" DESC, "articleId" DESC;

UPDATE "articles" a
SET "queuedAt" = aq."createdAt"    
FROM "articleQueues" aq
WHERE a."articleId" = aq."articleId"
AND aq."statusId" = 4
AND a."authorId" IS NOT NULL;

UPDATE "articles" a
SET "sentAt" = aq."sentAt"
FROM "articleQueues" aq
WHERE a."articleId" = aq."articleId"
AND aq."statusId" = 5
AND a."authorId" IS NOT NULL;

Create index "IX_queuedAt" on "articles" using btree ("queuedAt") where ('queuedAt' IS NOT NULL);
Create index "IX_sentAt" on "articles" using btree ("sentAt") where ('sentAt' IS NOT NULL);

DROP VIEW "getAuthorEvents";

alter table "authorEvents" add column "isQueued" Boolean NOT NULL Default false;

CREATE OR REPLACE VIEW "getAuthorEvents" AS
SELECT "public"."authorEvents"."articleId"
	, "public"."authorEvents"."authorId"
	, "public"."authorEvents"."commentIds"
	, "public"."authorEvents"."isQueued"
	, "public"."authorEvents"."isSent"
 FROM "public"."authorEvents";