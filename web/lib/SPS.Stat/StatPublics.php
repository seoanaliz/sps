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
                    $public->externalId <   35806186  ||
                    $public->externalId >   36959733  ||
                    $public->externalId ==  25678227  ||
                    $public->externalId ==  26776509  ||
                    $public->externalId ==  27421965 )
                    continue;

                $a['id'] = $public->externalId;
                $a['title'] = $public->title;
                $res[] = $a;
            }
            return $res;
        }

    }
?>