
CREATE TABLE "articleCounters"
(
  "vkId" integer NOT NULL, -- Для кого это событие
  "counterType" integer NOT NULL, -- Код типа счетчика
  "counterValue" integer NOT NULL
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "articleCounters"
  OWNER TO postgres;
COMMENT ON TABLE "articleCounters"
  IS 'События постов';
COMMENT ON COLUMN "articleCounters"."vkId" IS 'Для кого это событие';
COMMENT ON COLUMN "articleCounters"."counterType" IS 'Код типа счетчика';