Create table "editors"
(
	"editorId" Serial NOT NULL,
	"vkId" Integer NOT NULL UNIQUE,
	"firstName" Varchar(1000),
	"lastName" Varchar(1000),
	"avatar" Varchar(1000),
	"targetFeedIds" Integer[],
	"statusId" Integer NOT NULL,
 primary key ("editorId")
) Without Oids;

Create index "IX_FK_editorsStatusId_editors" on "editors" ("statusId");
Alter table "editors" add  foreign key ("statusId") references "statuses" ("statusId") on update restrict on delete restrict;

CREATE OR REPLACE VIEW "getEditors" AS
SELECT "public"."editors"."editorId"
	, "public"."editors"."vkId"
	, "public"."editors"."firstName"
	, "public"."editors"."lastName"
	, "public"."editors"."avatar"
	, "public"."editors"."targetFeedIds"
	, "public"."editors"."statusId"
 FROM "public"."editors"
	WHERE "public"."editors"."statusId" != 3;