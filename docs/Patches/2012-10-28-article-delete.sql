ALTER TABLE articles ADD COLUMN "deleteAt" timestamp without time zone;
COMMENT ON COLUMN articles."deleteAt" IS 'Время, когда нужно удалить пост';

ALTER TABLE articles ADD COLUMN "isDeleted" boolean;
ALTER TABLE articles ALTER COLUMN "isDeleted" SET DEFAULT false;
COMMENT ON COLUMN articles."isDeleted" IS 'Удален ли пост';