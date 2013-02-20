<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 10.09.12
 * Time: 12:32
 * To change this template use File | Settings | File Templates.
 */
class TokenChecker
{
    public function execute() {
        $publishers = PublisherFactory::Get();
        foreach( $publishers as $publisher ) {
            $res = VkHelper::get_vk_time( $publisher->vk_token );
            if( isset( $res->error )) {
                AuditUtility::CreateEvent(
                      'accessTokenDead'
                    , 'publisher'
                    , $publisher->publisherId
                    , $publisher->name . " ( " . $publisher->vk_id . " )" . " was presumably banned ");
            }
            sleep(0.5);

        }
    }


}
