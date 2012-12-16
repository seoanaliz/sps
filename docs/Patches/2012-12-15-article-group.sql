ALTER TABLE "articles" ADD COLUMN "articleGroupId" int;
ALTER TABLE "articles" ALTER COLUMN "articleGroupId" SET DEFAULT NULL;
COMMENT ON COLUMN "articles"."articleGroupId" IS 'Группа поста';


CREATE OR REPLACE VIEW "getArticles" AS
SELECT "public"."articles"."articleId"
	, "public"."articles"."importedAt"
	, "public"."articles"."createdAt"
	, "public"."articles"."queuedAt"
	, "public"."articles"."sentAt"
	, "public"."articles"."externalId"
	, "public"."articles"."rate"
	, "public"."articles"."sourceFeedId"
	, "public"."articles"."targetFeedId"
	, "public"."articles"."authorId"
	, "public"."articles"."editor"
	, "public"."articles"."isCleaned"
	, "public"."articles"."statusId"
	, "public"."articles"."articleStatus"
	, "public"."articles"."articleGroupId"
 FROM "public"."articles"
	WHERE "public"."articles"."statusId" != 3
ORDER BY "createdAt" DESC, "articleId" DESC;


CREATE TABLE "articleGroup"
(
  "articleGroupId" serial NOT NULL,
  name text,
  targetFeedId integer NOT NULL,
  CONSTRAINT "articleGroup_PK" PRIMARY KEY ("articleGroupId"),
  CONSTRAINT "targetFeed_FK" FOREIGN KEY (targetFeedId)
      REFERENCES "targetFeeds" ("targetFeedId") MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION
)
WITH (
  OIDS=FALSE
);

CREATE INDEX "fki_targetFeed_FK"
  ON "articleGroup"
  USING btree
  (targetFeedId);



CREATE TABLE "userArticleGroup"
(
  "vkId" integer NOT NULL, -- Идентификатор вконтакте
  "targetFeedId" integer NOT NULL, -- Идентификатор ленты
  "articleGroupId" integer NOT NULL, -- Идентификатор группы постов
  CONSTRAINT "userArticleGroup_PK" PRIMARY KEY ("vkId", "articleGroupId", "targetFeedId"),
  CONSTRAINT "articleGroup_FK" FOREIGN KEY ("articleGroupId")
      REFERENCES "articleGroup" ("articleGroupId") MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT "uag_targetFeed_FK" FOREIGN KEY ("targetFeedId")
      REFERENCES "targetFeeds" ("targetFeedId") MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION
)
WITH (
  OIDS=FALSE
);


COMMENT ON COLUMN "userArticleGroup"."vkId" IS 'Идентификатор вконтакте';
COMMENT ON COLUMN "userArticleGroup"."targetFeedId" IS 'Идентификатор ленты';
COMMENT ON COLUMN "userArticleGroup"."articleGroupId" IS 'Идентификатор группы постов';


CREATE INDEX "fki_articleGroup_FK"
  ON "userArticleGroup"
  USING btree
  ("articleGroupId");


CREATE INDEX "fki_uag_targetFeed_FK"
  ON "userArticleGroup"
  USING btree
  ("targetFeedId");