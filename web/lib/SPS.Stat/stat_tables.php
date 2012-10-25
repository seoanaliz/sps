<?php
/*    Package::Load( 'SPS.Articles' );
    Package::Load( 'SPS.Site' );*/


###constants

define('ADMIN_RANK', 2);

define( 'ERR_MISSING_PARAMS', ObjectHelper::ToJSON( array( 'response' => false, 'err_mes'   =>  'parameters missing '    )));
define( 'ERR_NO_ACC_TOK'    , ObjectHelper::ToJSON( array( 'response' => false, 'err_mes'   =>  'user is not authorized' )));

###tables

    define( 'TABLE_STAT_USERS',       'stat_users');   #таблица юзеров

//статистика по крупным пабликам
    define( 'TABLE_STAT_PUBLICS_POINTS', 'stat_publics_50k_points'); #посуточные чек-ауты
    define( 'TABLE_STAT_PUBLICS',     'stat_publics_50k');          #таблица пабликов
    define( 'TABLE_STAT_GROUPS',      'stat_groups');               #группы(листы) пабликов
    define( 'TABLE_STAT_ADMINS',      'stat_admins');               #админы этих пабликов

    define( 'TABLE_STAT_GROUP_PUBLIC_REL', 'stat_group_public_relation');  #принадлежность пабликов группам
    define( 'TABLE_STAT_GROUP_USER_REL',   'stat_group_user_relation');    #принадлежность групп юзерам

//статистика по нашим админам
    define( 'TABLE_OADMINS_POSTS', 'oadmins_posts' );  #посты на
    define( 'TABLE_OADMINS', 'oadmins' );              #на
    define( 'TABLE_OADMINS_CONF', 'oadmins_conf' );

//messeger
    define ( 'TABLE_MES_DIALOGS', 'mes_dialogs' ); #список диалогов, статус для каждого
    define ( 'TABLE_MES_DIALOG_STATUSES', 'mes_dialog_statuses' );  #таблица статусов диалогов
    define ( 'TABLE_MES_GROUPS', 'mes_dialogs_groups' );  #группы диалогов
    define ( 'TABLE_MES_GROUP_USER_REL', 'mes_group_user_relation' );  #принадлежность группы диалогов юзерам
    define ( 'TABLE_MES_GROUP_DIALOG_REL', 'mes_group_dialog_relation' );  #принадлежность даилога группам
    define ( 'TABLE_MES_QUEUES', 'mes_queue' ); #очередь сообщений
    define ( 'TABLE_MES_TEXTS', 'mes_texts' ); #сами сообщения
    define ( 'TABLE_MES_ACTIVITY_LOG', 'mes_activity_log');
    define ( 'TABLE_MES_DIALOG_TEMPLATES', 'mes_dialog_templates');

//статистика по юзерам пабликов
    define( 'TABLE_TEMPL_USER_IDS',          'temp_user_ids'); //здесь хранятся юзеры 1 паблика. Только на время работы
    define( 'TABLE_TEMPL_PUBLIC_SHORTNAMES', 'temp_public_shortnames');//здесь - данные о популярности пабликов

?>
