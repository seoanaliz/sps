INSERT INTO "statuses" ( "statusId", "title", "alias" ) VALUES ( 1, 'Опубликован', 'enabled' );
INSERT INTO "statuses" ( "statusId", "title", "alias" ) VALUES ( 2, 'Не опубликован', 'disabled' );
INSERT INTO "statuses" ( "statusId", "title", "alias" ) VALUES ( 3, 'Удален', 'deleted' );

INSERT INTO "users" ( "login", "password", "statusId" ) VALUES ( 'admin', md5( 'saltedp@$$-' ||  md5( 'saltedp@$$-' ||  'admin' )), 1 );

INSERT INTO "vfsFolders" ("title", "statusId") 			VALUES ( 'root', 1 ); 
INSERT INTO "vfsFoldersTree" 							VALUES ( 1, NULL, '1', NULL, NULL );