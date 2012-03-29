/*
Created		16.08.2008
Modified		20.03.2012
Project		
Model			
Company		
Author		
Version		
Database		PostgreSQL 8.1 
*/


/* Create Domains */


/* Create Sequences */


/* Create Tables */


Create table "users"
(
	"userId" Serial NOT NULL,
	"login" Varchar(64) NOT NULL,
	"password" Varchar(64) NOT NULL,
	"statusId" Integer NOT NULL,
 primary key ("userId")
) Without Oids;


Create table "statuses"
(
	"statusId" Serial NOT NULL,
	"title" Varchar(255) NOT NULL,
	"alias" Varchar(64) NOT NULL UNIQUE,
 primary key ("statusId")
) Without Oids;


Create table "daemonLocks"
(
	"daemonLockId" Serial NOT NULL,
	"title" Varchar(255) NOT NULL,
	"packageName" Varchar(255) NOT NULL,
	"methodName" Varchar(255) NOT NULL,
	"runAt" Timestamp NOT NULL Default now(),
	"maxExecutionTime" Interval NOT NULL Default '00:03:00',
 primary key ("daemonLockId")
) Without Oids;


Create table "vfsFiles"
(
	"fileId" Serial NOT NULL,
	"folderId" Integer NOT NULL,
	"title" Varchar(255) NOT NULL,
	"path" Varchar(255) NOT NULL,
	"params" Text,
	"isFavorite" Boolean Default false,
	"mimeType" Varchar(255) NOT NULL,
	"fileSize" Integer Default 0,
	"fileExists" Boolean NOT NULL Default true,
	"statusId" Integer NOT NULL,
	"createdAt" Timestamp NOT NULL Default now(),
 primary key ("fileId")
) Without Oids;


Create table "vfsFoldersTree"
(
	"objectId" Integer NOT NULL,
	"parentId" Integer,
	"path"  Varchar(255),
	"rKey" Integer,
	"lKey" Integer,
 primary key ("objectId")
) Without Oids;


Create table "vfsFolders"
(
	"folderId" Serial NOT NULL,
	"parentFolderId" Integer,
	"title" Varchar(255) NOT NULL,
	"isFavorite" Boolean Default false,
	"createdAt" Timestamp NOT NULL Default now(),
	"statusId" Integer NOT NULL,
 primary key ("folderId")
) Without Oids;


Create table "metaDetails"
(
	"metaDetailId" Serial NOT NULL,
	"url" Varchar(255) NOT NULL,
	"pageTitle" Varchar(255),
	"metaKeywords" Varchar(1024),
	"metaDescription" Varchar(1024),
	"alt" Varchar(255),
	"isInheritable" Boolean NOT NULL Default false,
	"statusId" Integer NOT NULL,
 primary key ("metaDetailId")
) Without Oids;


Create table "siteParams"
(
	"siteParamId" Serial NOT NULL,
	"alias" Varchar(32) NOT NULL,
	"value" Varchar(255) NOT NULL,
	"description" Varchar(255),
	"statusId" Integer NOT NULL,
 primary key ("siteParamId")
) Without Oids;


Create table "staticPages"
(
	"staticPageId" Serial NOT NULL,
	"title" Varchar(255) NOT NULL,
	"url" Varchar(255) NOT NULL,
	"content" Text,
	"pageTitle" Varchar(255),
	"metaKeywords" Varchar(2048),
	"metaDescription" Varchar(2048),
	"orderNumber" Integer,
	"parentStaticPageId" Integer,
	"statusId" Integer NOT NULL,
 primary key ("staticPageId")
) Without Oids;


Create table "navigations"
(
	"navigationId" Serial NOT NULL,
	"navigationTypeId" Integer NOT NULL,
	"title" Varchar(255),
	"orderNumber" Integer NOT NULL Default 1,
	"staticPageId" Integer,
	"url" Varchar(255),
	"statusId" Integer NOT NULL,
 primary key ("navigationId")
) Without Oids;


