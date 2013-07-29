alter table "promotionPost" add column id serial;
alter table "promotionPost" add column image_width integer;
alter table "promotionPost" add column image_height integer;

CREATE OR REPLACE VIEW "getPromotionPost" AS
SELECT
    "promotionPost"."publicId",
    "promotionPost".platform,
    "promotionPost"."headerText",
    "promotionPost"."imgUrl",
    "promotionPost".text,
    "promotionPost"."actionText",
    "promotionPost"."actionUrl",
    "promotionPost".index,
    "promotionPost".active,
    "promotionPost"."showsCount",
    "promotionPost"."image_height",
    "promotionPost"."image_width",
    "promotionPost"."id"
FROM "promotionPost";

alter table categories add column id serial;

CREATE OR REPLACE VIEW "getCategories" AS
SELECT
   categories."publicId",
   categories.name,
   categories.mask,
   categories.index,
   categories.id
FROM categories;