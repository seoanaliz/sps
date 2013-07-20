<?php
/**
 * Created in Topface.
 * User: akalie
 * Date: 10.09.12
 * Time: 12:32
 */
class TokenChecker
{
    public function execute() {
        $errors = 0;

        //токены издателей
        $publishers = PublisherFactory::Get(array('statusId' => 1));
        foreach( $publishers as $publisher ) {
            $errors += $this->check_token( $publisher->vk_token, $publisher->vk_id, 'sb');
            sleep(1);
        }

        //служебные
        $tokens = VkHelper::get_all_service_tokens();
        foreach( $tokens as $vk_id=>$token ) {
            $errors += $this->check_token( $token, $vk_id, 'serv');
            sleep(1);
        }
        if ( $errors ) {
            VkHelper::send_alert('rise and shine! Tokens are dead', 670456 );
        }
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

}
