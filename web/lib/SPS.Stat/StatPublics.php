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

    }
?>
