<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 28.10.12
 * Time: 21:06
 * To change this template use File | Settings | File Templates.
 */
//монструозина
//изначально нужна была только для отслеживания бартеров. Основная фича
// парс большого количества стен(особенно наших пабликов) . появился до функционал(отслеживание всех
//исходящих ссылей, отслеживание упоминаний.
//теперь здесь же будут мониторится посты, созданные не через sb и туда вноситься
class CheckWalls
{
    private $posts_in_progress;
    private $cookie = null;
    private $existing_external_ids;

    const url_mentions = 'http://vk.com/feed?section=mentions&obj=';
    const DEFAULT_AUTO_EVENTS_GROUP = 46;
    const MONITORING_TYPE_WALL = 'wall';
    const MONITORING_TYPE_MENTIONS = 'mentions';
    const MONITORING_TYPE_NOT_SB_POSTS = 'not_sb';

    private  $monitoring_array = array(
        43005314,
        42933269,
        43503298,
        41611636,
        42841673,
        26675756,
        43005314,
        39930420,
        43503681
    );

    public function Execute()
    {
//        error_reporting(0);
        set_time_limit( 0 );

        $this->get_mentions();
        $this->get_existing_external_ids();
        $this->posts_in_progress = $this->get_posts_under_observation();
        if ( date('H') == 1 && date('i') < 15 ) {
            //установка новых автомониторов
            $this->temp_barter_creater();
        }
        $this->kill_overtimed();
        $this->turn_on_search();

        $barters_for_search = BarterEventFactory::Get( array( '_status' => 2 ), null, 'tst' );
        $search_results = $this->wall_search( $barters_for_search );
        StatPublics::update_population( $search_results, 'start' );
        BarterEventFactory::UpdateRange( $search_results, null, 'tst' );
    }

    //
    public function kill_overtimed()
    {
        //ищем просроченные
        $events  = BarterEventFactory::Get( array( '_stop_search_atLE' => date('Y-m-d H:i:s',time() + StatPublics::time_shift ),'_status' => 2 ), null, 'tst' );
        foreach( $events as $event) {
            $event->status = 5;
            $event->posted_at = $event->start_search_at;
        }
        BarterEventFactory::UpdateRange( $events, null, 'tst' );
    }

    public function turn_on_search()
    {
        //ищем записи, которые включаются в поиск
        $events  = BarterEventFactory::Get( array( '_start_search_atLE' => date('Y-m-d H:i:s',time() + StatPublics::time_shift ), '_status' => 1 ), null, 'tst' );
        foreach( $events as $event)
            $event->status = 2;
        BarterEventFactory::UpdateRange( $events, null, 'tst' );
    }

    /** return BarterEvent[] */
    public function wall_search( $publics )
    {
        $ids_array = array();
        $our_publics = StatPublics::get_our_publics_list();
        $our_publics_ids = array_keys( $our_publics);

        //массив id пабликов для мониторинга бартеров
        foreach( $publics as $barter_event ) {
            $ids_array[] = $barter_event->barter_public;
        }
        //добавляем стены пабликов для отслеживания внешних ссылок
        $ids_array = array_merge( $ids_array, $this->monitoring_array, $our_publics_ids);
        $ids_array = array_unique( $ids_array );

        $walls = StatPublics::get_public_walls_mk2( $ids_array, 'barter' );
        foreach( $this->monitoring_array as $public_id ) {
            $post = $walls[ $public_id ][1];
            $this->save_post( $public_id, $post->id, $post->text );
        }

        //сохраняем активность наших пабликов
        foreach( $our_publics_ids as  $public_id ) {
            if ( !isset( $walls[ $public_id ][1] ))
                continue;
            $post = $walls[ $public_id ][1];
            $this->save_post( $public_id, $post->id, $post->text, self::MONITORING_TYPE_NOT_SB_POSTS );

            //заносим отсутствующие в sb посты в sb
            $this->add_posts_to_sb_queue( $walls[$public_id], $our_publics[$public_id]['sb_id'] );
        }

        //перебор мониторов, поиск постов на соответств. стенах
        $result = array();
        foreach( $publics as $barter_event ) {
            /* @var $barter_event BarterEvent*/

            if ( empty( $walls[ $barter_event->barter_public] ))
                continue;

            foreach( $walls[ $barter_event->barter_public ] as $post ) {

                //Если этот пост уже наблюдается
                if ( isset($this->posts_in_progress[$barter_event->creator_id]) && in_array( $barter_event->barter_public . '_' . $post->id, $this->posts_in_progress[$barter_event->creator_id] )) {
                    echo 'вылетел по причине наличия обзора над постом ' . $barter_event->barter_public . '_' . $post->id . '<br>';
                    continue;
                }

                $barter_post = $this->find_barter( $post->text, $barter_event->search_string, $barter_event->target_public );
                //если в тексте есть вики ссылка, или это репост с нашего паблика
                if ( $barter_post
                    || ( isset( $post->copy_owner_id ) && ltrim( $post->copy_owner_id, '-' ) == $barter_event->target_public )) {
                    $barter_event->status       =   3;
                    $barter_event->posted_at    =   date('Y-m-d H:i:s', $post->date);
                    $barter_event->post_id      =   $post->id;
                    $barter_event->detected_at  =   date( 'Y-m-d H:i:s', time());
                    $barter_event->stop_search_at = date( 'Y-m-d H:i:s', time() + 4000);

                    $result[] = $barter_event;
                    //добавляем в список наблюдаемых постов
                    $this->posts_in_progress[$barter_event->creator_id][] = $barter_event->barter_public . '_' . $post->id;
                    break;
                }
            }
        }
        return $result;
    }

