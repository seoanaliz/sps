<?php
/**
 * Created in Topface.
 * User: akalie
 * Date: 10.09.12
 * Time: 12:32
 */
class TokenChecker
{

    const ALERT_TOKEN = "9a52c2c5ad3c3a0dba10d682cd5e70e99aea7ca665701c2f754fb94e33775cf842485db7b5ec5fb49b2d5";
    public function execute() {
        $errors = 0;

        //токены издателей
        $publishers = PublisherFactory::Get(array('status_id' => 1));
//        foreach( $publishers as $publisher ) {
//            $errors += $this->check_token( $publisher->vk_token, $publisher->vk_id, 'sb');
//            sleep(1);
//        }

        //служебные
        $tokens = VkHelper::get_all_service_tokens();
        print_r($tokens);
        foreach( $tokens as $vk_id=>$token ) {
            $errors += $this->check_token( $token, $vk_id, 'serv');
            sleep(1);
        }
        if ( $errors ) $this->send_alert();

    }

    private function check_token( $token, $vk_id, $type )
    {
        $res = VkHelper::get_vk_time( $token );
        if( isset( $res->error )) {
            print_r($res->error->error_msg);
            echo '<br>' . $vk_id . '<br><br>';
            AuditUtility::CreateEvent(
                  'accessTokenDead'
                , 'publisher'
                , $vk_id
                ,  " Bot " . $vk_id . " was presumably banned (" . $type .  ")");
            return 1;
        }
        return 0;
    }

    private function send_alert() {
        $params = array(
            'uid'           =>   670456,
            'message'       =>  'rise and shine! tokens are dead. ' . md5(time()) ,
            'access_token'  =>  self::ALERT_TOKEN,
        );
        VkHelper::api_request( 'messages.send', $params );
    }
}