Create table "navigationTypes"
(
	"navigationTypeId" Serial NOT NULL,
	"title" Varchar(255) NOT NULL,
	"alias" Varchar(32) NOT NULL,
	"statusId" Integer NOT NULL,
 primary key ("navigationTypeId")
) Without Oids;


Create table "articles"
(
	"articleId" Serial NOT NULL,
	"importedAt" Timestamp NOT NULL,
	"createdAt" Timestamp,
	"externalId" Varchar(100) NOT NULL,
	"sourceFeedId" Integer NOT NULL,
	"statusId" Integer NOT NULL,
 primary key ("articleId")
) Without Oids;


Create table "articleQueues"
(
	"articleQueueId" Serial NOT NULL,
	"startDate" Timestamp NOT NULL,
	"endDate" Timestamp NOT NULL,
	"createdAt" Timestamp NOT NULL,
	"sentAt" Timestamp,
	"articleId" Integer NOT NULL,
	"targetFeedId" Integer NOT NULL,
	"statusId" Integer NOT NULL,
 primary key ("articleQueueId")
) Without Oids;


Create table "articleRecords"
(
	"articleRecordId" Serial NOT NULL,
	"content" Text NOT NULL,
	"likes" Integer,
	"photos" Text,
	"articleId" Integer,
	"articleQueueId" Integer,
 primary key ("articleRecordId")
) Without Oids;


Create table "sourceFeeds"
(
	"sourceFeedId" Serial NOT NULL,
	"title" Varchar(500) NOT NULL,
	"externalId" Varchar(100) NOT NULL,
	"useFullExport" Boolean NOT NULL Default false,
	"processed" Varchar(100),
	"statusId" Integer NOT NULL,
 primary key ("sourceFeedId")
) Without Oids;


Create table "targetFeeds"
(
	"targetFeedId" Serial NOT NULL,
	"title" Varchar(500) NOT NULL,
	"externalId" Varchar(100) NOT NULL,
	"publisherId" Integer NOT NULL,
	"statusId" Integer NOT NULL,
 primary key ("targetFeedId")
) Without Oids;


Create table "publishers"
(
	"publisherId" Serial NOT NULL,
	"name" Varchar(100) NOT NULL,
	"vk_id" Integer NOT NULL,
	"vk_app" Integer NOT NULL,
	"vk_token" Varchar(128) NOT NULL,
	"vk_seckey" Varchar(64) NOT NULL,
	"statusId" Integer NOT NULL,
 primary key ("publisherId")
) Without Oids;


Create table "auditEvents"
(
	"auditEventId" Serial NOT NULL,
	"object" Varchar(100),
	"objectId" Varchar(200),
	"message" Text,
	"createdAt" Timestamp NOT NULL Default now(),
	"auditEventTypeId" Integer NOT NULL,
 primary key ("auditEventId")
) Without Oids;


Create table "auditEventTypes"
(
	"auditEventTypeId" Serial NOT NULL,
	"title" Varchar(1000) NOT NULL,
	"alias" Varchar(1000) NOT NULL,
 primary key ("auditEventTypeId")
) Without Oids;


/* Create Tab 'Others' for Selected Tables */


/* Create Alternate Keys */


/* Create Indexes */
Create unique index "IX_daemonLock" on "daemonLocks" using btree ("title","packageName","methodName");
Create index "IX_vfsFoldersTreeTreePath" on "vfsFoldersTree" using gist ("path");
Create index "IX_vfsFoldersTreeTreeRKey" on "vfsFoldersTree" using btree ("rKey");
Create index "IX_vfsFoldersTreeTreeLKey" on "vfsFoldersTree" using btree ("lKey");


