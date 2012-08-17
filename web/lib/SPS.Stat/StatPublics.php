<?php
/*    Package::Load( 'SPS.Articles' );
    Package::Load( 'SPS.Site' );*/
//    Package::Load( 'SPS.Stat' );

    class StatPublics
    {

        public static function get_our_publics_list()
        {
            $publics = TargetFeedFactory::Get();
            $res = array();
            foreach ($publics as $public) {

                if( $public->type != 'vk'             ||
                    $public->externalId ==  25678227  ||
                    $public->externalId ==  26776509  ||
                    $public->externalId ==  27421965  ||
                    $public->externalId ==  34010064  ||
                    $public->externalId ==  25749497  ||
                    $public->externalId ==  35807078 )

                    continue;
                echo $public->externalId . ',<br>';
                $a['id'] = $public->externalId;
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

        //insert or update publics data from array od public ids
        public static function get_public_info( $public_ids )
        {
            $i = 0;
            $id_line = '';
            foreach( $public_ids as $public_id ) {

                $public_id = explode( '|', $public_id );
                $id = $public_id[0];
                $quantity[] = $public_id[1];
                $id_line .= $id . ',';
                if ( $i == 499 or !next( $public_ids ) ) {

                    $res = VkHelper::api_request('groups.getById', array( 'gids'  =>  $id_line ) );

                    foreach( $res as $public ) {
                      $sql = "UPDATE "
                              . TABLE_STAT_PUBLICS . "
                              SET
                                name=@name,
                                ava=@ava,
                                short_name=@short_name,
                                quantity=@quantity
                              WHERE
                                vk_id=@vk_id;

                              INSERT INTO " . TABLE_STAT_PUBLICS . "(vk_id, ava, name, short_name, quantity)
                                   SELECT @vk_id, @ava, @name, @short_name, @quantity
                                   WHERE NOT EXISTS ( SELECT 1 FROM " . TABLE_STAT_PUBLICS . " WHERE vk_id=@vk_id );
                            ";

                        $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
                        $cmd->SetInteger( '@quantity',  current( $quantity ) );
                        $cmd->SetInteger( '@vk_id',     $public->gid );
                        $cmd->SetString(  '@name',      $public->name );
                        $cmd->SetString(  '@ava',       $public->photo );
                        $cmd->SetString(  '@short_name',$public->screen_name );
                        $cmd->SetString(  '@name',      $public->name );
                        $cmd->Execute();

                        next( $quantity );
                    }

                    $quantity = array();
                    $id_line = '';
                    $i = 0;
                }

                $i++;
            }
        }

        public static function get_50k_publics()
        {
            $sql = 'SELECT vk_id FROM ' . TABLE_STAT_PUBLICS . " ORDER BY vk_id";
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ) );
            $ds = $cmd->Execute();
            $result = array();
            while ( $ds->Next() ) {
                $result[] = $ds->GetValue( 'vk_id', TYPE_INTEGER );
            }
            return $result;

        }

    }
?>
