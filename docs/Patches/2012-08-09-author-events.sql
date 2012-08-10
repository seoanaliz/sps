Create table "authorEvents"
(
	"articleId" Integer NOT NULL,
	"authorId" Integer NOT NULL,
	"commentIds" Integer[],
	"isSent" Boolean NOT NULL Default false,
 primary key ("articleId")
) Without Oids;

Create index "IX_FK_authorEventsArticleId_authorEvents" on "authorEvents" ("articleId");
Alter table "authorEvents" add  foreign key ("articleId") references "articles" ("articleId") on update restrict on delete restrict;

Create index "IX_FK_authorEventsAuthorId_authorEvents" on "authorEvents" ("authorId");
Alter table "authorEvents" add  foreign key ("authorId") references "authors" ("authorId") on update restrict on delete restrict;

CREATE OR REPLACE VIEW "getAuthorEvents" AS
SELECT "public"."authorEvents"."articleId"
	, "public"."authorEvents"."authorId"
	, "public"."authorEvents"."commentIds"
	, "public"."authorEvents"."isSent"
 FROM "public"."authorEvents";