/* Create Foreign Keys */
Create index "IX_FK_usersStatusId_users" on "users" ("statusId");
Alter table "users" add  foreign key ("statusId") references "statuses" ("statusId") on update restrict on delete restrict;
Create index "IX_FK_metaDetailsStatusId_metaDetails" on "metaDetails" ("statusId");
Alter table "metaDetails" add  foreign key ("statusId") references "statuses" ("statusId") on update restrict on delete restrict;
Create index "IX_FK_siteParamsStatusId_siteParams" on "siteParams" ("statusId");
Alter table "siteParams" add  foreign key ("statusId") references "statuses" ("statusId") on update restrict on delete restrict;
Create index "IX_FK_staticPagesStatusId_staticPages" on "staticPages" ("statusId");
Alter table "staticPages" add  foreign key ("statusId") references "statuses" ("statusId") on update restrict on delete restrict;
Create index "IX_FK_navigationTypesStatusId_navigationTypes" on "navigationTypes" ("statusId");
Alter table "navigationTypes" add  foreign key ("statusId") references "statuses" ("statusId") on update restrict on delete restrict;
Create index "IX_FK_navigationsStatusId_navigations" on "navigations" ("statusId");
Alter table "navigations" add  foreign key ("statusId") references "statuses" ("statusId") on update restrict on delete restrict;
Create index "IX_FK_vfsFoldersStatusId_vfsFolders" on "vfsFolders" ("statusId");
Alter table "vfsFolders" add  foreign key ("statusId") references "statuses" ("statusId") on update restrict on delete restrict;
Create index "IX_FK_vfsFilesStatusId_vfsFiles" on "vfsFiles" ("statusId");
Alter table "vfsFiles" add  foreign key ("statusId") references "statuses" ("statusId") on update restrict on delete restrict;
Create index "IX_FK_articlesStatusId_articles" on "articles" ("statusId");
Alter table "articles" add  foreign key ("statusId") references "statuses" ("statusId") on update restrict on delete restrict;
Create index "IX_FK_articleQueuesStatusId_articleQueues" on "articleQueues" ("statusId");
Alter table "articleQueues" add  foreign key ("statusId") references "statuses" ("statusId") on update restrict on delete restrict;
Create index "IX_FK_sourceFeedsStatusId_sourceFeeds" on "sourceFeeds" ("statusId");
Alter table "sourceFeeds" add  foreign key ("statusId") references "statuses" ("statusId") on update restrict on delete restrict;
Create index "IX_FK_targetFeedsStatusId_targetFeeds" on "targetFeeds" ("statusId");
Alter table "targetFeeds" add  foreign key ("statusId") references "statuses" ("statusId") on update restrict on delete restrict;
Create index "IX_FK_publishersStatusId_publishers" on "publishers" ("statusId");
Alter table "publishers" add  foreign key ("statusId") references "statuses" ("statusId") on update restrict on delete restrict;
Create index "IX_FK_vfsFoldersFolderId_vfsFolders" on "vfsFolders" ("parentFolderId");
Alter table "vfsFolders" add  foreign key ("parentFolderId") references "vfsFolders" ("folderId") on update restrict on delete restrict;
Create index "IX_FK_vfsFilesFolderId_vfsFiles" on "vfsFiles" ("folderId");
Alter table "vfsFiles" add  foreign key ("folderId") references "vfsFolders" ("folderId") on update restrict on delete restrict;
Create index "IX_FK_vfsFoldersTreeFolderId_vfsFoldersTree" on "vfsFoldersTree" ("objectId");
Alter table "vfsFoldersTree" add  foreign key ("objectId") references "vfsFolders" ("folderId") on update restrict on delete restrict;
Create index "IX_FK_vfsFoldersTreeParentId_vfsFoldersTree" on "vfsFoldersTree" ("parentId");
Alter table "vfsFoldersTree" add  foreign key ("parentId") references "vfsFolders" ("folderId") on update restrict on delete restrict;
Create index "IX_FK_navigationsStaticPageId_navigations" on "navigations" ("staticPageId");
Alter table "navigations" add  foreign key ("staticPageId") references "staticPages" ("staticPageId") on update restrict on delete restrict;
Create index "IX_FK_staticPagesParentStaticPageId_staticPages" on "staticPages" ("parentStaticPageId");
Alter table "staticPages" add  foreign key ("parentStaticPageId") references "staticPages" ("staticPageId") on update restrict on delete restrict;
Create index "IX_FK_navigationsNavigationTypeId_navigations" on "navigations" ("navigationTypeId");
Alter table "navigations" add  foreign key ("navigationTypeId") references "navigationTypes" ("navigationTypeId") on update restrict on delete restrict;
Create index "IX_FK_articleQueuesArticleId_articleQueues" on "articleQueues" ("articleId");
Alter table "articleQueues" add  foreign key ("articleId") references "articles" ("articleId") on update restrict on delete restrict;
Create index "IX_FK_articleRecordsArticleId_articleRecords" on "articleRecords" ("articleId");
Alter table "articleRecords" add  foreign key ("articleId") references "articles" ("articleId") on update restrict on delete restrict;
Create index "IX_FK_articleRecordsArticleQueueId_articleRecords" on "articleRecords" ("articleQueueId");
Alter table "articleRecords" add  foreign key ("articleQueueId") references "articleQueues" ("articleQueueId") on update restrict on delete restrict;
Create index "IX_FK_articlesSourceFeedId_articles" on "articles" ("sourceFeedId");
Alter table "articles" add  foreign key ("sourceFeedId") references "sourceFeeds" ("sourceFeedId") on update restrict on delete restrict;
Create index "IX_FK_articleQueuesTargetFeedId_articleQueues" on "articleQueues" ("targetFeedId");
Alter table "articleQueues" add  foreign key ("targetFeedId") references "targetFeeds" ("targetFeedId") on update restrict on delete restrict;
Create index "IX_FK_targetFeeds_publisherId_targetFeeds" on "targetFeeds" ("publisherId");
Alter table "targetFeeds" add  foreign key ("publisherId") references "publishers" ("publisherId") on update restrict on delete restrict;
Create index "IX_FK_auditEventsAuditEventTypeId_auditEvents" on "auditEvents" ("auditEventTypeId");
Alter table "auditEvents" add  foreign key ("auditEventTypeId") references "auditEventTypes" ("auditEventTypeId") on update restrict on delete restrict;


