Create table "comments"
(
	"commentId" Serial NOT NULL,
	"text" Varchar(1000) NOT NULL,
	"createdAt" Timestamp NOT NULL Default now(),
	"articleId" Integer NOT NULL,
	"authorId" Integer,
	"editorId" Integer,
	"statusId" Integer NOT NULL,
 primary key ("commentId")
) Without Oids;

Create index "IX_FK_commentsStatusId_comments" on "comments" ("statusId");
Alter table "comments" add  foreign key ("statusId") references "statuses" ("statusId") on update restrict on delete restrict;
Create index "IX_FK_commentsArticleId_comments" on "comments" ("articleId");
Alter table "comments" add  foreign key ("articleId") references "articles" ("articleId") on update restrict on delete restrict;
Create index "IX_FK_commentsAuthorId_comments" on "comments" ("authorId");
Alter table "comments" add  foreign key ("authorId") references "authors" ("authorId") on update restrict on delete restrict;
Create index "IX_FK_commentsEditorId_comments" on "comments" ("editorId");
Alter table "comments" add  foreign key ("editorId") references "editors" ("editorId") on update restrict on delete restrict;

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