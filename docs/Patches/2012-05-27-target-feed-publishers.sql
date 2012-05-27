Create table "targetFeedPublishers"
(
	"targetFeedId" Integer NOT NULL,
	"publisherId" Integer NOT NULL,
 primary key ("targetFeedId","publisherId")
) Without Oids;

Create index "IX_FK_targetFeedPublishersTagetFeedId_targetFeedPublishers" on "targetFeedPublishers" ("targetFeedId");
Alter table "targetFeedPublishers" add  foreign key ("targetFeedId") references "targetFeeds" ("targetFeedId") on update restrict on delete restrict;
Create index "IX_FK_targetFeedPublishersPublisherId_targetFeedPublishers" on "targetFeedPublishers" ("publisherId");
Alter table "targetFeedPublishers" add  foreign key ("publisherId") references "publishers" ("publisherId") on update restrict on delete restrict;

CREATE OR REPLACE VIEW "getTargetFeedPublishers" AS
SELECT "public"."targetFeedPublishers"."targetFeedId"
	, "public"."targetFeedPublishers"."publisherId"
	, "publisher"."publisherId" AS "publisher.publisherId"
	, "publisher"."name" AS "publisher.name"
	, "publisher"."vk_id" AS "publisher.vk_id"
	, "publisher"."vk_app" AS "publisher.vk_app"
	, "publisher"."vk_token" AS "publisher.vk_token"
	, "publisher"."vk_seckey" AS "publisher.vk_seckey"
	, "publisher"."statusId" AS "publisher.statusId"
 FROM "public"."targetFeedPublishers"
	INNER JOIN "public"."publishers" "publisher" ON
		"publisher"."publisherId" = "public"."targetFeedPublishers"."publisherId";
		
INSERT INTO "targetFeedPublishers"
SELECT "targetFeedId", "publisherId"
FROM "targetFeeds"
WHERE "publisherId" IS NOT NULL;