/* Create Procedures */


/* Create Views */


/* Create Referential Integrity Triggers */


/* Create User-Defined Triggers */


/* Create Roles */


/* Add Roles To Roles */


/* Create Role Permissions */
/* Role permissions on tables */

/* Role permissions on views */

/* Role permissions on procedures */


CREATE OR REPLACE VIEW "getStatuses" AS
SELECT "public"."statuses"."statusId"
	, "public"."statuses"."title"
	, "public"."statuses"."alias"
 FROM "public"."statuses";
	
CREATE OR REPLACE VIEW "getDaemonLocks" AS
SELECT "public"."daemonLocks"."daemonLockId"
	, "public"."daemonLocks"."title"
	, "public"."daemonLocks"."packageName"
	, "public"."daemonLocks"."methodName"
	, "public"."daemonLocks"."runAt"
	, "public"."daemonLocks"."maxExecutionTime"
	, ( now() - "runAt" < "maxExecutionTime" ) as "isActive"
 FROM "public"."daemonLocks";
 
CREATE OR REPLACE VIEW "getUsers" AS
SELECT "public"."users"."userId"
	, "public"."users"."login"
	, "public"."users"."password"
	, "public"."users"."statusId"
 FROM "public"."users"
	WHERE "public"."users"."statusId" IN (1,2);CREATE OR REPLACE VIEW "getVfsFiles" AS
