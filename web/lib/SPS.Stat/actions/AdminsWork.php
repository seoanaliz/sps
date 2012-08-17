<?php
/*    Package::Load( 'SPS.Articles' );
    Package::Load( 'SPS.Site' );*/
    Package::Load( 'SPS.Stat' );

    class AdminsWork extends wrapper
    {

        public function execute()
        {
//            error_reporting( 0 );
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

        public function get_public_admins($date_min, $date_max, $public_id = 0 ) {
            $date_max = $date_max ? $date_max : 1543395811;
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
                $a['name']  = $ds->getValue('name', TYPE_STRING);
                $a['ava']   = $ds->getValue('ava', TYPE_STRING);

                $res[] = $a;
                $a = array();
            }

            if (count($res) == 0)
                return false;
            return $res;
        }

        public function get_posts( $author_id, $public_id, $date_min = 0, $date_max = 0 )
        {

//            echo '<br>min' . $date_min .'<br>max' . $date_max . '<br>';

            $date_max = $date_max ? $date_max : 1543395811;
            $date_min = $date_min ? $date_min : 0;
            $sql = 'SELECT * FROM ' . TABLE_OADMINS_POSTS . '
                    WHERE   author_id=@author_id
                            AND public_id=@public_id
                            AND post_time > @date_min
                            AND post_time < @date_max
                    ORDER BY post_time';

            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
            $cmd->SetInteger( '@author_id', $author_id );
            $cmd->SetInteger( '@public_id', $public_id );
            $cmd->SetInteger( '@date_min',  $date_min );
            $cmd->SetInteger( '@date_max',  $date_max );
            $ds = $cmd->Execute();
            $res        = array();
            $diff       = 0;
            $topics     = 0;
            $compls     = 0;
            $reposts    = 0;
            $overposts  = 0;

            $time_prev = 0;
            while ( $ds->Next() ) {
                $res['posts'][]     =   $ds->getValue('vk_post_id', TYPE_INTEGER);
                $reposts            +=  $ds->getValue('reposts',    TYPE_INTEGER);
                $diff               +=  $ds->getValue('likes',      TYPE_INTEGER);
                $post_time          =   $ds->getValue( 'post_time', TYPE_INTEGER );

                if (  $post_time - $time_prev  < 1200 )
                    $overposts++;

                if( $ds->getValue( 'is_topic', TYPE_BOOLEAN ) )
                    $topics += 1;

                if($ds->getValue('complicate', TYPE_BOOLEAN))
                    $compls += 1;

            }

            $q = count($res);

            if ($q < 1)
                return false;
            $res['rel_likes']   =   round( $diff / $q );
            $res['reposts']     =   round( $reposts / $q );
            $res['topics']      =   $topics;
            $res['compls']      =   $compls;
            $res['overposts']    =   $overposts;

            return $res;
        }
    }
?>
