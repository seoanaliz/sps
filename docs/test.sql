INSERT INTO "public"."sourceFeeds" ("title", "externalId", "statusId")
VALUES ('animal.world', '27052479', 1);

INSERT INTO "public"."publishers" ("name", "vk_id", "vk_app", "vk_token", "vk_seckey", "statusId")
VALUES ('test publisher', 0, 0, 'c7ac1842c3ddb889c3ddb88974c3f6e60dcc3ddc3d8801f85c16a3c7336abd4', 'V1us1w3lbkoaapuYiddg', 1);

INSERT INTO "public"."targetFeeds" ("title", "externalId", "publisherId", "statusId")
VALUES ('test wall 1', '27421965', 1, 1);
INSERT INTO "public"."targetFeeds" ("title", "externalId", "publisherId", "statusId")
VALUES ('test wall 2', '27421965', 1, 1);
INSERT INTO "public"."targetFeeds" ("title", "externalId", "publisherId", "statusId")
VALUES ('test wall 3', '27421965', 1, 1);