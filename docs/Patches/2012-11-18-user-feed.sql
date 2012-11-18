CREATE TABLE "userFeed"
(
  "vkId" integer NOT NULL, -- Идентификатор вконтакте
  role smallint NOT NULL, -- код роли пользователя на этой ленте
  "targetFeedId" integer NOT NULL, -- Идентификатор ленты
  CONSTRAINT "userFeed_PK" PRIMARY KEY ("vkId", role, "targetFeedId")
)
WITH (
  OIDS=FALSE
);
COMMENT ON COLUMN "userFeed"."vkId" IS 'Идентификатор вконтакте';
COMMENT ON COLUMN "userFeed".role IS 'код роли пользователя на этой ленте';
COMMENT ON COLUMN "userFeed"."targetFeedId" IS 'Идентификатор ленты';

-- migration
CREATE OR REPLACE FUNCTION migrate_roles(integer[], integer) RETURNS boolean AS '
    DECLARE
        i INTEGER;
        arr ALIAS FOR $1;
        vkId ALIAS FOR $2;
        targetFeedId INTEGER;
    BEGIN
        i := 1;
        WHILE arr[i] IS NOT NULL LOOP
            targetFeedId := arr[i];
            INSERT INTO "userFeed" ("vkId","role", "targetFeedId") VALUES (vkId, 0, targetFeedId);
            i := i + 1;
        END LOOP;
        RETURN true;
    END;
' LANGUAGE plpgsql;
SELECT migrate_roles("targetFeedIds", "vkId") FROM "editors";