    public function find_barter( $search_string, $public_shortname, $public_id )
    {
        $preg_filter = '/(\[\s?(?:club|public)' . $public_id . '\s?\|)/';
        if ( !preg_match( $preg_filter , $search_string ))
            if( !preg_match( '/(\[\s?' . $public_shortname . '\s?\|)/', $search_string ))
                return false;
        return true;
    }

    //омг. задание автомониторов
    public function temp_barter_creater()
    {
        $our_array = array(
             43157718
            ,38000555
            ,43503575
            ,43503460
            ,43503503
            ,43503550
            ,43503725
            ,43503431
            ,43503315
            ,43503298
            ,43503235
            ,43503264
            ,52223807
        );

        $not_our_array = array(
            35806721,
            36959733 ,
            35807148 ,
            35807199 ,
            36959676 ,
            35806476 ,
            36959959 ,
            36621543 ,
            35807284 ,
            38000303 ,
            37140953 ,
            36621560 ,
            35807216 ,
            36959798 ,
            37140910 ,
            35807044 ,
            37140977 ,
            36959483 ,
            38000455 ,
            35806378 ,
            35807213 ,
            38000361 ,
            36621513 ,
            35806186 ,
            38000487,
            38000467,
            35807190 ,
            38000435 ,
            35807071 ,
            35807273 ,
            38000323 ,
            38000382 ,
            43503681 ,
            43503725 ,
            43503694 ,
            43503630 ,
            43503753 ,
            38000341 ,
        );

        $barter_events_array = array();
        $publs_info = array_merge( $our_array, $not_our_array);
        $info = StatPublics::get_publics_info( $publs_info );
        $group_id = GroupsUtility::get_default_group( '196506553', Group::BARTER_GROUP );

        foreach( $our_array as $oid ) {
            foreach( $not_our_array as $noid ) {
                if ( $oid == $noid )
                    continue;
                $now = time();
                $check = BarterEventFactory::Get(
                    array(
                     '_barter_public'       =>  $noid
                    ,'_target_public'       =>  $oid
                    ,'_created_atGE'        =>  date( 'Y-m-d 00:00:01', $now )
                    ,'_status' => array(1,2,3,4)
                    )
                );
                if( !empty( $check )) {
                    print_r( $check );
                    return true;
                }

                $barter_event = new BarterEvent();
                $barter_event->barter_public =  $info[$noid]['id'];
                $barter_event->target_public =  $info[$oid]['id'];
                $barter_event->status        =  1;
                $barter_event->search_string =  $info[$oid]['shortname'];
                $barter_event->barter_type   =  1;
                $stop_looking_time           = date( 'Y-m-d 00:45:00', $now + 86400 );
                $barter_event->start_search_at =  date( 'Y-m-d H:i:s', $now );
                $barter_event->stop_search_at  =  $stop_looking_time;
                $barter_event->standard_mark = true;
                $barter_event->created_at    = date ( 'Y-m-d H:i:s', $now );
                $barter_event->creator_id    = $group_id->created_by;
                $barter_event->groups_ids    = array( self::DEFAULT_AUTO_EVENTS_GROUP );
                $barter_events_array[] = $barter_event;
            }
        }
        BarterEventFactory::AddRange( $barter_events_array, array( BaseFactory::WithReturningKeys => true ), 'tst' );
    }

    public function get_page_name( $urls )
    {
        $search = array( '/^(club)/', '/^(public)/');

        $query_line = array();
        foreach( $urls as $url ) {
            $url = parse_url( $url );
            $url = ltrim( $url['path'], '/' );
            $query_line[] = preg_replace( $search, '', $url );
        }

        $info = StatPublics::get_publics_info( $query_line );
        return  array( 'target' =>  reset( $info ),
            'barter' =>  end( $info ));
    }

