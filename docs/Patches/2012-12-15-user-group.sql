CREATE TABLE "userGroup"
(
  "userGroupId" serial NOT NULL,
  name text,
  "targetFeedId" integer NOT NULL,
  CONSTRAINT "userGroup_PK" PRIMARY KEY ("userGroupId"),
  CONSTRAINT "targetFeed_FK" FOREIGN KEY ("targetFeedId")
      REFERENCES "targetFeeds" ("targetFeedId") MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION
)
WITH (
  OIDS=FALSE
);

CREATE INDEX "fki_targetFeed_FK"
  ON "userGroup"
  USING btree
  ("targetFeedId");



CREATE TABLE "userUserGroup"
(
  "vkId" integer NOT NULL, -- Идентификатор вконтакте
  "userGroupId" integer NOT NULL, -- Идентификатор группы постов
  CONSTRAINT "userUserGroup_PK" PRIMARY KEY ("vkId", "userGroupId"),
  CONSTRAINT "userGroup_FK" FOREIGN KEY ("userGroupId")
      REFERENCES "userGroup" ("userGroupId") MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION
)
WITH (
  OIDS=FALSE
);


COMMENT ON COLUMN "userUserGroup"."vkId" IS 'Идентификатор вконтакте';
COMMENT ON COLUMN "userUserGroup"."targetFeedId" IS 'Идентификатор ленты';
COMMENT ON COLUMN "userUserGroup"."userGroupId" IS 'Идентификатор группы постов';


CREATE INDEX "fki_userGroup_FK"
  ON "useruserGroup"
  USING btree
  ("userGroupId");


CREATE INDEX "fki_uag_targetFeed_FK"
  ON "useruserGroup"
  USING btree
  ("targetFeedId");