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
        $publishers = PublisherFactory::Get();
        $errors = 0;
        foreach( $publishers as $publisher ) {
            $res = VkHelper::get_vk_time( $publisher->vk_token );
            if( isset( $res->error )) {
                AuditUtility::CreateEvent(
                      'accessTokenDead'
                    , 'publisher'
                    , $publisher->publisherId
                    , $publisher->name . " ( " . $publisher->vk_id . " )" . " was presumably banned ");
                $errors ++;
            }
            sleep(0.5);
        }


        if ( $errors ) $this->send_alert();

    }

    private function send_alert() {
        $params = array(
            'uid'           =>   670456,
            'message'       =>  'rise and shine! tokens are dead.',
            'access_token'  =>  self::ALERT_TOKEN,
        );
        VkHelper::api_request( 'messages.send', $params );
    }
}
