Create table "authorManages"
(
    "authorManageId" Serial NOT NULL,
    "authorVkId" Varchar(1000) NOT NULL,
    "editorVkId" Varchar(1000) NOT NULL,
    "createdAt" Timestamp NOT NULL Default now(),
    "action" Varchar(1000) NOT NULL Default 'add',
    "targetFeedId" Integer NOT NULL,
 primary key ("authorManageId")
) Without Oids;

CREATE OR REPLACE VIEW "getAuthorManages" AS
SELECT
    "public"."authorManages"."authorManageId",
    "public"."authorManages"."authorVkId",
    "public"."authorManages"."editorVkId",
    "public"."authorManages"."createdAt",
    "public"."authorManages"."action",
    "public"."authorManages"."targetFeedId"
 FROM "public"."authorManages"
ORDER BY "public"."authorManages"."createdAt";