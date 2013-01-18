ALTER TABLE "userFeed" DROP CONSTRAINT "userFeed_PK";
ALTER TABLE "userFeed" ADD CONSTRAINT "userFeed_PK" PRIMARY KEY ("vkId", "targetFeedId");