    public static function refresh_monitors()
    {
        $now = date( 'Y-m-d H:i:s', time());
        $check = BarterEventFactory::Get(
            array(
                 '_created_atGE'    => date( 'Y-m-d 00:00:01', time())
                ,'standard_mark'    => true
            )
        );

        if( !empty( $check ))
            return;

        $active_monitors = BarterEventFactory::Get( array( 'status' => 2 ));
        $new_monitors = array();
        foreach( $active_monitors as $monitor ) {
            $new_monitors[]          = clone $monitor;
            $monitor->standard_mark  = false;
            $monitor->status         = 5;
            $monitor->stop_search_at = $now;
        }

        BarterEventFactory::AddRange( $new_monitors );
        BarterEventFactory::UpdateRange( $active_monitors );
    }

    /** @var array */
    private function get_posts_under_observation()
    {
        //ищем все обмены за последнее время, для которых пост был найден
        $result = array();
        $events  = BarterEventFactory::Get( array( '_status' => array( 3,4,6 ), '_start_search_atGE' =>date('Y-m-d 05:00:00', strtotime(" -2 days"))), null, 'tst' );
        foreach( $events as $event) {
            $result[$event->creator_id][] = $event->barter_public . '_' . $event->post_id;
        }
        return $result;
    }

    private function check_if_post_registered( $public_id, $post_id )
    {
        $sql = 'SELECT * FROM barter_monitoring WHERE post_id = @post_id and public_id = @public_id';
        $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
        $cmd->SetInteger( '@public_id', $public_id );
        $cmd->SetInteger( '@post_id', $post_id );
        $ds = $cmd->Execute();
        return $ds->Next();
    }

    private function find_memlink( $text )
    {
        $matches = array();
        if ( preg_match( '/\[(.*?)\|/', $text, $matches ) || preg_match( '/\[(.*?)\|/', $text, $matches ))
            return $matches[1];
        return null;
    }

    private function find_url( $text )
    {
        $matches = array();
        if ( preg_match( '/(https?|ftp):\/\/\S+[^\s.,> )\];\'\"!?]/', $text, $matches )) {
            return $matches[0];
        }
        return false;
    }

    private function save_post( $public_id, $post_id, $text, $type = self::MONITORING_TYPE_WALL, $link = null, $mentioned_public = null )
    {
        if( !$link ) {
            $link = $this->find_memlink( $text );
        }
        $outer_link = $this->find_url( $text );
        if ( $outer_link ) {
            $link = $outer_link;
        }
        if( $link && $type == self::MONITORING_TYPE_WALL )
            return false;

        if( !$this->check_if_post_registered( $public_id, $post_id )) {
            $sql = 'INSERT INTO barter_monitoring VALUES (@public_id, @post_id, now(), @text, @link, @type, @mentioned_public)';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
            $cmd->SetInteger( '@mentioned_public', $mentioned_public );
            $cmd->SetInteger( '@public_id', $public_id );
            $cmd->SetInteger( '@post_id',  $post_id );
            $cmd->SetString ( '@text',     trim( strip_tags( $text )));
            $cmd->SetString ( '@link',     $link );
            $cmd->SetString ( '@type',     $type );
            $cmd->Execute();
            if( $outer_link ) {
                $message = 'У нас внешние ссылки(' . $outer_link . '): http://vk.com/wall-' . $public_id . '_' . $post_id ;
                VkHelper::send_alert( $message, array( 670456, 106175502 ));
            }
        }
    }

    private function get_publics_for_mentions()
    {
        return array();
    }

    //собирает упоминания пабликов
    private function get_mentions()
    {
        $publics_for_men_search = $this->get_publics_for_mentions();

        $i = 0;
        while( $i < 10 ) {
            $this->cookie = VkHelper::vk_authorize();
            if ($this->cookie ) break;
            sleep(3);
        }
        foreach( $publics_for_men_search as $public_id ) {
            echo 'mentions of ' . $public_id . '<br>';

            $page = VkHelper::connect( 'http://vk.com/feed?section=mentions&obj=-' . $public_id, $this->cookie );
            $this->parse_mentions_page( $page, $public_id );
        }
    }

