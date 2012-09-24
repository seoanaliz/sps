<?php
/*    Package::Load( 'SPS.Articles' );
    Package::Load( 'SPS.Site' );*/
//    Package::Load( 'SPS.Stat' );

    class StatPublics
    {
        const FAVE_PUBLS_URL = 'http://vk.com/al_fans.php?act=show_publics_box&al=1&oid=';

        public static function get_our_publics_list()
        {
            $publics = TargetFeedFactory::Get();

            $res = array();
            foreach ( $publics as $public ) {

                if( $public->type != 'vk'             ||
                    $public->externalId ==  25678227  ||
                    $public->externalId ==  26776509  ||
                    $public->externalId ==  27421965  ||
                    $public->externalId ==  34010064  ||
                    $public->externalId ==  25749497  ||
                    $public->externalId ==  38000555  ||
                    $public->externalId ==  35807078 )
                    continue;

                $a['id']    = $public->externalId;
                $a['title'] = $public->title;
                $a['sb_id'] = $public->targetFeedId;
                $res[] = $a;
            }
            return $res;
        }

        public static function get_last_update_time()
        {
            $sql = 'SELECT MAX(time) FROM ' . TABLE_STAT_PUBLICS_POINTS;
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
            $ds = $cmd->Execute();
            $ds->Next();

            return $ds->getValue('max', TYPE_INTEGER);
        }

        public static function get_public_users( $public_id, $data_base, $offset = 0 )
        {
            $public_id = +$public_id;
            $offset    = +$offset;
            $sql = "INSERT INTO publics VALUES (@public_id, '{0}')";
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( $data_base ));
            $cmd->SetInteger( '@public_id', $public_id );
            $cmd->Execute();

            while (1) {
                $values = '';
                $code = '';
                $return = "return{";
                for ( $i = 0; $i < 25; $i++ ) {
                    $code   .= "var a$i = API.groups.getMembers({\"gid\":$public_id, \"count\":1000, \"offset\":$offset }) ;";
                    $return .= "\"a$i\":a$i,";
                    $offset += 1000;
                }
                $code .= trim($return, ',') . "};";
                $res = VkHelper::api_request( 'execute', array( 'code' => $code)  );
                if ( count( $res->a0->users ) == 0 )
                    break;
                foreach( $res as $query_reuslt ) {
                    $values .= implode( ',', $query_reuslt->users ) . ',';
                }
                echo '<br>' . count( explode( ',', $values)) . '<br>';
                sleep(0.4);
                $values = "{" . trim( $values, ',' ) . "}";

                $sql = 'UPDATE publics SET "vkIds" = "vkIds" + @array WHERE "publicId" = @public_id';
                $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
                $cmd->SetString ( '@array',     $values );
                $cmd->SetInteger( '@public_id', $public_id );
                $cmd->Execute();
            }
        }

        public static function get_50k( $start = 0, $stop = 1000000000 )
        {
            $sql = 'DELETE FROM publics WHERE "publicId" = @stop';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
            $cmd->SetInteger( '@stop',  $stop );
            $cmd->Execute();

            $sql = 'SELECT vk_id FROM ' . TABLE_STAT_PUBLICS . '
                    WHERE quantity > 50000 AND vk_id >= @start AND vk_id <= @stop
                    ORDER BY vk_id DESC';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
            $cmd->SetInteger( '@start', $start );
            $cmd->SetInteger( '@stop',  $stop  );
            $ds = $cmd->Execute();

            $res = array();

            while( $ds->Next()) {
                $res[] = $ds->GetValue( 'vk_id' );
            }
            return $res;
        }

        public static function get_distinct_users()
        {
            $sql = 'SELECT DISTINCT * FROM ' . TABLE_TEMPL_USER_IDS;
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst'));
            $ds =  $cmd->Execute();
            $res = array();
            while( $ds->Next()) {
                $res[] = $ds->GetValue( 'id', TYPE_INTEGER );
            }
            return $res;
        }

        public static function collect_fave_publics( $users_array )
        {
            set_time_limit(0);
            $fave_array = array();
            $i = 0;
            $url_array = array();
            foreach( $users_array as $user ) {
                $url_array[] = self::FAVE_PUBLS_URL . $user;
                echo self::FAVE_PUBLS_URL . $user . '<br>';
                $i++;
                if ( $i == 20 ) {
                    echo '1 <br>';
                    $res = array();
                    VkHelper::multiget( $url_array, $res );
//                    print_r($res);
                    foreach( $res as $page ) {
                        $matches = array();
//                        $public_list = file_get_contents( self::FAVE_PUBLS_URL . $user );
                        $page = explode( 'setUpTabbedBox', $page );
                        preg_match_all( '/\/g(\d{2,14})\//', $page[0], $matches );
                        $fave_array = array_merge( $fave_array, reset( array_chunk( $matches[1], 7 )));
                    }

                    $values = implode( '),(', $fave_array );
                    if ( $values ) {
                        $sql = 'INSERT INTO ' . TABLE_TEMPL_PUBLIC_SHORTNAMES . ' VALUES (' . $values . ')';
                        $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst'));
                        $cmd->ExecuteNonQuery();
                    }
                    $i = 0;
                    $fave_array = array();
                    $url_array = array();
                }
                sleep( 0.1 );
            }
        }

        public static function truncate_table( $table )
        {
             $sql = 'TRUNCATE TABLE ' .$table ;
             $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst'));
             return $cmd->ExecuteNonQuery();
         }

        public static function get_intersections( $first_public, $second_public )
        {
            $sql = 'SELECT icount(
                     ( SELECT "vkIds" FROM "publics" WHERE "publicId" = @first_public )
                      &
                     ( SELECT "vkIds" FROM "publics" WHERE "publicId" = @second_public ))';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
            $cmd->SetInteger( '@first_public',   $first_public  );
            $cmd->SetInteger( '@second_public',  $second_public );
            $ds = $cmd->Execute();
            $ds->Next();
            return $ds->GetInteger( 'icount');
        }

        public static function get_table()
        {
            $publics = self::get_50k();
            $count   = count( $publics );
            $res = array();
            $a = microtime(1);
            for ( $i = 0; $i < $count; $i++ ) {
                for ( $j = $i + 1; $j < $count; $j++ ) {
                    $res[$i][$j] = self::get_intersections( $publics[$i], $publics[$j] );
                    $res[$j][$i] = $res[$i][$j];
                    echo 'между ' . $publics[$i] . ' и ' . $publics[$j] . ' ' . $res[$j][$i] .' пересечений<br>';
                }


                echo '<br>';
                die( $a - microtime(1));
            }
            print_r($res);
            return $res;
        }


    }
?>
