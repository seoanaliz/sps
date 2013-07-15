<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 10.07.13
 * Time: 11:41
 * To change this template use File | Settings | File Templates.
 */
class BadooParser
{
    const BADOO_USER_URL    = 'http://badoo.com/';
    const MG_CONNECT_TIME   = 3;
    const MG_MAX_THREADS    = 100;

    public $weekAgoTS;

    public static  $allUseragents = array(
                "Opera/9.23 (Windows NT 5.1; U; ru)",
                "Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.8.1.8) Gecko/20071008 Firefox/2.0.0.4;MEGAUPLOAD 1.0",
                "Mozilla/5.0 (Windows; U; Windows NT 5.1; Alexa Toolbar; MEGAUPLOAD 2.0; rv:1.8.1.7) Gecko/20070914 Firefox/2.0.0.7;MEGAUPLOAD 1.0",
                "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0; MyIE2; Maxthon)",
                "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0; MyIE2; Maxthon)",
                "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0; MyIE2; Maxthon)",
                "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; WOW64; Maxthon; SLCC1; .NET CLR 2.0.50727; .NET CLR 3.0.04506; Media Center PC 5.0; InfoPath.1)",
                "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0; MyIE2; Maxthon)",
                "Opera/9.10 (Windows NT 5.1; U; ru)",
                "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.2.1; aggregator:Tailrank; http://tailrank.com/robot) Gecko/20021130",
                "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.8) Gecko/20071008 Firefox/2.0.0.8",
                "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0; MyIE2; Maxthon)",
                "Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.8.1.8) Gecko/20071008 Firefox/2.0.0.8",
                "Opera/9.22 (Windows NT 6.0; U; ru)",
                "Opera/9.22 (Windows NT 6.0; U; ru)",
                "Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.8.1.8) Gecko/20071008 Firefox/2.0.0.8",
                "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.1.4322; .NET CLR 2.0.50727; .NET CLR 3.0.04506.30)",
                "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; MRSPUTNIK 1, 8, 0, 17 HW; MRA 4.10 (build 01952); .NET CLR 1.1.4322; .NET CLR 2.0.50727)",
                "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)",
                "Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.8.1.9) Gecko/20071025 Firefox/2.0.0.9"
            );

    public static $periodSearchArray = array('час', 'минут', 'cейчас', 'пользователь', 'только что' );

    public function __construct() {
        set_time_limit(240);
        $this->weekAgoTS = DateTimeWrapper::Now()->modify('-1 week')->format('U');
    }

    public function multiget( $badooUserIds, $short_name = false ) {
        $urls_pack = array();
        $result    = array();
        $useragent = self::$allUseragents[ array_rand( self::$allUseragents )];
        /** url по id выглядит как badoo.com/0xxxx */
        $id_prefix = $short_name ? '' : '0' ;

        for( $i = 0; $i < count( $badooUserIds ); $i += self::MG_MAX_THREADS ) {
            $urls_pack[] = array_slice( $badooUserIds, $i, self::MG_MAX_THREADS );
        }

        foreach( $urls_pack as $pack ) {
            sleep(1);
            $mh = curl_multi_init();
            unset( $conn );
            foreach ( $pack as $i => $id )
            {
                $conn[$i]=curl_init( self::BADOO_USER_URL . $id_prefix . trim( $id ));
                echo self::BADOO_USER_URL . $id_prefix . trim( $id ).'<br>';
                curl_setopt($conn[$i], CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($conn[$i], CURLOPT_TIMEOUT, self::MG_CONNECT_TIME );
                curl_setopt($conn[$i], CURLOPT_USERAGENT, $useragent );
                curl_multi_add_handle ( $mh, $conn[ $i ]);
            }
            do {
                curl_multi_exec( $mh, $active );
                sleep( 0.01 ); }
            while ( $active );

            foreach ( $pack as $i => $id )
            {
                $result[$id] = curl_multi_getcontent( $conn[ $i ]);
                curl_close( $conn[$i] );
            }
            curl_multi_close( $mh );

        }

        return $result;
    }

    public function isVip( $page ) {
        return (boolean) strpos( $page, '"custom-spp"');
    }

    public function visitedToday( $status ) {
        $status = mb_strtolower( $status );

        foreach (self::$periodSearchArray as $period ) {
            if( mb_strpos( $status, $period )) {
                return true;
            }
        }
        return false;
    }

    /** @var $BadooUser BadooUser */
    public function parseProfile( $BadooUser, $page ) {
        if (!preg_match('/class="psnc_str">(.*?)</', $page, $matches )) {
            return false;
        }

        $status = $matches[1];
        $now = time();

        $BadooUser->updated_at = $now;
        if ($this->visitedToday( $status )) {
            $BadooUserVisit = new BadooUsersVisit( $BadooUser->external_id, time());
            BadooUsersVisitFactory::Add($BadooUserVisit);
        }
        $IsNowVip = $this->isVip( $page );

        if ( $IsNowVip ^ $BadooUser->is_vip ) {
            $BadooUser->is_vip = $IsNowVip;
            $BadooUserVip = new  BadooUsersVip( $BadooUser->external_id, $now, $IsNowVip);
            BadooUsersVipFactory::Add( $BadooUserVip );
        }

        return true;
    }

    public function get_ts_from_period( $period ) {
        $now = DateTimeWrapper::Now();
        switch($period) {
            case 'today':
                return $now->modify('midnight');
            case 'yesterday':
                return $now->modify('-1 day')->modify('midnight');
            case 'week':
                return $this->weekAgoTS;
            default:
                return new DateTimeWrapper($period);
        }
    }

    public function getShortName( $page ) {
        if( preg_match('/href="http:\/\/badoo.com\/(..{1,25})\/"/', $page, $matches)) {
            return $matches[1];
        }
        return false;
    }
}
