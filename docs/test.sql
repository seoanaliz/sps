--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

--
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner:
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner:
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


--
-- Name: intarray; Type: EXTENSION; Schema: -; Owner:
--

CREATE EXTENSION IF NOT EXISTS intarray WITH SCHEMA public;


--
-- Name: EXTENSION intarray; Type: COMMENT; Schema: -; Owner:
--

COMMENT ON EXTENSION intarray IS 'functions, operators, and index support for 1-D arrays of integers';


SET search_path = public, pg_catalog;

--
-- Name: find_public_place(integer); Type: FUNCTION; Schema: public; Owner: sps
--

CREATE FUNCTION find_public_place(id integer) RETURNS integer
    LANGUAGE plpgsql
    AS $$
            DECLARE
                i INT := 0;
                curr INT;
            BEGIN
                FOR curr IN select vk_id from stat_publics_50k WHERE NOT is_page ORDER BY quantity DESC
                LOOP
                i := i+1;
                    IF (curr = id ) THEN return i; END IF;
                END LOOP;
                RETURN 0;
            END
            $$;


ALTER FUNCTION public.find_public_place(id integer) OWNER TO sps;

--
-- Name: if(boolean, anyelement, anyelement); Type: FUNCTION; Schema: public; Owner: sps
--

CREATE FUNCTION if(boolean, anyelement, anyelement) RETURNS anyelement
    LANGUAGE sql IMMUTABLE
    AS $_$
SELECT CASE WHEN $1 THEN $2 ELSE $3 END
$_$;


ALTER FUNCTION public.if(boolean, anyelement, anyelement) OWNER TO sps;

--
-- Name: set_state(integer, character varying, boolean); Type: FUNCTION; Schema: public; Owner: sps
--

