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