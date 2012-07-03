Create table "gridLines"
(
	"gridLineId" Serial NOT NULL,
	"startDate" Date NOT NULL,
	"endDate" Date NOT NULL,
	"time" Time NOT NULL,
	"type" Varchar(10) NOT NULL,
	"targetFeedId" Integer NOT NULL,
 primary key ("gridLineId")
) Without Oids;


Create table "gridLineItems"
(
	"gridLineItemId" Serial NOT NULL,
	"date" Timestamp NOT NULL,
	"gridLineId" Integer NOT NULL,
 primary key ("gridLineItemId")
) Without Oids;

Create index "IX_FK_gridLinesTargetFeedId_gridLines" on "gridLines" ("targetFeedId");
Alter table "gridLines" add  foreign key ("targetFeedId") references "targetFeeds" ("targetFeedId") on update restrict on delete restrict;
Create index "IX_FK_gridLineItemsGridLineId_gridLineItems" on "gridLineItems" ("gridLineId");
Alter table "gridLineItems" add  foreign key ("gridLineId") references "gridLines" ("gridLineId") on update restrict on delete restrict;

CREATE OR REPLACE VIEW "getGridLines" AS
SELECT "public"."gridLines"."gridLineId"
	, "public"."gridLines"."startDate"
	, "public"."gridLines"."endDate"
	, "public"."gridLines"."time"
	, "public"."gridLines"."type"
	, "public"."gridLines"."targetFeedId"
 FROM "public"."gridLines"
ORDER BY "time" DESC;

CREATE OR REPLACE VIEW "getGridLineItems" AS
SELECT "public"."gridLineItems"."gridLineItemId"
	, "public"."gridLineItems"."date"
	, "public"."gridLineItems"."gridLineId"
 FROM "public"."gridLineItems";