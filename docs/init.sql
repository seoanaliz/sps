INSERT INTO "statuses" ( "statusId", "title", "alias" ) VALUES ( 1, 'Опубликован', 'enabled' );
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
INSERT INTO "public"."auditEventTypes" ("title", "alias")
VALUES ('Удаление поста', 'articleDelete');
INSERT INTO "public"."auditEventTypes" ("title", "alias")
VALUES ('Планирование поста', 'articleQueue');
INSERT INTO "public"."auditEventTypes" ("title", "alias")
VALUES ('Изменение времени сетки', 'gridLineTime');