SELECT "public"."vfsFiles"."fileId"
	, "public"."vfsFiles"."folderId"
	, "public"."vfsFiles"."title"
	, "public"."vfsFiles"."path"
	, "public"."vfsFiles"."params"
	, "public"."vfsFiles"."isFavorite"
	, "public"."vfsFiles"."mimeType"
	, "public"."vfsFiles"."fileSize"
	, "public"."vfsFiles"."fileExists"
	, "public"."vfsFiles"."statusId"
	, "public"."vfsFiles"."createdAt"
	, "folder"."folderId" AS "folder.folderId"
	, "folder"."parentFolderId" AS "folder.parentFolderId"
	, "folder"."title" AS "folder.title"
	, "folder"."isFavorite" AS "folder.isFavorite"
	, "folder"."createdAt" AS "folder.createdAt"
	, "folder"."statusId" AS "folder.statusId"
 FROM "public"."vfsFiles"
	INNER JOIN "public"."vfsFolders" "folder" ON
		"folder"."folderId" = "public"."vfsFiles"."folderId"
	WHERE "public"."vfsFiles"."statusId" IN (1,2)
ORDER BY "createdAt" DESC;
	
CREATE OR REPLACE VIEW "getVfsFolders" AS
SELECT ft."objectId"
    , ft."parentId"
    , ft."path"
    , ft."rKey"
    , ft."lKey"   
    , COALESCE( nlevel(ft."path" ), 0 ) as "level"
	,"public"."vfsFolders"."folderId"
	, "public"."vfsFolders"."parentFolderId"
	, "public"."vfsFolders"."title"
	, "public"."vfsFolders"."isFavorite"
	, "public"."vfsFolders"."createdAt"
	, "public"."vfsFolders"."statusId"
	, "parentFolder"."folderId" AS "parentFolder.folderId"
	, "parentFolder"."parentFolderId" AS "parentFolder.parentFolderId"
	, "parentFolder"."title" AS "parentFolder.title"
	, "parentFolder"."isFavorite" AS "parentFolder.isFavorite"
	, "parentFolder"."createdAt" AS "parentFolder.createdAt"
	, "parentFolder"."statusId" AS "parentFolder.statusId"
 FROM "public"."vfsFolders"
	LEFT JOIN "vfsFoldersTree" ft ON
        "vfsFolders"."folderId" = ft."objectId"
	LEFT JOIN "public"."vfsFolders" "parentFolder" ON
		"parentFolder"."folderId" = "public"."vfsFolders"."parentFolderId"
	WHERE "public"."vfsFolders"."statusId" IN (1,2)
	ORDER BY "level" ASC, "title" ASC;CREATE OR REPLACE VIEW "getSourceFeeds" AS
SELECT "public"."sourceFeeds"."sourceFeedId"
	, "public"."sourceFeeds"."title"
	, "public"."sourceFeeds"."externalId"
	, "public"."sourceFeeds"."useFullExport"
	, "public"."sourceFeeds"."processed"
	, "public"."sourceFeeds"."statusId"
 FROM "public"."sourceFeeds"
	WHERE "public"."sourceFeeds"."statusId" != 3
ORDER BY "title";
	
CREATE OR REPLACE VIEW "getTargetFeeds" AS
SELECT "public"."targetFeeds"."targetFeedId"
	, "public"."targetFeeds"."title"
	, "public"."targetFeeds"."externalId"
	, "public"."targetFeeds"."publisherId"
	, "public"."targetFeeds"."statusId"
	, "publisher"."publisherId" AS "publisher.publisherId"
	, "publisher"."name" AS "publisher.name"
	, "publisher"."vk_id" AS "publisher.vk_id"
	, "publisher"."vk_app" AS "publisher.vk_app"
	, "publisher"."vk_token" AS "publisher.vk_token"
	, "publisher"."vk_seckey" AS "publisher.vk_seckey"
	, "publisher"."statusId" AS "publisher.statusId"
 FROM "public"."targetFeeds"
	INNER JOIN "public"."publishers" "publisher" ON
		"publisher"."publisherId" = "public"."targetFeeds"."publisherId"
	WHERE "public"."targetFeeds"."statusId" != 3;
	
CREATE OR REPLACE VIEW "getArticles" AS
SELECT "public"."articles"."articleId"
	, "public"."articles"."importedAt"
	, "public"."articles"."createdAt"
	, "public"."articles"."externalId"
	, "public"."articles"."sourceFeedId"
	, "public"."articles"."statusId"
 FROM "public"."articles"
	WHERE "public"."articles"."statusId" != 3
