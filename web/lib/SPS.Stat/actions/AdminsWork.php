<?php
/*    Package::Load( 'SPS.Articles' );
    Package::Load( 'SPS.Site' );*/
Package::Load( 'SPS.Stat' );

class AdminsWork extends wrapper
{
    public static  $white_list = array(
        2701428,
        1715958,
        2814488,
        3969468,
        4181767,
        5155508,
        5274121,
        6810901,
        7203958,
        7875269,
        10497980,
        11716281,
        17662525,
        18379341,
        25766591,
        27575384,
        43032990,
        43916879,
        58540552,
        61514101,
        83475534,
//        106175502,
        110337004,
        114080351,
        121069867,
        135339094,
        150220483,
        161113216,
        178503163,
        14412297,
        13819249,
        174517560
    );

    public function execute()
    {
        $publics = StatPublics::get_our_publics_list();

        $result = array();
//            foreach ($publics as $public) {
//                echo 'public ' . $public . '<br>';
//                $admins = $this->get_public_admins( $public );
//
//                foreach( $admins as $admin ) {
//                    echo '     admin ' . $admin . '<br>';
//                    $line = $this->get_posts( $admin, $public );
//                    if (!$line)
//                        continue;
//                    print_r($line);
//                    die();
//                }
//
//            }

//            Response::setInteger( 'pages', round($i/50,0));
//            Response::setInteger( 'last_time', date("d.m.Y", $time_for_table));
        $conf =

            Response::setArray( 'our_publics', $publics );

//            Response::SetArray( 'options', $ );
    }

    private function get_conf() {
        $sql = 'SELECT * FROM ' . TABLE_OADMINS_CONF;
        $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ) );
        $ds = $cmd->Execute();
        $ds->Next();
    }

    public static function get_public_admins($date_min, $date_max, $public_id = 0 )
    {
        $date_max = $date_max ? $date_max : 1445736044;
        $date_min = $date_min ? $date_min : 0;

        $public_line = $public_id ? ' AND a.public_id=@public_id ' : '';
        {
            $sql = 'SELECT DISTINCT a.author_id, b.name, b.ava
                        FROM ' . TABLE_OADMINS_POSTS . ' as a, ' . TABLE_OADMINS . ' as b
                        WHERE a.author_id = b.vk_id
                            AND a.post_time > @date_min
                            AND a.post_time < @date_max '
                . $public_line . '
                        ORDER BY b.name
                        ';
        }

        $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ) );
        if ( $public_id )
            $cmd->SetInteger( '@public_id', $public_id['id'] );
        $cmd->SetInteger( '@date_min',  $date_min );
        $cmd->SetInteger( '@date_max',  $date_max );

        $ds = $cmd->Execute();
        $res = array();

        while ($ds->Next()) {
            $a['id']    = $ds->getValue('author_id', TYPE_INTEGER);
            if ( !in_array( $a['id'], self::$white_list )) {
                continue;
            }
            $a['name']  = $ds->getValue('name', TYPE_STRING);
            $a['ava']   = $ds->getValue('ava', TYPE_STRING);

            $res[$a['id']] = $a;
            $a = array();
        }
