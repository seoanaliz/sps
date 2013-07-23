
CREATE TABLE "androidSettings"
(
  "minVersion" character varying,
  "curVersion" character varying,
  "shareText" character varying,
  "publicId" integer
);
CREATE INDEX "androidSettings_publicId"
  ON "androidSettings"
  USING btree
  ("publicId" );

CREATE TABLE banners
(
  "publicId" integer,
  platform character varying,
  "bannerId" character varying,
  prob integer,
  "imgUrl" character varying,
  "actionUrl" character varying,
  active character varying
);
CREATE INDEX banners_platform
  ON banners
  USING btree
  (platform COLLATE pg_catalog."default" );

CREATE INDEX "banners_publicId"
  ON banners
  USING btree
  ("publicId" );


CREATE TABLE categories
(
  "publicId" integer,
  name character varying,
  mask character varying,
  index integer
);
CREATE INDEX "categories_publicId"
  ON categories
  USING btree
  ("publicId" );

CREATE TABLE "iOSsettings"
(
  "minVersion" character varying,
  "curVersion" character varying,
  "shareText" character varying,
  "publicId" integer
);

CREATE INDEX "iOSsettings_publicId"
  ON "iOSsettings"
  USING btree
  ("publicId" );

CREATE TABLE "promotionPost"
(
  "publicId" integer,
  platform character varying,
  "headerText" character varying,
  "imgUrl" character varying,
  text character varying,
  "actionText" character varying,
  "actionUrl" character varying,
  index integer,
  active character varying,
  "showsCount" integer
);


CREATE INDEX "promotionPost_platform"
  ON "promotionPost"
  USING btree
  (platform COLLATE pg_catalog."default" );

CREATE INDEX "promotionPost_publicId"
  ON "promotionPost"
  USING btree
  ("publicId" );


CREATE OR REPLACE VIEW "getAndroidSettings" AS
SELECT "androidSettings"."minVersion", "androidSettings"."curVersion", "androidSettings"."shareText", "androidSettings"."publicId"
 FROM "androidSettings";

CREATE OR REPLACE VIEW "getBanners" AS
SELECT banners."publicId", banners.platform, banners."bannerId", banners.prob, banners."imgUrl", banners."actionUrl", banners.active
FROM banners;

CREATE OR REPLACE VIEW "getCategories" AS
 SELECT categories."publicId", categories.name, categories.mask, categories.index
   FROM categories;

CREATE OR REPLACE VIEW "getIOSsettings" AS
 SELECT "iOSsettings"."minVersion", "iOSsettings"."curVersion", "iOSsettings"."shareText", "iOSsettings"."publicId"
   FROM "iOSsettings";

CREATE OR REPLACE VIEW "getPromotionPost" AS
SELECT "promotionPost"."publicId", "promotionPost".platform, "promotionPost"."headerText", "promotionPost"."imgUrl", "promotionPost".text, "promotionPost"."actionText", "promotionPost"."actionUrl", "promotionPost".index, "promotionPost".active, "promotionPost"."showsCount"
   FROM "promotionPost";