ORDER BY "createdAt" DESC, "articleId" DESC;

CREATE OR REPLACE VIEW "getArticleQueues" AS
SELECT "public"."articleQueues"."articleQueueId"
	, "public"."articleQueues"."startDate"
	, "public"."articleQueues"."endDate"
	, "public"."articleQueues"."createdAt"
	, "public"."articleQueues"."sentAt"
	, "public"."articleQueues"."articleId"
	, "public"."articleQueues"."targetFeedId"
	, "public"."articleQueues"."statusId"
 FROM "public"."articleQueues"
	WHERE "public"."articleQueues"."statusId" != 3
ORDER BY "createdAt" DESC, "articleQueueId" DESC;

CREATE OR REPLACE VIEW "getArticleRecords" AS
SELECT "public"."articleRecords"."articleRecordId"
	, "public"."articleRecords"."content"
	, "public"."articleRecords"."likes"
	, "public"."articleRecords"."photos"
	, "public"."articleRecords"."articleId"
	, "public"."articleRecords"."articleQueueId"
 FROM "public"."articleRecords";
 
CREATE OR REPLACE VIEW "getPublishers" AS
SELECT "public"."publishers"."publisherId"
	, "public"."publishers"."name"
	, "public"."publishers"."vk_id"
	, "public"."publishers"."vk_app"
	, "public"."publishers"."vk_token"
	, "public"."publishers"."vk_seckey"
	, "public"."publishers"."statusId"
 FROM "public"."publishers"
	WHERE "public"."publishers"."statusId" != 3
ORDER BY "publisherId";CREATE OR REPLACE VIEW "getSiteParams" AS
SELECT "public"."siteParams"."siteParamId"
	, "public"."siteParams"."alias"
	, "public"."siteParams"."value"
	, "public"."siteParams"."description"
	, "public"."siteParams"."statusId"
 FROM "public"."siteParams"
	WHERE "public"."siteParams"."statusId" IN (1,2);

CREATE OR REPLACE VIEW "getMetaDetails" AS
SELECT "public"."metaDetails"."metaDetailId"
	, "public"."metaDetails"."url"
	, "public"."metaDetails"."pageTitle"
	, "public"."metaDetails"."metaKeywords"
	, "public"."metaDetails"."metaDescription"
	, "public"."metaDetails"."alt"
	, "public"."metaDetails"."isInheritable"
	, "public"."metaDetails"."statusId"
 FROM "public"."metaDetails"
	WHERE "public"."metaDetails"."statusId" IN (1,2)
ORDER BY "url";

CREATE OR REPLACE VIEW "getStaticPages" AS
SELECT "public"."staticPages"."staticPageId"
	, "public"."staticPages"."title"
	, "public"."staticPages"."url"
	, "public"."staticPages"."content"
	, "public"."staticPages"."pageTitle"
	, "public"."staticPages"."metaKeywords"
	, "public"."staticPages"."metaDescription"
	, "public"."staticPages"."orderNumber"
	, "public"."staticPages"."parentStaticPageId"
	, "public"."staticPages"."statusId"
	, "parentStaticPage"."staticPageId" AS "parentStaticPage.staticPageId"
	, "parentStaticPage"."title" AS "parentStaticPage.title"
	, "parentStaticPage"."url" AS "parentStaticPage.url"		
	, "parentStaticPage"."parentStaticPageId" AS "parentStaticPage.parentStaticPageId"
 FROM "public"."staticPages"
	LEFT JOIN "public"."staticPages" "parentStaticPage" ON
		"parentStaticPage"."staticPageId" = "public"."staticPages"."parentStaticPageId"
	WHERE "public"."staticPages"."statusId" IN (1,2)
ORDER BY "orderNumber", "url";

CREATE OR REPLACE VIEW "getNavigationTypes" AS
SELECT "public"."navigationTypes"."navigationTypeId"
	, "public"."navigationTypes"."title"
	, "public"."navigationTypes"."alias"
	, "public"."navigationTypes"."statusId"
 FROM "public"."navigationTypes"
	WHERE "public"."navigationTypes"."statusId" IN (1,2)