    private function parse_mentions_page( $page, $public_id )
    {
        $document = phpQuery::newDocument( $page );
        $posts = $document->find('div.feed_row');
        foreach( $posts as $post ) {
            $feed_row = pq($post);
            $a = $feed_row->find('div.post');
            $source = $a->attr('id');
            //проверка на тип стены( если личная страница - мимо)
            if( !substr_count( $source, '-')) {
                continue;
            }
            //проверка, не является ли пост ответом на стене группы
            $reply = $feed_row->find( 'a.reply_parent_link' );
            if( $reply->length()) {
                echo '$reply is not empty';
                continue;
            }

            $a = $feed_row->find('[mention_id]');
            $mention = ($a->Attr('mention_id'));
            //проверка на упоминание именно этого паблика. не нужна вроде
            $mention = str_replace( array( 'public', 'club' ), '', $mention );
            if( $mention != $public_id ) {
                continue;
            }

            //регэкспим id паблика и поста. если нет - мимо
            preg_match( '/-(\d*?)_(\d*)/', $source, $matches );
            if( count( $matches) != 3 ) {
                continue;
            }
            $source_public_id = $matches[1];
            $source_post_id   = $matches[2];
            $text = $feed_row->find( '.wall_post_text' );
            $text = $text->getString();
            $text = $text[0];
            $this->save_post( $source_public_id, $source_post_id, $text, self::MONITORING_TYPE_MENTIONS, null, $public_id );

        }
    }

    public function get_existing_external_ids()
    {
        $result = array();
        //выбираем внешние id отправленных чз sb(или уже спаренных) постов
        $sql = <<<sql
            SELECT "externalId"
            FROM   "articleQueues"
            WHERE
                   "sentAt" IS NOT NULL
               AND "sentAt" > now() - interval '15 minute'
               AND "externalId" IS NOT NULL
sql;

        $cmd = new SqlCommand($sql, ConnectionFactory::Get());
        $ds = $cmd->Execute();
        while( $ds->Next()) {
            $result[$ds->GetValue('externalId')] = 1;
        }

        $this->existing_external_ids = $result;
    }

    //сохраняем посты(сделанные не через sb), заносим их в очередь(правую ленту) как отправленные
    public function add_posts_to_sb_queue( $posts, $targeetFeedId, $check_time = true )
    {
//        $likes_array  = array();
        $look_from_time = new DateTimeWrapper( date('r', time() - 600 ));
        $look_to_time   = new DateTimeWrapper( date('r', time() - 60 ));
        $converted_posts = ParserVkontakte::post_conv( $posts );
        foreach( $converted_posts as $post ) {
            $postDate = new DateTimeWrapper( date('r', $post['time'] ));
            //общее условие поиска по времени( посты, опубликованные от 1 до 10 минут назад)
            $check_date_condition = $check_time &&  ( $postDate >= $look_from_time ) && ( $postDate <= $look_to_time );
            //отсеиваем неподходящие по времени или уже содержащиеся в базе
            if ( !$check_date_condition  || isset( $this->existing_external_ids[$post['id']] )) {
//                $likes_array[ $post['id']] = array('likes' => $post['likes_tr'], 'retweet' => $post['retweet']);
                continue;
            }
            $articleRecord = ParserVkontakte::get_articleRecord_from_post( $post );

            $conn = ConnectionFactory::Get();
            $conn->begin();
            $articleQueue = ParserVkontakte::get_articleQueue_from_article( $post, $postDate, $targeetFeedId );
            ArticleQueueFactory::Add( $articleQueue, array(BaseFactory::WithReturningKeys => true) );
            $articleRecord->articleQueueId = $articleQueue->articleQueueId;
            $this->make_grids( $targeetFeedId, $postDate );
            $result = ArticleRecordFactory::Add( $articleRecord );

            if(  $result && $articleRecord->articleQueueId ) {
                $conn->commit();
            } else {
                $conn->rollback();
            }
        }
    }

    public function make_grids( $target_feed_id, $sent_at )
    {
        $date = $sent_at->format('d.m.Y');
        $grid_line = new GridLine();
        $grid_line->startDate = $date;
        $grid_line->endDate = $date;
        $grid_line->targetFeedId = $target_feed_id;
        $grid_line->time = $sent_at->format('H:i:s');
        $grid_line->type = GridLineUtility::TYPE_CONTENT;
        $result = GridLineFactory::Add( $grid_line, array(BaseFactory::WithReturningKeys => true));

        if (!$result )
            return false;

        $grid_line_item = new GridLineItem();
        $grid_line_item->gridLineId = $grid_line->gridLineId;
        $grid_line_item->date = $sent_at;

        return GridLineItemFactory::Add( $grid_line_item );
    }

    //собираем 15 последних вступивших в паблик, нужно для точки отсчта при финализации бартера
    private function get_init_members( BarterEvent $barter_event )
    {
        $barter_event->init_users = array();

        $params = array(
            'gid'   =>  $barter_event->target_public,
            'count' =>  15,
            'sort'  =>  'time_desc'
        );

        for( $try = 0; $try < 3; $try++ ) {
            $res = VkHelper::api_request( 'groups.getMembers', $params, 0 );
            if( isset( $res->error )) {
                continue;
            } else {

            }
        }

        return false;
    }
}