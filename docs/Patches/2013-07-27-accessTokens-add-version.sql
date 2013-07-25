alter table "accessTokens" add column version integer;

CREATE OR REPLACE VIEW "getAccessTokens" AS
SELECT
      "accessTokens"."vkId"
    , "accessTokens"."accessToken"
    , "accessTokens"."appId"
    , "accessTokens"."createdAt"
    , "accessTokens"."statusId"
    , "accessTokens"."accessTokenId"
    , status."statusId" AS "status.statusId"
    , status.title AS "status.title"
    , status.alias AS "status.alias"
    , "accessTokens"."version"
FROM "accessTokens"
JOIN statuses status ON status."statusId" = "accessTokens"."statusId"
WHERE "accessTokens"."statusId" <> 3;