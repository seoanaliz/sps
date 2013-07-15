alter table group_entry add column "addedBy" character varying;

CREATE OR REPLACE VIEW "getGroup_entry" AS
SELECT
    group_entry."groupId",
    group_entry."entryId",
    group_entry."sourceType",
    group_entry."addedBy"
FROM group_entry;