ORDER BY "alias";

CREATE OR REPLACE VIEW "getNavigations" AS
SELECT "public"."navigations"."navigationId"
	, "public"."navigations"."navigationTypeId"
	, "public"."navigations"."title"
	, "public"."navigations"."orderNumber"
	, "public"."navigations"."staticPageId"
	, "public"."navigations"."url"
	, "public"."navigations"."statusId"
	, "navigationType"."navigationTypeId" AS "navigationType.navigationTypeId"
	, "navigationType"."title" AS "navigationType.title"
	, "navigationType"."alias" AS "navigationType.alias"
	, "staticPage"."staticPageId" AS "staticPage.staticPageId"
	, "staticPage"."title" AS "staticPage.title"
	, "staticPage"."url" AS "staticPage.url"
	, "staticPage"."parentStaticPageId" AS "staticPage.parentStaticPageId"
 FROM "public"."navigations"
	INNER JOIN "public"."navigationTypes" "navigationType" ON
		"navigationType"."navigationTypeId" = "public"."navigations"."navigationTypeId"
	LEFT JOIN "public"."staticPages" "staticPage" ON
		"staticPage"."staticPageId" = "public"."navigations"."staticPageId"
	WHERE "public"."navigations"."statusId" IN (1,2)
ORDER BY "navigationType"."alias", "orderNumber";

CREATE OR REPLACE VIEW "getAuditEventTypes" AS
SELECT "public"."auditEventTypes"."auditEventTypeId"
	, "public"."auditEventTypes"."title"
	, "public"."auditEventTypes"."alias"
 FROM "public"."auditEventTypes"
ORDER BY "auditEventTypeId";

CREATE OR REPLACE VIEW "getAuditEvents" AS
SELECT "public"."auditEvents"."auditEventId"
	, "public"."auditEvents"."object"
	, "public"."auditEvents"."objectId"
	, "public"."auditEvents"."message"
	, "public"."auditEvents"."createdAt"
	, "public"."auditEvents"."auditEventTypeId"
	, "auditEventType"."auditEventTypeId" AS "auditEventType.auditEventTypeId"
	, "auditEventType"."title" AS "auditEventType.title"
	, "auditEventType"."alias" AS "auditEventType.alias"
 FROM "public"."auditEvents"
	INNER JOIN "public"."auditEventTypes" "auditEventType" ON
		"auditEventType"."auditEventTypeId" = "public"."auditEvents"."auditEventTypeId"
ORDER BY "createdAt" DESC;INSERT INTO "statuses" ( "statusId", "title", "alias" ) VALUES ( 1, 'Опубликован', 'enabled' );
INSERT INTO "statuses" ( "statusId", "title", "alias" ) VALUES ( 2, 'Не опубликован', 'disabled' );
INSERT INTO "statuses" ( "statusId", "title", "alias" ) VALUES ( 3, 'Удален', 'deleted' );
INSERT INTO "statuses" ( "statusId", "title", "alias" ) VALUES ( 4, 'Обрабатывается', 'queued' );
INSERT INTO "statuses" ( "statusId", "title", "alias" ) VALUES ( 5, 'Отправлен', 'finished' );

INSERT INTO "users" ( "login", "password", "statusId" ) VALUES ( 'admin', md5( 'saltedp@$$-' ||  md5( 'saltedp@$$-' ||  'admin' )), 1 );

INSERT INTO "vfsFolders" ("title", "statusId") 			VALUES ( 'root', 1 ); 
INSERT INTO "vfsFoldersTree" 							VALUES ( 1, NULL, '1', NULL, NULL );

INSERT INTO "public"."auditEventTypes" ("title", "alias")
VALUES ('Ошибки импорта', 'importErrors');
INSERT INTO "public"."auditEventTypes" ("title", "alias")
VALUES ('Ошибки экспорта', 'exportErrors');