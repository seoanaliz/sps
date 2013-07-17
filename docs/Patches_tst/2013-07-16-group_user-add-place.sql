alter table group_user add column place integer;
CREATE OR REPLACE VIEW "getGroup_user" AS 
 SELECT 
	  group_user."groupId"
	, group_user."vkId"
	, group_user."sourceType"
	, group_user."place"
   FROM group_user;