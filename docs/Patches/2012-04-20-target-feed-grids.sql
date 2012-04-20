Create table "targetFeedGrids"
(
	"targetFeedGridId" Serial NOT NULL,
	"startDate" Timestamp NOT NULL,
	"period" Integer NOT NULL,
	"targetFeedId" Integer NOT NULL,
 primary key ("targetFeedGridId")
) Without Oids;

Create index "IX_FK_targetFeedGridsTargetFeedId_targetFeedGrids" on "targetFeedGrids" ("targetFeedId");
Alter table "targetFeedGrids" add  foreign key ("targetFeedId") references "targetFeeds" ("targetFeedId") on update restrict on delete restrict;

CREATE OR REPLACE VIEW "getTargetFeedGrids" AS
SELECT "public"."targetFeedGrids"."targetFeedGridId"
	, "public"."targetFeedGrids"."startDate"
	, "public"."targetFeedGrids"."period"
	, "public"."targetFeedGrids"."targetFeedId"
 FROM "public"."targetFeedGrids"
ORDER BY "public"."targetFeedGrids"."startDate";