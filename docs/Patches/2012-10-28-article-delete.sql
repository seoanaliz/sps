ALTER TABLE "articleQueues" ADD COLUMN "deleteAt" timestamp without time zone;
COMMENT ON COLUMN "articleQueues"."deleteAt" IS 'Время, когда нужно удалить пост';

ALTER TABLE "articleQueues" ADD COLUMN "isDeleted" boolean;
ALTER TABLE "articleQueues" ALTER COLUMN "isDeleted" SET DEFAULT false;
COMMENT ON COLUMN "articleQueues"."isDeleted" IS 'Удален ли пост';