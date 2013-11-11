<?php
/**
 * Created by akalie.
 */

class SociateHelper {
    const SociateToken  = '9d66cd77744a4e3d966d5980a24abaf2';
    const SociateApiUrl = 'http://sociate.ru/api/v1.0/';
    //список пабликов, подключенных к sociate todo в админку
    public static  $sociateTargetFeedIds = [
         41
        ,40
        ,69
        ,56
        ,46
        ,70
        ,14
        , 3
        ,36
        ,38
        ,20
        ,24
        ,59
        ,58
        ,60
        , 5
        ,39
        ,48
        ,17
        ,10
        ,72
        ,13
        ,18
        ,43
        ,35
        ,68
        ,19
        , 7
        , 6
        ,16
        ,12
        ,37
        ,71
        ,62
        , 4
        ,47
        ,34
        ,23
        ,57
        ,45
        , 9
        ,51
        ,49
        ,11
        , 8
        ,31
        ,42
        ,61
        ,25
        ,21
        ,67
        ,73
    ];
    /**
     * проверяем, занято ли данное время в Sociate
     * если да - возвращает масив ['from'=>, 'to'=>] - интервал занятости
     * @var $timestamp int
     * @var $vkPublivId int
     * @return DateTimeWrapper[] | bool
     * @throws Exception;
     */
    public static function checkIfIntervalOccupied($timestamp, $vkPublicId) {
        $params = [
            'timestamp'     =>  $timestamp,
            'gid'           =>  $vkPublicId,
            'date_format'   =>  'timestamp'
        ];
        $tryes = 0;
        //иногда API подвисает...
        while($tryes++ < 3) {
            try {
                $result = SociateHelper::apiRequest('autopost.canPost', $params);
                break;
            } catch (Exception $e) {
                if ( $tryes == 3) {
                    throw $e;
                }
                sleep(0.4);
            }
        }
        if ( isset($result->allowed) && $result->allowed ) { //если заданное время не занято в sociate
            return false;
        } elseif( !isset($result->timestamp)) {
            throw new Exception('strange response in sociate request: ' . json_encode($result));
        }
        $ocupiedToString = date('r',$result->timestamp);
        $to   = new DateTimeWrapper(  $ocupiedToString );
        $from = (new DateTimeWrapper( $ocupiedToString ))->modify('-1 hour');
        return ['to'=> $to, 'from' => $from];
    }

    public static function apiRequest( $method, $params ) {
        $params['access_token'] = SociateHelper::SociateToken;
        $params['method']       = $method;

        $paramsRow = http_build_query( $params );

        $ch = curl_init( SociateHelper::SociateApiUrl .'?' . $paramsRow );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT , 20 );
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $res = json_decode( curl_exec($ch));
        if ( isset($res->error) || !isset( $res->response )) {
            throw new Exception('we got a problem with sociate request: ' . json_encode($res));
        }
        return $res->response;
    }
}