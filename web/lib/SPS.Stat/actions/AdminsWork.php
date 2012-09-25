<?php
/*    Package::Load( 'SPS.Articles' );
    Package::Load( 'SPS.Site' );*/
    Package::Load( 'SPS.Stat' );

    class AdminsWork extends wrapper
    {

        public static  $white_list = array(
            1715958,
            2814488,
            6810901,
            7203958,
            7875269,
            18379341,
            25766591,
            27575384,
            43916879,
            58540552,
            114080351,
            121069867,
            135339094,
            182447583,
            110337004,

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
            Response::setArray( 'our_publics', $publics );
        }

        private function get_conf() {
            $sql = 'SELECT * FROM ' . TABLE_OADMINS_CONF;
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ) );
            $ds = $cmd->Execute();
            $ds->Next();


        }

        public static function get_public_admins($date_min, $date_max, $public_id = 0 ) {
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
                        ORDER BY b.name'
                        ;
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
                if ( $post_time - $time_prev < 11 * 60 ) {
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

                $post_id     = $ds->getValue( 'vk_post_id', TYPE_INTEGER );
                $res[]       = $post_id;
                $reposts    += $ds->getValue( 'reposts', TYPE_INTEGER );
                $diff       += $ds->getValue( 'likes', TYPE_INTEGER );
                $diff_rel   += $ds->getValue( 'rel_likes', TYPE_FLOAT );

                $post_time = $ds->GetValue( 'post_time', TYPE_INTEGER );
                if ( $post_time - $time_prev < 11 * 60 ) {
                    $overposts++;
                }
                $time_prev = $post_time;

                if( $ds->getValue( 'is_topic', TYPE_BOOLEAN ))
                    $topics++;

                if( $ds->getValue( 'complicate', TYPE_BOOLEAN ))
                    $compls++;
            }

            $q = count($res);
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


    }
?>
