Create table "authors"
(
	"authorId" Serial NOT NULL,
	"vkId" Integer NOT NULL UNIQUE,
	"firstName" Varchar(1000),
	"lastName" Varchar(1000),
	"avatar" Varchar(1000),
	"targetFeedIds" Text,
	"statusId" Integer NOT NULL,
 primary key ("authorId")
) Without Oids;

Create index "IX_FK_authorsStatusId_authors" on "authors" ("statusId");
Alter table "authors" add  foreign key ("statusId") references "statuses" ("statusId") on update restrict on delete restrict;

CREATE OR REPLACE VIEW "getAuthors" AS
SELECT "public"."authors"."authorId"
	, "public"."authors"."vkId"
	, "public"."authors"."firstName"
	, "public"."authors"."lastName"
	, "public"."authors"."avatar"
	, "public"."authors"."targetFeedIds"
	, "public"."authors"."statusId"
 FROM "public"."authors"
	WHERE "public"."authors"."statusId" != 3;