//            $public_line = $public_id ? ' AND c."externalId"=@publicId ' : '';
//            $sql = 'select a."vkId",a."firstName",a."lastName",a.avatar
//                    from authors as a,articles as b,"targetFeeds" as c
//                    where a."authorId"=b."authorId"
//                        and c."targetFeedId"=b."targetFeedId"
//                        and b."sentAt">@sentFrom
//                        AND b."sentAt"<@sentTo'
//                        . $public_line ;
//            $cmd = new SqlCommand( $sql, ConnectionFactory::Get() );
//
//            $cmd->SetString  ('@sentFrom',    date('Y-m-d 00:00:00', $date_min)  );
//            $cmd->SetString  ('@sentTo',      date('Y-m-d 00:00:00', $date_max)    );
//            if ( $public_id )
//                $cmd->SetString  ('@publicId',   $public_id['id']  );
//            echo $cmd->GetQuery();
//            $ds = $cmd->Execute();
//
//            while ( $ds->Next() ) {
//
//                $a['id']  = $ds->getValue('vkId', TYPE_INTEGER);
//                if ( !in_array( $a['id'], self::$white_list )) {
//                    echo 'откинул ' . $a['id'] . '<br>';
//                    continue;
//                }
//                $a['name']  = $ds->getValue('firstName', TYPE_STRING) . ' ' . $ds->getValue('lastName', TYPE_STRING);
//                $a['ava']   = $ds->getValue('avatar', TYPE_STRING);
//
//                $res[$a['id']] = $a;
//                $a = array();
//            }

        if (count($res) == 0)
            return false;
        return $res;
    }

    public static function get_stat( $author_id, $public_id, $date_min = 0, $date_max = 0 )
    {
        $date_max = $date_max ? $date_max : 1543395811;
        $date_min = $date_min ? $date_min : 0;
        $sql = 'SELECT * FROM ' . TABLE_OADMINS_POSTS . '
                    WHERE   author_id=@author_id
                            AND public_id=@public_id
                            AND post_time > @date_min
                            AND post_time < @date_max
                    ORDER BY post_time ';

        $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
        $cmd->SetInteger('@author_id', $author_id);
        $cmd->SetInteger('@public_id', $public_id);
        $cmd->SetInteger('@date_min',  $date_min);
        $cmd->SetInteger('@date_max',  $date_max);
        $ds = $cmd->Execute();
        $res = array();
        $diff = 0;
        $diff_rel = 0;
        $topics = 0;
        $compls = 0;
        $reposts = 0;
        $overposts = 0;
        $time_prev = 0;

        while ( $ds->Next() ) {
            $post_id     = $ds->getValue( 'vk_post_id', TYPE_INTEGER );
            $res[]       = $post_id;
            $reposts    += $ds->getValue( 'reposts', TYPE_INTEGER );
            $diff       += $ds->getValue( 'likes', TYPE_INTEGER );
            $diff_rel   += $ds->getValue( 'rel_likes', TYPE_FLOAT );

            $post_time = $ds->GetValue( 'post_time', TYPE_INTEGER );
            if ( $post_time - $time_prev < 30 * 60 ) {
//                    echo ($post_time - $time_prev) . '<br>';
                $overposts++;
            }
            $time_prev = $post_time;

            if( $ds->getValue( 'is_topic', TYPE_BOOLEAN ))
                $topics++;

            if( $ds->getValue( 'complicate', TYPE_BOOLEAN ))
                $compls++;
        }

        $q = count( $res );
        print_r($res);
        if ( $q < 1)
            return false;
        $res['rel_likes']   = round( $diff / $q);
        $res['reposts']     = round( $reposts / $q, 2);
        $res['topics']      = $topics;
        $res['compls']      = $compls;
        $res['overposts']   = $overposts;
        $res['diff_rel']    = $diff_rel / $q;
//            $res['sb_posts']    =  $sb_posts;
        return $res;
    }

    private function get_sboard_posts( $author_id, $public_id , $time_from, $time_to )
    {
        $sql = 'SELECT b."vkId",c."externalId" FROM "articles" as a
                    JOIN "authors"     as b ON a."authorId"=b."authorId"
                    JOIN "targetFeeds" as c ON a."targetFeedId"=c."targetFeedId"
                    WHERE
                        b."vkId"=@authorId
                        AND a."sentAt">@sentFrom
                        AND a."sentAt"<@sentTo
                        AND c."externalId"=@publicId';

//                  WHERE b."authorId"=38000555
//                  and date between @sendFrom::date AND @sendTo::date
//                  AND a."sentAt"<'2012-08-21 00:00:00'
//                  AND c."externalId"=110337004

        $cmd = new SqlCommand( $sql, ConnectionFactory::Get() );
        $cmd->SetInteger ('@authorId',   $author_id  );
        $cmd->SetString  ('@sentFrom',   $time_from  );
        $cmd->SetString  ('@sentTo',     $time_to    );
        $cmd->SetString  ('@publicId',   $public_id  );
//            echo $cmd->getQuery();
        $ds = $cmd->Execute();

        $ds->Last();
        $a = $ds->GetCursor();
        return ++$a;

    }

    public static function get_posts( $author_id, $public_id, $date_min = 0, $date_max = 0 )
    {
        $date_max = $date_max ? $date_max : 1543395811;
        $date_min = $date_min ? $date_min : 0;
        $sql = 'SELECT * FROM ' . TABLE_OADMINS_POSTS . '
                    WHERE   author_id=@author_id
                            AND public_id=@public_id
                            AND post_time > @date_min
                            AND post_time < @date_max
                    ORDER BY post_time ';

        $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
        $cmd->SetInteger('@author_id', $author_id);
        $cmd->SetInteger('@public_id', $public_id);
        $cmd->SetInteger('@date_min',  $date_min);
        $cmd->SetInteger('@date_max',  $date_max);
//            echo $cmd->GetQuery();
        $ds = $cmd->Execute();
        $res = array();
        $diff = 0;
        $diff_rel = 0;
        $topics = 0;
        $compls = 0;
        $reposts = 0;
        $overposts = 0;
        $time_prev = 0;


        while ( $ds->Next() ) {
            $tmp_rel_likes = $ds->getValue( 'rel_likes', TYPE_FLOAT );
            $tmp_rel_likes = ( $tmp_rel_likes > 500 ) ? 100 : $tmp_rel_likes;
            $post_id     = $ds->getValue( 'vk_post_id', TYPE_INTEGER );
            $res[]       = $post_id;
            $reposts    += $ds->getValue( 'reposts', TYPE_INTEGER );
            $diff       += $ds->getValue( 'likes', TYPE_INTEGER );
            $diff_rel   += $tmp_rel_likes;

            $post_time = $ds->GetValue( 'post_time', TYPE_INTEGER );
            if ( $post_time - $time_prev < 30 * 60 ) {
                $overposts++;
            }
            $time_prev = $post_time;

            if( $ds->getValue( 'is_topic', TYPE_BOOLEAN ))
                $topics++;

            if( $ds->getValue( 'complicate', TYPE_BOOLEAN ))
                $compls++;
        }

        $q = count( $res );
        if ($q < 1)
            return false;
        $res['rel_likes']   = round( $diff / $q);
        $res['reposts']     = round( $reposts / $q );
        $res['topics']      = $topics;
        $res['compls']      = $compls;
        $res['overposts']   = $overposts;
        $res['diff_rel']    = $diff_rel / $q;

        return $res;
    }

    //todo учет статусов?
    public static function get_articles( $author_sb_id, $target_feed_id = -1,  $time_from = 0, $time_to = 0 )
    {
        $feed_check = $target_feed_id == -1 ? '' : ' AND "targetFeedId" = @feed_id ';
        $time_from  = $time_from ? date( 'Y-m-d H:i:s', 0 ) : date( 'Y-m-d H:i:s', $time_from );
        $time_to    = $time_from ? date( 'Y-m-d H:i:s', time()) : date( 'Y-m-d H:i:s', $time_to );
        $sql = 'SELECT
                        "articleId"
                    FROM
                        articles
                    WHERE
                            "authorId"   =  @author_id '
            . $feed_check .
            ' AND "createdAt" >= @time_from
                        AND "createdAt" <= @time_to';
        $cmd = new SqlCommand( $sql, ConnectionFactory::Get());
        $cmd->SetInteger( '@author_id', $author_sb_id );
        $cmd->SetString ( '@time_from', $time_from );
        $cmd->SetString ( '@time_to'  , $time_to   );
        $cmd->SetInteger( '@feed_id'  , $target_feed_id );
        $ds = $cmd->Execute();
        $res = array();
        while( $ds->Next()) {
            $res[] =  $ds->GetInteger( 'articleId' );
        }
        return $res;
    }

    //article_ids -  строка id постов чз запятую
    public static function get_posts_quality( $article_ids )
    {
        $article_ids = '{' . $article_ids . '}';
        $sql = 'SELECT
                       "articleId",
                        char_length( content ) as length,
                        substring( "photos" from 3 for 2 ) as photos
                    FROM
                        "articleRecords"
                    WHERE
                        "articleId" = any( @article_id )';
//            $sql = 'SELECT char_length(content)as length,photos FROM "articleRecords" where "articleRecordId" = @article_id';

        $cmd = new SqlCommand( $sql, ConnectionFactory::Get() );
        $cmd->SetString( '@article_id', $article_ids );
        $ds = $cmd->Execute();
        $res = array();

        $average_mark = 0;
        $i = 0;
        while ( $ds->next()) {
            $i++;
            $length      =  $ds->GetInteger( 'length' );
            $article_id  =  $ds->GetInteger( 'articleId' );
            $photo_count =  trim( $ds->GetString( 'photos' ), ':');
            $length      =  $length > 4000 ? 4000 : $length;
            $photo_count =  $photo_count > 10 ? 10 : $photo_count;
            $average_mark += 5 * ( floor( $length / 400 ) + $photo_count );
        }
        return round( $average_mark / ( $i ? $i : 1)  );
    }

    //todo авторские/неавторские/все
    public static function get_average_values( $target_feed_id, $time_from = 0, $time_to = 0 )
    {
        $time_from = $time_from ? date( 'Y-m-d H:i:s', 0 ) : date( 'Y-m-d H:i:s', $time_from );
        $time_to   = $time_from ? date( 'Y-m-d H:i:s', time()) : date( 'Y-m-d H:i:s', $time_to );
        $sql = 'SELECT
                        avg("externalLikes")    as avg_likes,
                        avg("externalRetweets") as avg_retweets
                    FROM
                        "articleQueues"
                    WHERE
                       "targetFeedId"    = @target_feed_id
                        AND "sentAt" >= @time_from
                        AND "sentAt" <= @time_to
                        AND "externalLikes" > 20
                    ';
        $cmd = new SqlCommand( $sql, ConnectionFactory::Get() );
        $cmd->SetInteger( '@target_feed_id', $target_feed_id );
        $cmd->SetString ( '@time_from',      $time_from );
        $cmd->SetString ( '@time_to'  ,      $time_to   );
        $ds = $cmd->Execute();
        $ds->Next();
        return array(
            'likes'         =>  round( $ds->GetFloat( 'avg_likes' )),
            'retweets'      =>  round( $ds->GetFloat( 'avg_retweets' ))
        );
    }

    public static function get_authors_top( $public_ids )
    {

    }

    public static function get_public_authors( $target_feed_id )
    {
        $sql = 'SELECT * FROM authors WHERE @feed_id = ANY("targetFeedIds")';
        $cmd = new SqlCommand( $sql, ConnectionFactory::Get() );
        $cmd->SetInteger( '@feed_id'  , $target_feed_id );
        $ds = $cmd->Execute();
        $res = array();
        while ( $ds->Next()) {
            $res[ $ds->GetInteger( 'vkId')] =  array(
                'sb_id' =>  $ds->GetInteger( 'authorId' ),
                'name'  =>  $ds->GetValue( 'firstName' ) . ' ' . $ds->GetValue('lastName'),
                'ava'   =>  $ds->GetValue( 'avatar' )
            );
        }
        return $res;
    }

    //todo все посты/авторские(пока - авторские)
    public static function get_gen_editor_work( $editor_vk_id, $target_feed_id = -1, $time_from = 0, $time_to = 0 )
    {
        $feed_check = $target_feed_id == -1 ? '' : ' AND "targetFeedId" = @feed_id ';
        $time_from  = $time_from ? date( 'Y-m-d H:i:s', 0 ) : date( 'Y-m-d H:i:s', $time_from );
        $time_to    = $time_from ? date( 'Y-m-d H:i:s', time()) : date( 'Y-m-d H:i:s', $time_to );

        $sql = 'SELECT
                        count(*),
                        avg("externalLikes") as avg_likes,
                        avg("externalRetweets") as avg_retweets
                    FROM
                       "articleQueues" AS a,
                       "authorEvents"  AS b
                    WHERE
                        author=@author'
            . $feed_check .
            'AND "sentAt" >= @time_from
                        AND "sentAt" <= @time_to
                        AND a."articleId" = b."articleId"';
        $cmd = new SqlCommand( $sql, ConnectionFactory::Get() );
        $cmd->SetString ( '@author'   , $editor_vk_id );
        $cmd->SetString ( '@time_from', $time_from );
        $cmd->SetString ( '@time_to'  , $time_to   );
        $cmd->SetInteger( '@feed_id'  , $target_feed_id );
        $ds = $cmd->Execute();
        $ds->Next();
//        return array(
//            'posts_quantity'    =>  $ds->GetInteger(),
//            'avg_likes'         =>  round( $ds->GetFloat( 'avg_likes' )),
//            'avg_retweets'      =>  round( $ds->GetFloat( 'avg_retweets' ))
//        );

    }

    public static function get_author_posts_likes( $author_sb_id, $article_ids )
    {
        $article_ids = '{' . $article_ids . '}';
        $sql = 'SELECT
                        count(*),
                        avg("externalLikes") as avg_likes,
                        avg("externalRetweets") as avg_retweets
                    FROM
                        "articleQueues" AS a,
                         authors AS b
                    WHERE
                       "authorId" = @authorId
                        AND "articleId" = any( @article_id )
                        AND cast( a."author" as integer ) =  b."vkId" ';
        $cmd = new SqlCommand( $sql, ConnectionFactory::Get() );
        $cmd->SetInteger( '@authorId' , $author_sb_id );
        $cmd->SetString( '@article_id', $article_ids );
        $ds = $cmd->Execute();
        $ds->Next();
        return array(
            'posts_quantity'=>  $ds->GetInteger( 'count' ),
            'likes'         =>  round( $ds->GetFloat( 'avg_likes' )),
            'retweets'      =>  round( $ds->GetFloat( 'avg_retweets' )),
        );
    }

    public static function get_author_final_score( $author_id, $publics_list, $time_from = -1, $time_to = -1 )
    {
        $ids_rel = StatPublics::get_sb_public_ids( $publics_list );
        print_r($ids_rel);
        $res = array();
        echo '<br>' . $author_id . '<br>';
        foreach( $ids_rel as $public_id => $target_feed_id ) {

            $articles = AdminsWork::get_articles( $author_id, $target_feed_id, $time_from, $time_to );

            $articles = implode( ',', $articles );
            if ( !$articles )
                continue;
            print_r($articles);
            echo ' <br>';
            $average_quality = AdminsWork::get_posts_quality( $articles );
            print_r($average_quality);
            echo '<br>';
            $likes_rt_quan   = AdminsWork::get_author_posts_likes( $author_id, $articles );
            print_r($likes_rt_quan);
            echo '<br>';
            $average         = AdminsWork::get_average_values( $target_feed_id, $time_from, $time_to );
            $final_cf =
                (    $likes_rt_quan['likes']    / ( $average['likes']    * 200 )        +
                    7 * $likes_rt_quan['retweets'] / ( $average['retweets'] * 200 )) *
                    $likes_rt_quan['posts_quantity'] * $average_quality;
            $res[ $public_id ][ 'coef' ] = $final_cf;
        }

        return $res;
    }



}
?>
