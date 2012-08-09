Create table "authorFeedViews"
(
	"authorId" Integer NOT NULL,
	"targetFeedId" Integer NOT NULL,
	"lastViewDate" Timestamp NOT NULL,
 primary key ("authorId","targetFeedId")
) Without Oids;

Create index "IX_FK_authorFeedViewsTargetFeedId_authorFeedViews" on "authorFeedViews" ("targetFeedId");
Alter table "authorFeedViews" add  foreign key ("targetFeedId") references "targetFeeds" ("targetFeedId") on update restrict on delete restrict;
Create index "IX_FK_authorFeedViewsAuthorId_authorFeedViews" on "authorFeedViews" ("authorId");
Alter table "authorFeedViews" add  foreign key ("authorId") references "authors" ("authorId") on update restrict on delete restrict;

CREATE OR REPLACE VIEW "getAuthorFeedViews" AS
SELECT "public"."authorFeedViews"."targetFeedId"
	, "public"."authorFeedViews"."authorId"
	, "public"."authorFeedViews"."lastViewDate"
 FROM "public"."authorFeedViews";