alter table "groups" add column slug character varying;
CREATE OR REPLACE VIEW get_groups AS
SELECT groups.group_id,
    groups.name,
    groups.general,
    groups.type,
    groups.users_ids,
    groups.created_by,
    groups.status,
    groups.source,
    groups.slug
FROM groups
WHERE groups.status <> 2
ORDER BY groups.group_id DESC;