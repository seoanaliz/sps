/*
Created		16.08.2008
Modified		09.08.2012
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
	"path" Varchar(255),
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
	"rate" Integer NOT NULL Default 0,
	"sourceFeedId" Integer NOT NULL,
	"targetFeedId" Integer,
	"authorId" Integer,
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
	"type" Varchar(10) NOT NULL Default 'content',
	"author" Varchar(100),
	"externalId" Varchar(100),
	"externalLikes" Integer,
	"externalRetweets" Integer,
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
	"link" Varchar(500),
	"rate" Integer,
	"retweet" Text,
	"video" Text,
	"music" Text,
	"map" Varchar(500),
	"poll" Varchar(500),
	"text_links" Text,
	"doc" Varchar(500),
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
	"targetFeedIds" Text,
	"type" Varchar(100) NOT NULL Default 'source',
	"statusId" Integer NOT NULL,
 primary key ("sourceFeedId")
) Without Oids;


Create table "targetFeeds"
(
	"targetFeedId" Serial NOT NULL,
	"title" Varchar(500) NOT NULL,
	"externalId" Varchar(100) NOT NULL,
	"startTime" Time NOT NULL Default '09:00:00',
	"period" Integer NOT NULL Default 60,
	"vkIds" Text,
	"type" Varchar(10) NOT NULL Default 'vk',
	"params" Text,
	"publisherId" Integer,
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


Create table "targetFeedGrids"
(
	"targetFeedGridId" Serial NOT NULL,
	"startDate" Timestamp NOT NULL,
	"period" Integer NOT NULL,
	"targetFeedId" Integer NOT NULL,
 primary key ("targetFeedGridId")
) Without Oids;


Create table "targetFeedPublishers"
(
	"targetFeedId" Integer NOT NULL,
	"publisherId" Integer NOT NULL,
 primary key ("targetFeedId","publisherId")
) Without Oids;


Create table "gridLines"
(
	"gridLineId" Serial NOT NULL,
	"startDate" Date NOT NULL,
	"endDate" Date NOT NULL,
	"time" Time NOT NULL,
	"type" Varchar(10) NOT NULL,
	"targetFeedId" Integer NOT NULL,
 primary key ("gridLineId")
) Without Oids;


Create table "gridLineItems"
(
	"gridLineItemId" Serial NOT NULL,
	"date" Timestamp NOT NULL,
	"gridLineId" Integer NOT NULL,
 primary key ("gridLineItemId")
) Without Oids;


Create table "authors"
(
	"authorId" Serial NOT NULL,
	"vkId" Integer NOT NULL UNIQUE,
	"firstName" Varchar(1000),
	"lastName" Varchar(1000),
	"avatar" Varchar(1000),
	"targetFeedIds" Integer[],
	"statusId" Integer NOT NULL,
 primary key ("authorId")
) Without Oids;


Create table "editors"
(
	"editorId" Serial NOT NULL,
	"vkId" Integer NOT NULL UNIQUE,
	"firstName" Varchar(1000),
	"lastName" Varchar(1000),
	"avatar" Varchar(1000),
	"targetFeedIds" Integer[],
	"statusId" Integer NOT NULL,
 primary key ("editorId")
) Without Oids;


Create table "comments"
(
	"commentId" Serial NOT NULL,
	"text" Varchar(1000) NOT NULL,
	"createdAt" Timestamp NOT NULL Default now(),
	"articleId" Integer NOT NULL,
	"authorId" Integer,
	"editorId" Integer,
	"statusId" Integer NOT NULL,
 primary key ("commentId")
) Without Oids;


Create table "authorEvents"
(
	"articleId" Integer NOT NULL,
	"authorId" Integer NOT NULL,
	"commentIds" Integer[],
	"isSent" Boolean NOT NULL Default false,
 primary key ("articleId")
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
Create index "IX_FK_authorsStatusId_authors" on "authors" ("statusId");
Alter table "authors" add  foreign key ("statusId") references "statuses" ("statusId") on update restrict on delete restrict;
Create index "IX_FK_editorsStatusId_editors" on "editors" ("statusId");
Alter table "editors" add  foreign key ("statusId") references "statuses" ("statusId") on update restrict on delete restrict;
Create index "IX_FK_commentsStatusId_comments" on "comments" ("statusId");
Alter table "comments" add  foreign key ("statusId") references "statuses" ("statusId") on update restrict on delete restrict;
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
Create index "IX_FK_commentsArticleId_comments" on "comments" ("articleId");
Alter table "comments" add  foreign key ("articleId") references "articles" ("articleId") on update restrict on delete restrict;
Create index "IX_FK_authorEventsArticleId_authorEvents" on "authorEvents" ("articleId");
Alter table "authorEvents" add  foreign key ("articleId") references "articles" ("articleId") on update restrict on delete restrict;
Create index "IX_FK_articleRecordsArticleQueueId_articleRecords" on "articleRecords" ("articleQueueId");
Alter table "articleRecords" add  foreign key ("articleQueueId") references "articleQueues" ("articleQueueId") on update restrict on delete restrict;
Create index "IX_FK_articleQueuesTargetFeedId_articleQueues" on "articleQueues" ("targetFeedId");
Alter table "articleQueues" add  foreign key ("targetFeedId") references "targetFeeds" ("targetFeedId") on update restrict on delete restrict;
Create index "IX_FK_targetFeedGridsTargetFeedId_targetFeedGrids" on "targetFeedGrids" ("targetFeedId");
Alter table "targetFeedGrids" add  foreign key ("targetFeedId") references "targetFeeds" ("targetFeedId") on update restrict on delete restrict;
Create index "IX_FK_targetFeedPublishersTagetFeedId_targetFeedPublishers" on "targetFeedPublishers" ("targetFeedId");
Alter table "targetFeedPublishers" add  foreign key ("targetFeedId") references "targetFeeds" ("targetFeedId") on update restrict on delete restrict;
Create index "IX_FK_gridLinesTargetFeedId_gridLines" on "gridLines" ("targetFeedId");
Alter table "gridLines" add  foreign key ("targetFeedId") references "targetFeeds" ("targetFeedId") on update restrict on delete restrict;
Create index "IX_FK_articlesTargetFeedId_articles" on "articles" ("targetFeedId");
Alter table "articles" add  foreign key ("targetFeedId") references "targetFeeds" ("targetFeedId") on update restrict on delete restrict;
Create index "IX_FK_targetFeeds_publisherId_targetFeeds" on "targetFeeds" ("publisherId");
Alter table "targetFeeds" add  foreign key ("publisherId") references "publishers" ("publisherId") on update restrict on delete restrict;
Create index "IX_FK_targetFeedPublishersPublisherId_targetFeedPublishers" on "targetFeedPublishers" ("publisherId");
Alter table "targetFeedPublishers" add  foreign key ("publisherId") references "publishers" ("publisherId") on update restrict on delete restrict;
Create index "IX_FK_auditEventsAuditEventTypeId_auditEvents" on "auditEvents" ("auditEventTypeId");
Alter table "auditEvents" add  foreign key ("auditEventTypeId") references "auditEventTypes" ("auditEventTypeId") on update restrict on delete restrict;
Create index "IX_FK_gridLineItemsGridLineId_gridLineItems" on "gridLineItems" ("gridLineId");
Alter table "gridLineItems" add  foreign key ("gridLineId") references "gridLines" ("gridLineId") on update restrict on delete restrict;
Create index "IX_FK_articlesAuthorId_articles" on "articles" ("authorId");
Alter table "articles" add  foreign key ("authorId") references "authors" ("authorId") on update restrict on delete restrict;
Create index "IX_FK_commentsAuthorId_comments" on "comments" ("authorId");
Alter table "comments" add  foreign key ("authorId") references "authors" ("authorId") on update restrict on delete restrict;
Create index "IX_FK_authorEventsAuthorId_authorEvents" on "authorEvents" ("authorId");
Alter table "authorEvents" add  foreign key ("authorId") references "authors" ("authorId") on update restrict on delete restrict;
Create index "IX_FK_commentsEditorId_comments" on "comments" ("editorId");
Alter table "comments" add  foreign key ("editorId") references "editors" ("editorId") on update restrict on delete restrict;


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


