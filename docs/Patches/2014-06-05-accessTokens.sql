CREATE TABLE "accessTokens"
(
 "accessTokenId" serial NOT NULL,
  "vkId" character varying NOT NULL,
  "accessToken" character varying,
  "appId" integer NOT NULL,
  "createdAt" timestamp without time zone,
  "statusId" integer NOT NULL,
  CONSTRAINT "accessTokens_pkey" PRIMARY KEY ("accessTokenId" ),
  CONSTRAINT "accessTokens_statusId_fkey" FOREIGN KEY ("statusId")
      REFERENCES statuses ("statusId") MATCH SIMPLE
      ON UPDATE RESTRICT ON DELETE RESTRICT,
  CONSTRAINT "accessTokens_vkId_appId_key" UNIQUE ("vkId" , "appId" )
)

CREATE OR REPLACE VIEW "getAccessTokens" AS
SELECT "public"."accessTokens"."accessTokenId",
      "public"."accessTokens"."vkId"
	, "public"."accessTokens"."accessToken"
	, "public"."accessTokens"."appId"
	, "public"."accessTokens"."createdAt"
	, "public"."accessTokens"."statusId"
FROM "public"."accessTokens"
WHERE "public"."accessTokens"."statusId" != 3;