CREATE FUNCTION set_state(id integer, column_name character varying, state boolean) RETURNS boolean
    LANGUAGE plpgsql
    AS $_$
                DECLARE
                old_value boolean    := 0;
                curr_state boolean := false;
                BEGIN
                    execute 'SELECT '|| column_name ||' FROM stat_publics_50k WHERE vk_id='||$1 INTO old_value;
                    IF $3=old_value THEN
                        return false;
                    ELSE
                        execute 'INSERT INTO stat_public_audit( public_id, '||$2||', changed_at,act) VALUES ( '||$1||','||$3||',CURRENT_TIMESTAMP, '''||$2||''' )';
                        execute 'UPDATE stat_publics_50k SET '||$2||' = '||$3||' WHERE  vk_id='||$1;
                        return true;
                    END IF;
                END
                $_$;


ALTER FUNCTION public.set_state(id integer, column_name character varying, state boolean) OWNER TO sps;

--
-- Name: update_public_info(integer, character varying, character varying); Type: FUNCTION; Schema: public; Owner: sps
--

CREATE FUNCTION update_public_info(id integer, p_name character varying, ava character varying) RETURNS character varying
    LANGUAGE plpgsql
    AS $_$
                    DECLARE
                        curr_name CHARACTER VARYING := '_';
                    BEGIN
                        SELECT name INTO curr_name FROM stat_publics_50k WHERE vk_id=$1;
                        IF( curr_name=p_name )
                        THEN
                            curr_name := '';
                        ELSE
                            INSERT INTO stat_public_audit(public_id,name,changed_at,act) VALUES ($1,curr_name,CURRENT_TIMESTAMP,'name');
                        END IF;
                        UPDATE stat_publics_50k SET name = $2, ava=$3 WHERE vk_id=$1;

                        RETURN curr_name;
                    END
                    $_$;


ALTER FUNCTION public.update_public_info(id integer, p_name character varying, ava character varying) OWNER TO sps;

--
-- Name: update_public_info(integer, character varying, character varying, boolean); Type: FUNCTION; Schema: public; Owner: sps
--

CREATE FUNCTION update_public_info(id integer, p_name character varying, ava character varying, page boolean) RETURNS character varying
    LANGUAGE plpgsql
    AS $_$
                    DECLARE
                        curr_name CHARACTER VARYING := '_';
                    BEGIN
                        SELECT name INTO curr_name FROM stat_publics_50k WHERE vk_id=$1;
                        IF( curr_name=p_name )
                        THEN
                            curr_name := '';
                        ELSE
                            INSERT INTO stat_public_audit(public_id,name,changed_at,act) VALUES ($1,curr_name,CURRENT_TIMESTAMP,'name');
                        END IF;
                        UPDATE stat_publics_50k SET name = $2, ava=$3, is_page=$4 WHERE vk_id=$1;
                        RETURN curr_name;
                    END
                    $_$;


ALTER FUNCTION public.update_public_info(id integer, p_name character varying, ava character varying, page boolean) OWNER TO sps;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: oadmins_posts; Type: TABLE; Schema: public; Owner: sps; Tablespace:
--

CREATE TABLE oadmins_posts (
    id integer NOT NULL,
    vk_post_id character varying,
    author_id integer,
    post_time integer,
    complicate boolean,
    ad boolean DEFAULT false,
    likes integer,
    reposts integer,
    is_topic boolean,
    public_id integer,
    tweet_from character varying,
    rel_likes real,
    source character(1)
);


ALTER TABLE public.oadmins_posts OWNER TO sps;

--
-- Name: admin_posts_id_seq; Type: SEQUENCE; Schema: public; Owner: sps
--

CREATE SEQUENCE admin_posts_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.admin_posts_id_seq OWNER TO sps;

--
-- Name: admin_posts_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sps
--

ALTER SEQUENCE admin_posts_id_seq OWNED BY oadmins_posts.id;


--
-- Name: albums; Type: TABLE; Schema: public; Owner: sps; Tablespace:
--

CREATE TABLE albums (
    public_id integer NOT NULL,
    album_id integer NOT NULL,
    photos_quantity integer,
    likes_quantity integer,
    comments_quantity integer,
    name character varying,
    ava character varying,
    state integer
);


ALTER TABLE public.albums OWNER TO sps;

--
-- Name: albums_points; Type: TABLE; Schema: public; Owner: sps; Tablespace:
--

CREATE TABLE albums_points (
    album_id integer,
    public_id integer,
    likes_quantity integer,
    comments_quantity integer,
    photos_quantity integer,
    ts integer
);


ALTER TABLE public.albums_points OWNER TO sps;

--
-- Name: barter_events; Type: TABLE; Schema: public; Owner: sps; Tablespace:
--

CREATE TABLE barter_events (
    barter_event_id integer NOT NULL,
    barter_type integer NOT NULL,
    status integer NOT NULL,
    barter_public character varying NOT NULL,
    search_string character varying NOT NULL,
    start_search_at timestamp without time zone NOT NULL,
    stop_search_at timestamp without time zone NOT NULL,
    posted_at timestamp without time zone,
    deleted_at timestamp without time zone,
    barter_overlaps character varying,
    start_visitors integer,
    end_visitors integer,
    start_subscribers integer,
    end_subscribers integer,
    created_at timestamp without time zone,
    target_public character varying(255),
    post_id character varying,
    standard_mark boolean,
    groups_ids integer[],
    creator_id character varying,
    detected_at timestamp without time zone,
    init_users integer[],
    neater_subscribers integer
);


ALTER TABLE public.barter_events OWNER TO sps;

--
-- Name: barter_events_barter_event_id_seq; Type: SEQUENCE; Schema: public; Owner: sps
--

CREATE SEQUENCE barter_events_barter_event_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.barter_events_barter_event_id_seq OWNER TO sps;

--
-- Name: barter_events_barter_event_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sps
--

ALTER SEQUENCE barter_events_barter_event_id_seq OWNED BY barter_events.barter_event_id;


--
-- Name: barter_monitoring; Type: TABLE; Schema: public; Owner: sps; Tablespace:
--

CREATE TABLE barter_monitoring (
    public_id integer,
    post_id integer,
    "time" timestamp without time zone,
    text character varying,
    link character varying,
    type character varying,
    mentioned_public_id integer
);


ALTER TABLE public.barter_monitoring OWNER TO sps;

--
-- Name: newUserRequests; Type: TABLE; Schema: public; Owner: sps; Tablespace:
--

CREATE TABLE "newUserRequests" (
    "newUserRequestId" integer NOT NULL,
    "vkId" character varying,
    email character varying,
    "publicIds" integer[],
    "statusId" integer,
    "createdAt" timestamp without time zone
);


ALTER TABLE public."newUserRequests" OWNER TO sps;

--
-- Name: getNewUserRequests; Type: VIEW; Schema: public; Owner: sps
--

CREATE VIEW "getNewUserRequests" AS
    SELECT "newUserRequests"."newUserRequestId", "newUserRequests"."vkId", "newUserRequests".email, "newUserRequests"."publicIds", "newUserRequests"."statusId", "newUserRequests"."createdAt" FROM "newUserRequests" WHERE ("newUserRequests"."statusId" <> 3);


ALTER TABLE public."getNewUserRequests" OWNER TO sps;

--
-- Name: get_barter_events; Type: VIEW; Schema: public; Owner: sps
--

CREATE VIEW get_barter_events AS
    SELECT barter_events.barter_event_id, barter_events.barter_type, barter_events.status, barter_events.barter_public, barter_events.search_string, barter_events.start_search_at, barter_events.stop_search_at, barter_events.posted_at, barter_events.deleted_at, barter_events.barter_overlaps, barter_events.start_visitors, barter_events.end_visitors, barter_events.start_subscribers, barter_events.end_subscribers, barter_events.created_at, barter_events.target_public, barter_events.post_id, barter_events.standard_mark, barter_events.groups_ids, (barter_events.end_subscribers - barter_events.start_subscribers) AS subscribers, (barter_events.end_visitors - barter_events.start_visitors) AS visitors, barter_events.creator_id, barter_events.detected_at, barter_events.init_users, barter_events.neater_subscribers FROM barter_events ORDER BY barter_events.created_at DESC, barter_events.barter_event_id DESC;


ALTER TABLE public.get_barter_events OWNER TO sps;

--
-- Name: groups; Type: TABLE; Schema: public; Owner: sps; Tablespace:
--

CREATE TABLE groups (
    group_id integer NOT NULL,
    name character varying(255),
    general boolean,
    type smallint,
    users_ids integer[],
    created_by integer,
    status integer DEFAULT 1,
    source integer
);


ALTER TABLE public.groups OWNER TO sps;

--
-- Name: get_groups; Type: VIEW; Schema: public; Owner: sps
--

CREATE VIEW get_groups AS
    SELECT groups.group_id, groups.name, groups.general, groups.type, groups.users_ids, groups.created_by, groups.status, groups.source FROM groups ORDER BY groups.group_id DESC;


ALTER TABLE public.get_groups OWNER TO sps;

--
-- Name: stat_publics_50k; Type: TABLE; Schema: public; Owner: sps; Tablespace:
--

CREATE TABLE stat_publics_50k (
    vk_id integer NOT NULL,
    ava character(300),
    name character varying(300),
    short_name character varying(120),
    price integer,
    diff_abs integer,
    diff_rel real,
    quantity integer,
    sh_in_main boolean,
    diff_abs_week integer,
    diff_abs_month integer,
    diff_rel_week real,
    diff_rel_month real,
    is_page boolean,
    visitors integer,
    in_search boolean DEFAULT true,
    closed boolean,
    active boolean,
    visitors_week integer,
    visitors_month integer,
    updated_at date,
    vk_public_id integer NOT NULL,
    viewers integer,
    viewers_week integer,
    viewers_month integer
);


ALTER TABLE public.stat_publics_50k OWNER TO sps;

--
-- Name: get_publics; Type: VIEW; Schema: public; Owner: sps
--

CREATE VIEW get_publics AS
    SELECT stat_publics_50k.vk_id, stat_publics_50k.ava, stat_publics_50k.name, stat_publics_50k.short_name, stat_publics_50k.diff_abs, stat_publics_50k.diff_rel, stat_publics_50k.quantity, stat_publics_50k.sh_in_main, stat_publics_50k.diff_abs_week, stat_publics_50k.diff_abs_month, stat_publics_50k.diff_rel_week, stat_publics_50k.diff_rel_month, stat_publics_50k.is_page, stat_publics_50k.visitors, stat_publics_50k.in_search, stat_publics_50k.closed, stat_publics_50k.active, stat_publics_50k.visitors_week, stat_publics_50k.visitors_month, stat_publics_50k.updated_at FROM stat_publics_50k;


ALTER TABLE public.get_publics OWNER TO sps;

--
-- Name: stat_groups; Type: TABLE; Schema: public; Owner: sps; Tablespace:
--

CREATE TABLE stat_groups (
    group_id integer NOT NULL,
    name character varying,
    comments character varying,
    ava character varying,
    group_admin integer,
    type integer DEFAULT 1,
    general boolean
);


ALTER TABLE public.stat_groups OWNER TO sps;

--
-- Name: groups_group_id_seq; Type: SEQUENCE; Schema: public; Owner: sps
--

CREATE SEQUENCE groups_group_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.groups_group_id_seq OWNER TO sps;

--
-- Name: groups_group_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sps
--

ALTER SEQUENCE groups_group_id_seq OWNED BY stat_groups.group_id;


--
-- Name: groups_group_id_seq1; Type: SEQUENCE; Schema: public; Owner: sps
--

CREATE SEQUENCE groups_group_id_seq1
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.groups_group_id_seq1 OWNER TO sps;

--
-- Name: groups_group_id_seq1; Type: SEQUENCE OWNED BY; Schema: public; Owner: sps
--

ALTER SEQUENCE groups_group_id_seq1 OWNED BY groups.group_id;


--
-- Name: mes_activity_log; Type: TABLE; Schema: public; Owner: sps; Tablespace:
--

CREATE TABLE mes_activity_log (
    dialog_id integer,
    activity_time integer,
    queued boolean DEFAULT false
);


ALTER TABLE public.mes_activity_log OWNER TO sps;

--
-- Name: mes_dialog_statuses; Type: TABLE; Schema: public; Owner: sps; Tablespace:
--

CREATE TABLE mes_dialog_statuses (
    name character varying,
    id integer NOT NULL
);


ALTER TABLE public.mes_dialog_statuses OWNER TO sps;

--
-- Name: mes_dialog_statuses_id_seq; Type: SEQUENCE; Schema: public; Owner: sps
--

CREATE SEQUENCE mes_dialog_statuses_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.mes_dialog_statuses_id_seq OWNER TO sps;

--
-- Name: mes_dialog_statuses_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sps
--

ALTER SEQUENCE mes_dialog_statuses_id_seq OWNED BY mes_dialog_statuses.id;


--
-- Name: mes_dialog_templates; Type: TABLE; Schema: public; Owner: sps; Tablespace:
--

CREATE TABLE mes_dialog_templates (
    text character varying,
    groups integer[],
    id integer NOT NULL,
    created_at integer,
    user_id integer
);


ALTER TABLE public.mes_dialog_templates OWNER TO sps;

--
-- Name: mes_dialog_templates_id_seq; Type: SEQUENCE; Schema: public; Owner: sps
--

CREATE SEQUENCE mes_dialog_templates_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.mes_dialog_templates_id_seq OWNER TO sps;

--
-- Name: mes_dialog_templates_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sps
--

ALTER SEQUENCE mes_dialog_templates_id_seq OWNED BY mes_dialog_templates.id;


--
-- Name: mes_dialogs; Type: TABLE; Schema: public; Owner: sps; Tablespace:
--

CREATE TABLE mes_dialogs (
    id integer NOT NULL,
    user_id integer,
    rec_id integer,
    status character varying,
    last_update integer,
    state smallint,
    text_id integer
);


ALTER TABLE public.mes_dialogs OWNER TO sps;

--
-- Name: mes_dialogs_groups; Type: TABLE; Schema: public; Owner: sps; Tablespace:
--

CREATE TABLE mes_dialogs_groups (
    group_id integer NOT NULL,
    name character varying,
    general boolean,
    type integer DEFAULT 0
);


ALTER TABLE public.mes_dialogs_groups OWNER TO sps;

--
-- Name: mes_dialogs_groups_id_seq; Type: SEQUENCE; Schema: public; Owner: sps
--

CREATE SEQUENCE mes_dialogs_groups_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.mes_dialogs_groups_id_seq OWNER TO sps;

--
-- Name: mes_dialogs_groups_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sps
--

ALTER SEQUENCE mes_dialogs_groups_id_seq OWNED BY mes_dialogs_groups.group_id;


--
-- Name: mes_dialogs_id_seq; Type: SEQUENCE; Schema: public; Owner: sps
--

CREATE SEQUENCE mes_dialogs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.mes_dialogs_id_seq OWNER TO sps;

--
-- Name: mes_dialogs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sps
--

ALTER SEQUENCE mes_dialogs_id_seq OWNED BY mes_dialogs.id;


--
-- Name: mes_group_dialog_relation; Type: TABLE; Schema: public; Owner: sps; Tablespace:
--

CREATE TABLE mes_group_dialog_relation (
    group_id integer NOT NULL,
    dialog_id integer NOT NULL
);


ALTER TABLE public.mes_group_dialog_relation OWNER TO sps;

--
-- Name: mes_group_user_relation; Type: TABLE; Schema: public; Owner: sps; Tablespace:
--

CREATE TABLE mes_group_user_relation (
    group_id integer NOT NULL,
    user_id integer NOT NULL,
    read_mark boolean DEFAULT true,
    seq_number integer DEFAULT 0,
    unread_dialogs_list integer[] DEFAULT '{0}'::integer[],
    last_clear_time integer DEFAULT 0
);


ALTER TABLE public.mes_group_user_relation OWNER TO sps;

--
-- Name: mes_queue; Type: TABLE; Schema: public; Owner: sps; Tablespace:
--

CREATE TABLE mes_queue (
    id integer NOT NULL,
    created_time integer,
    sent_time integer DEFAULT 0,
    user_id integer,
    sent boolean DEFAULT false,
    dialog_id integer
);


ALTER TABLE public.mes_queue OWNER TO sps;

--
-- Name: mes_queue_id_seq; Type: SEQUENCE; Schema: public; Owner: sps
--

CREATE SEQUENCE mes_queue_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.mes_queue_id_seq OWNER TO sps;

--
-- Name: mes_queue_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sps
--

ALTER SEQUENCE mes_queue_id_seq OWNED BY mes_queue.id;


--
-- Name: mes_texts; Type: TABLE; Schema: public; Owner: sps; Tablespace:
--

CREATE TABLE mes_texts (
    text character varying,
    "out" boolean,
    read boolean,
    mid integer,
    id integer NOT NULL
);


ALTER TABLE public.mes_texts OWNER TO sps;

--
-- Name: mes_texts_id_seq; Type: SEQUENCE; Schema: public; Owner: sps
--

CREATE SEQUENCE mes_texts_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.mes_texts_id_seq OWNER TO sps;

--
-- Name: mes_texts_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sps
--

ALTER SEQUENCE mes_texts_id_seq OWNED BY mes_texts.id;


--
-- Name: newUserRequests_newUserRequestId_seq; Type: SEQUENCE; Schema: public; Owner: sps
--

CREATE SEQUENCE "newUserRequests_newUserRequestId_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."newUserRequests_newUserRequestId_seq" OWNER TO sps;

--
-- Name: newUserRequests_newUserRequestId_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sps
--

ALTER SEQUENCE "newUserRequests_newUserRequestId_seq" OWNED BY "newUserRequests"."newUserRequestId";


--
-- Name: oadmins; Type: TABLE; Schema: public; Owner: sps; Tablespace:
--

CREATE TABLE oadmins (
    vk_id integer NOT NULL,
    name character varying,
    ad boolean DEFAULT false,
    ava character varying
);


ALTER TABLE public.oadmins OWNER TO sps;

--
-- Name: oadmins_conf; Type: TABLE; Schema: public; Owner: sps; Tablespace:
--

CREATE TABLE oadmins_conf (
    price integer,
    complicate real,
    reposts real,
    rel_mark real,
    overposts real
);


ALTER TABLE public.oadmins_conf OWNER TO sps;

--
-- Name: oadmins_public_points; Type: TABLE; Schema: public; Owner: sps; Tablespace:
--

CREATE TABLE oadmins_public_points (
    public_sb_id integer NOT NULL,
    ts integer NOT NULL,
    likes integer,
    reposts integer,
    comments integer
);


ALTER TABLE public.oadmins_public_points OWNER TO sps;

--
-- Name: old_50k_points; Type: TABLE; Schema: public; Owner: sps; Tablespace:
--

CREATE TABLE old_50k_points (
    id integer NOT NULL,
    "time" integer NOT NULL,
    quantity integer
);


ALTER TABLE public.old_50k_points OWNER TO sps;

--
-- Name: our_publs_points; Type: TABLE; Schema: public; Owner: sps; Tablespace:
--

CREATE TABLE our_publs_points (
    id integer,
    "time" integer,
    quantity integer
);


ALTER TABLE public.our_publs_points OWNER TO sps;

--
-- Name: points; Type: TABLE; Schema: public; Owner: sps; Tablespace:
--

CREATE TABLE points (
    publ_inner_id integer,
    comments integer,
    reposts integer,
    likes integer,
    date_point integer,
    unic_likes integer,
    unic_reposts integer,
    unic_comms integer
);


ALTER TABLE public.points OWNER TO sps;

--
-- Name: posts_for_likes; Type: TABLE; Schema: public; Owner: sps; Tablespace:
--

CREATE TABLE posts_for_likes (
    vk_id character varying,
    time_st integer,
    comments integer,
    likes integer,
    reposts integer
);


ALTER TABLE public.posts_for_likes OWNER TO sps;

--
-- Name: publ_rels_names; Type: TABLE; Schema: public; Owner: sps; Tablespace:
--

CREATE TABLE publ_rels_names (
    user_id integer NOT NULL,
    publ_id integer NOT NULL,
    selected_admin integer,
    group_id integer NOT NULL
);


ALTER TABLE public.publ_rels_names OWNER TO sps;

--
-- Name: publics; Type: TABLE; Schema: public; Owner: sps; Tablespace:
--

CREATE TABLE publics (
    id integer,
    name character varying,
    vk_id integer NOT NULL,
    population integer,
    check_time integer,
    "offset" integer,
    active smallint,
    in_search smallint
);


ALTER TABLE public.publics OWNER TO sps;

--
-- Name: serv_access_tokens; Type: TABLE; Schema: public; Owner: sps; Tablespace:
--

CREATE TABLE serv_access_tokens (
    user_id integer NOT NULL,
    access_token character varying NOT NULL,
    active boolean DEFAULT true,
    app_id integer
);


ALTER TABLE public.serv_access_tokens OWNER TO sps;

--
-- Name: serv_states; Type: TABLE; Schema: public; Owner: sps; Tablespace:
--

CREATE TABLE serv_states (
    serv_name character varying,
    status_id integer
);


ALTER TABLE public.serv_states OWNER TO sps;

--
-- Name: stat_admins; Type: TABLE; Schema: public; Owner: sps; Tablespace:
--

CREATE TABLE stat_admins (
    vk_id integer,
    role character varying,
    name character varying,
    ava character varying,
    status integer,
    publ_id integer,
    rank integer,
    comments character varying(128)
);


ALTER TABLE public.stat_admins OWNER TO sps;

--
-- Name: stat_group_public_relation; Type: TABLE; Schema: public; Owner: sps; Tablespace:
--

CREATE TABLE stat_group_public_relation (
    group_id integer NOT NULL,
    public_id integer NOT NULL,
    main_admin integer,
    listed_by integer
);


ALTER TABLE public.stat_group_public_relation OWNER TO sps;

--
-- Name: TABLE stat_group_public_relation; Type: COMMENT; Schema: public; Owner: sps
--

COMMENT ON TABLE stat_group_public_relation IS 'СЃРѕРґРµСЂР¶РёС‚ СѓРЅРёРєР°Р»СЊРЅС‹Рµ РїР°СЂС‹ "РїСѓР±Р»РёС‡РЅР°СЏ СЃС‚СЂР°РЅРёС†Р° - РіСЂСѓРїРїР°"';


--
-- Name: stat_group_user_relation; Type: TABLE; Schema: public; Owner: sps; Tablespace:
--

CREATE TABLE stat_group_user_relation (
    user_id integer NOT NULL,
    group_id integer NOT NULL,
    fave boolean DEFAULT false
);


ALTER TABLE public.stat_group_user_relation OWNER TO sps;

--
-- Name: TABLE stat_group_user_relation; Type: COMMENT; Schema: public; Owner: sps
--

COMMENT ON TABLE stat_group_user_relation IS 'РЎРѕРґРµСЂР¶РёС‚ СѓРЅРёРєР°Р»СЊРЅС‹Рµ РїР°СЂС‹ "РіСЂСѓРїРїР°(Р»РёСЃС‚) - РїРѕР»СЊР·РѕРІР°С‚РµР»СЊ"';


--
-- Name: stat_our_auditory; Type: TABLE; Schema: public; Owner: sps; Tablespace:
--

CREATE TABLE stat_our_auditory (
    point_date date,
    unique_users integer,
    all_users integer,
    type character varying
);


ALTER TABLE public.stat_our_auditory OWNER TO sps;

--
-- Name: stat_our_publics_vis_vie; Type: TABLE; Schema: public; Owner: sps; Tablespace:
--

CREATE TABLE stat_our_publics_vis_vie (
    public_id integer NOT NULL,
    date integer NOT NULL,
    views integer,
    visitors integer
);


ALTER TABLE public.stat_our_publics_vis_vie OWNER TO sps;

--
-- Name: stat_parser; Type: TABLE; Schema: public; Owner: sps; Tablespace:
--

CREATE TABLE stat_parser (
    current_public integer,
    max_public integer,
    reseted_at timestamp without time zone,
    tries integer
);


ALTER TABLE public.stat_parser OWNER TO sps;

--
-- Name: stat_public_audit; Type: TABLE; Schema: public; Owner: sps; Tablespace:
--

CREATE TABLE stat_public_audit (
    public_id integer,
    name character varying,
    active boolean,
    in_search boolean,
    act character varying,
    changed_at timestamp without time zone,
    closed boolean
);


ALTER TABLE public.stat_public_audit OWNER TO sps;

--
-- Name: stat_publics_50k_points; Type: TABLE; Schema: public; Owner: sps; Tablespace:
--

CREATE TABLE stat_publics_50k_points (
    id integer NOT NULL,
    "time" date NOT NULL,
    quantity integer,
    visitors integer,
    views integer,
    reach integer,
    "createdAt" timestamp without time zone
);


ALTER TABLE public.stat_publics_50k_points OWNER TO sps;

--
-- Name: stat_publics_50k_vk_public_id_seq; Type: SEQUENCE; Schema: public; Owner: sps
--

CREATE SEQUENCE stat_publics_50k_vk_public_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.stat_publics_50k_vk_public_id_seq OWNER TO sps;

--
-- Name: stat_publics_50k_vk_public_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sps
--

ALTER SEQUENCE stat_publics_50k_vk_public_id_seq OWNED BY stat_publics_50k.vk_public_id;


--
-- Name: stat_users; Type: TABLE; Schema: public; Owner: sps; Tablespace:
--

CREATE TABLE stat_users (
    user_id integer NOT NULL,
    name character varying,
    ava character varying,
    comments character varying,
    rank character varying,
    access_token text
);


ALTER TABLE public.stat_users OWNER TO sps;

--
-- Name: temp_users_ids_store; Type: TABLE; Schema: public; Owner: sps; Tablespace:
--

CREATE TABLE temp_users_ids_store (
    public_id integer NOT NULL,
    user_ids integer[],
    type character varying
);


ALTER TABLE public.temp_users_ids_store OWNER TO sps;

--
-- Name: barter_event_id; Type: DEFAULT; Schema: public; Owner: sps
--

ALTER TABLE ONLY barter_events ALTER COLUMN barter_event_id SET DEFAULT nextval('barter_events_barter_event_id_seq'::regclass);


--
-- Name: group_id; Type: DEFAULT; Schema: public; Owner: sps
--

ALTER TABLE ONLY groups ALTER COLUMN group_id SET DEFAULT nextval('groups_group_id_seq1'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: sps
--

ALTER TABLE ONLY mes_dialog_statuses ALTER COLUMN id SET DEFAULT nextval('mes_dialog_statuses_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: sps
--

ALTER TABLE ONLY mes_dialog_templates ALTER COLUMN id SET DEFAULT nextval('mes_dialog_templates_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: sps
--

ALTER TABLE ONLY mes_dialogs ALTER COLUMN id SET DEFAULT nextval('mes_dialogs_id_seq'::regclass);


--
-- Name: group_id; Type: DEFAULT; Schema: public; Owner: sps
--

ALTER TABLE ONLY mes_dialogs_groups ALTER COLUMN group_id SET DEFAULT nextval('mes_dialogs_groups_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: sps
--

ALTER TABLE ONLY mes_queue ALTER COLUMN id SET DEFAULT nextval('mes_queue_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: sps
--

ALTER TABLE ONLY mes_texts ALTER COLUMN id SET DEFAULT nextval('mes_texts_id_seq'::regclass);


--
-- Name: newUserRequestId; Type: DEFAULT; Schema: public; Owner: sps
--

ALTER TABLE ONLY "newUserRequests" ALTER COLUMN "newUserRequestId" SET DEFAULT nextval('"newUserRequests_newUserRequestId_seq"'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: sps
--

ALTER TABLE ONLY oadmins_posts ALTER COLUMN id SET DEFAULT nextval('admin_posts_id_seq'::regclass);


--
-- Name: group_id; Type: DEFAULT; Schema: public; Owner: sps
--

ALTER TABLE ONLY stat_groups ALTER COLUMN group_id SET DEFAULT nextval('groups_group_id_seq'::regclass);


--
-- Name: vk_public_id; Type: DEFAULT; Schema: public; Owner: sps
--

ALTER TABLE ONLY stat_publics_50k ALTER COLUMN vk_public_id SET DEFAULT nextval('stat_publics_50k_vk_public_id_seq'::regclass);


--
-- Name: albums_pkey; Type: CONSTRAINT; Schema: public; Owner: sps; Tablespace:
--

ALTER TABLE ONLY albums
    ADD CONSTRAINT albums_pkey PRIMARY KEY (public_id, album_id);


--
-- Name: albums_points_album_id_public_id_ts_key; Type: CONSTRAINT; Schema: public; Owner: sps; Tablespace:
--

ALTER TABLE ONLY albums_points
    ADD CONSTRAINT albums_points_album_id_public_id_ts_key UNIQUE (album_id, public_id, ts);


--
-- Name: barter_events_pkey; Type: CONSTRAINT; Schema: public; Owner: sps; Tablespace:
--

ALTER TABLE ONLY barter_events
    ADD CONSTRAINT barter_events_pkey PRIMARY KEY (barter_event_id);


--
-- Name: barter_monitoring_public_id_post_id_key; Type: CONSTRAINT; Schema: public; Owner: sps; Tablespace:
--

ALTER TABLE ONLY barter_monitoring
    ADD CONSTRAINT barter_monitoring_public_id_post_id_key UNIQUE (public_id, post_id);


--
-- Name: gr50k_pkey; Type: CONSTRAINT; Schema: public; Owner: sps; Tablespace:
--

ALTER TABLE ONLY old_50k_points
    ADD CONSTRAINT gr50k_pkey PRIMARY KEY ("time", id);


--
-- Name: group_pablic_relation_pkey; Type: CONSTRAINT; Schema: public; Owner: sps; Tablespace:
--

ALTER TABLE ONLY stat_group_public_relation
    ADD CONSTRAINT group_pablic_relation_pkey PRIMARY KEY (public_id, group_id);


--
-- Name: groups_pkey; Type: CONSTRAINT; Schema: public; Owner: sps; Tablespace:
--

ALTER TABLE ONLY stat_groups
    ADD CONSTRAINT groups_pkey PRIMARY KEY (group_id);


--
-- Name: groups_pkey1; Type: CONSTRAINT; Schema: public; Owner: sps; Tablespace:
--

ALTER TABLE ONLY groups
    ADD CONSTRAINT groups_pkey1 PRIMARY KEY (group_id);


--
-- Name: mes_dialog_statuses_pkey; Type: CONSTRAINT; Schema: public; Owner: sps; Tablespace:
--

ALTER TABLE ONLY mes_dialog_statuses
    ADD CONSTRAINT mes_dialog_statuses_pkey PRIMARY KEY (id);


--
-- Name: mes_dialogs_groups_pkey; Type: CONSTRAINT; Schema: public; Owner: sps; Tablespace:
--

ALTER TABLE ONLY mes_dialogs_groups
    ADD CONSTRAINT mes_dialogs_groups_pkey PRIMARY KEY (group_id);


--
-- Name: mes_dialogs_pkey; Type: CONSTRAINT; Schema: public; Owner: sps; Tablespace:
--

ALTER TABLE ONLY mes_dialogs
    ADD CONSTRAINT mes_dialogs_pkey PRIMARY KEY (id);


--
-- Name: mes_dialogs_user_id_rec_id_key; Type: CONSTRAINT; Schema: public; Owner: sps; Tablespace:
--

ALTER TABLE ONLY mes_dialogs
    ADD CONSTRAINT mes_dialogs_user_id_rec_id_key UNIQUE (user_id, rec_id);


--
-- Name: mes_group_dialog_relation_pkey; Type: CONSTRAINT; Schema: public; Owner: sps; Tablespace:
--

ALTER TABLE ONLY mes_group_dialog_relation
    ADD CONSTRAINT mes_group_dialog_relation_pkey PRIMARY KEY (group_id, dialog_id);


--
-- Name: mes_group_user_relation_pkey; Type: CONSTRAINT; Schema: public; Owner: sps; Tablespace:
--

ALTER TABLE ONLY mes_group_user_relation
    ADD CONSTRAINT mes_group_user_relation_pkey PRIMARY KEY (group_id, user_id);


--
-- Name: mes_queue_pkey; Type: CONSTRAINT; Schema: public; Owner: sps; Tablespace:
--

ALTER TABLE ONLY mes_queue
    ADD CONSTRAINT mes_queue_pkey PRIMARY KEY (id);


--
-- Name: newUserRequests_pkey; Type: CONSTRAINT; Schema: public; Owner: sps; Tablespace:
--

ALTER TABLE ONLY "newUserRequests"
    ADD CONSTRAINT "newUserRequests_pkey" PRIMARY KEY ("newUserRequestId");


--
-- Name: new_50k_pkey; Type: CONSTRAINT; Schema: public; Owner: sps; Tablespace:
--

ALTER TABLE ONLY stat_publics_50k_points
    ADD CONSTRAINT new_50k_pkey PRIMARY KEY (id, "time");


--
-- Name: oadmins_pkey; Type: CONSTRAINT; Schema: public; Owner: sps; Tablespace:
--

ALTER TABLE ONLY oadmins
    ADD CONSTRAINT oadmins_pkey PRIMARY KEY (vk_id);


--
-- Name: oadmins_posts_pkey; Type: CONSTRAINT; Schema: public; Owner: sps; Tablespace:
--

ALTER TABLE ONLY oadmins_posts
    ADD CONSTRAINT oadmins_posts_pkey PRIMARY KEY (id);


--
-- Name: oadmins_posts_vk_post_id_public_id_key; Type: CONSTRAINT; Schema: public; Owner: sps; Tablespace:
--

ALTER TABLE ONLY oadmins_posts
    ADD CONSTRAINT oadmins_posts_vk_post_id_public_id_key UNIQUE (vk_post_id, public_id);


--
-- Name: oadmins_public_points_pkey; Type: CONSTRAINT; Schema: public; Owner: sps; Tablespace:
--

ALTER TABLE ONLY oadmins_public_points
    ADD CONSTRAINT oadmins_public_points_pkey PRIMARY KEY (public_sb_id, ts);


--
-- Name: publ_rels_names_pkey; Type: CONSTRAINT; Schema: public; Owner: sps; Tablespace:
--

ALTER TABLE ONLY publ_rels_names
    ADD CONSTRAINT publ_rels_names_pkey PRIMARY KEY (user_id, publ_id, group_id);


--
-- Name: publics_pkey; Type: CONSTRAINT; Schema: public; Owner: sps; Tablespace:
--

ALTER TABLE ONLY publics
    ADD CONSTRAINT publics_pkey PRIMARY KEY (vk_id);


--
-- Name: serv_access_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: sps; Tablespace:
--

ALTER TABLE ONLY serv_access_tokens
    ADD CONSTRAINT serv_access_tokens_pkey PRIMARY KEY (access_token);


--
-- Name: stat_our_publics_vis_vie_pkey; Type: CONSTRAINT; Schema: public; Owner: sps; Tablespace:
--

ALTER TABLE ONLY stat_our_publics_vis_vie
    ADD CONSTRAINT stat_our_publics_vis_vie_pkey PRIMARY KEY (public_id, date);


--
-- Name: stat_publics_50k_pkey; Type: CONSTRAINT; Schema: public; Owner: sps; Tablespace:
--

ALTER TABLE ONLY stat_publics_50k
    ADD CONSTRAINT stat_publics_50k_pkey PRIMARY KEY (vk_public_id);


--
-- Name: stat_publics_50k_vk_id_key; Type: CONSTRAINT; Schema: public; Owner: sps; Tablespace:
--

ALTER TABLE ONLY stat_publics_50k
    ADD CONSTRAINT stat_publics_50k_vk_id_key UNIQUE (vk_id);


--
-- Name: temp_users_ids_store_pkey; Type: CONSTRAINT; Schema: public; Owner: sps; Tablespace:
--

ALTER TABLE ONLY temp_users_ids_store
    ADD CONSTRAINT temp_users_ids_store_pkey PRIMARY KEY (public_id);


--
-- Name: user_group_relation_pkey; Type: CONSTRAINT; Schema: public; Owner: sps; Tablespace:
--

ALTER TABLE ONLY stat_group_user_relation
    ADD CONSTRAINT user_group_relation_pkey PRIMARY KEY (user_id, group_id);


--
-- Name: users_pkey; Type: CONSTRAINT; Schema: public; Owner: sps; Tablespace:
--

ALTER TABLE ONLY stat_users
    ADD CONSTRAINT users_pkey PRIMARY KEY (user_id);


--
-- Name: name_search; Type: INDEX; Schema: public; Owner: sps; Tablespace:
--

CREATE INDEX name_search ON stat_publics_50k USING btree (name);


--
-- Name: public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM postgres;
GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL ON SCHEMA public TO PUBLIC;


--
-- PostgreSQL database dump complete
--

