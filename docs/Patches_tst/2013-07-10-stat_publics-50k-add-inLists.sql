alter table "stat_publics_50k" add column "inLists" boolean default FALSE;

CREATE OR REPLACE VIEW get_publics AS
 SELECT stat_publics_50k.vk_id,
        stat_publics_50k.ava,
        stat_publics_50k.name,
        stat_publics_50k.short_name,
        stat_publics_50k.diff_abs,
        stat_publics_50k.diff_rel,
        stat_publics_50k.quantity,
        stat_publics_50k.sh_in_main,
        stat_publics_50k.diff_abs_week,
        stat_publics_50k.diff_abs_month,
        stat_publics_50k.diff_rel_week,
        stat_publics_50k.diff_rel_month,
        stat_publics_50k.is_page,
        stat_publics_50k.visitors,
        stat_publics_50k.in_search,
        stat_publics_50k.closed,
        stat_publics_50k.active,
        stat_publics_50k.visitors_week,
        stat_publics_50k.visitors_month,
        stat_publics_50k.updated_at,
        stat_publics_50k.vk_public_id,
        stat_publics_50k.viewers,
        stat_publics_50k.viewers_week,
        stat_publics_50k.viewers_month,
        stat_publics_50k."inLists"
   FROM stat_publics_50k;
