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
                    $public->externalId ==  35807078 )
                    continue;

                $a['id']    = $public->externalId;
                $a['title'] = $public->title;
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

        public static function get_public_users( $public_id, $offset = 0 )
        {
            set_time_limit(1000);
            $err_counter = 0;
            $public_id = +$public_id;
            $offset = +$offset;

            while (1) {
                $values = '';
                $params = array(
                                'gid'       =>  $public_id,
                                'offset'    =>  $offset,
                                );
                $res = VkHelper::api_request( 'groups.getMembers', $params );

                if (count( $res->users ) == 0) break;

                $values = implode('),(', $res->users );
                $values = '(' . $values . ')';

                $query = "INSERT INTO " . TABLE_TEMPL_USER_IDS . " VALUES $values";
                $cmd = new SqlCommand( $query, ConnectionFactory::Get( 'tst' ));
                $cmd->Execute();
                $offset += 1000;
                sleep(0.3);
            }
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
            foreach( $users_array as $user ) {
                $matches = array();
                $public_list = file_get_contents( self::FAVE_PUBLS_URL . $user );
                $public_list = explode( 'setUpTabbedBox', $public_list );
                preg_match_all( '/\/g(\d{2,14})\//', $public_list[0], $matches );

                $fave_array = reset( array_chunk( $matches[1], 7 ));
                $values = implode( '),(', $fave_array );
                if ( $values ) {
                    $sql = 'INSERT INTO ' . TABLE_TEMPL_PUBLIC_SHORTNAMES . ' VALUES (' . $values . ')';
                    $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst'));
                    $cmd->ExecuteNonQuery();
                }
                sleep(0.3);
            }
        }



         public static function truncate_table( $table ) {
             $sql = 'TRUNCATE TABLE ' .$table ;
             $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst'));
             return $cmd->ExecuteNonQuery();
         }

    }
?>
