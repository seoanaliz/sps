alter table "articles" add column "authorId" Integer;
alter table "articles" add column "targetFeedId" Integer;

Create index "IX_FK_articlesAuthorId_articles" on "articles" ("authorId");
Alter table "articles" add  foreign key ("authorId") references "authors" ("authorId") on update restrict on delete restrict;
Create index "IX_FK_articlesTargetFeedId_articles" on "articles" ("targetFeedId");
Alter table "articles" add  foreign key ("targetFeedId") references "targetFeeds" ("targetFeedId") on update restrict on delete restrict;

alter table "articles" DROP CONSTRAINT "articles_sourceFeedId_fkey";

DROP VIEW "getArticles";
CREATE OR REPLACE VIEW "getArticles" AS
SELECT "public"."articles"."articleId"
	, "public"."articles"."importedAt"
	, "public"."articles"."createdAt"
	, "public"."articles"."externalId"
	, "public"."articles"."rate"
	, "public"."articles"."sourceFeedId"
	, "public"."articles"."targetFeedId"
	, "public"."articles"."authorId"
	, "public"."articles"."statusId"
 FROM "public"."articles"
	WHERE "public"."articles"."statusId" != 3
ORDER BY "createdAt" DESC, "articleId" DESC;