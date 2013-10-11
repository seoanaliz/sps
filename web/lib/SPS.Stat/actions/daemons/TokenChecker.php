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

        $feeds = TargetFeedFactory::Get(array('isOur'=>true, 'type' => 'vk' ));
        echo count( $feeds) . '<br>';
        foreach ( $feeds as $feed ) {
            echo '<br><br> Работаем с пабликом ' . $feed->externalId . '(' .$feed->targetFeedId.')<br>' ;
            $userFeeds  =  UserFeedFactory::Get(array( 'targetFeedId' => $feed->targetFeedId ));
            $userFeeds  =  ArrayHelper::Collapse( $userFeeds, 'vkId', $convertToArray = false);
            $vkIds = array_keys($userFeeds);
            if (empty($vkIds)) {
                echo 'пустые vkId для tf ' .$feed->targetFeedId . ' <br>';
                continue;
            }

            $botAuthors =  AuthorFactory::Get(
                array(
                    'vkIdIn' => $vkIds,
                    'isBot' =>  true
                ));

            if (empty($botAuthors)) {
                echo 'пустые $botAuthors для tf ' .$feed->targetFeedId . ' <br>';
                continue;
            }
            $botAuthors =  ArrayHelper::Collapse( $botAuthors, 'vkId', $convertToArray = false);
            $tokens     =  AccessTokenFactory::Get(array(
                'vkIdIn'  => array_keys( $botAuthors ),
                'version' => AuthVkontakte::$Version
            ));

            echo 'всего ботов: ' . count( $tokens ) . '<br>';
            foreach ( $tokens as $token ) {
                echo '<a href="http://vk.com/id' . $token->vkId . '">' . $token->vkId . '</a><br>';
                $params = array(
                    'group_ids'     =>   $feed->externalId,
                    'access_token'  =>   $token->accessToken
                );

                $res = VkHelper::api_request('groups.getById', $params, 0 );
                sleep(0.4);
                if( isset( $res->error)) {
                    echo 'ломанный токен ' . $token->accessToken . ' http://vk.com/id' . $token->vkId . '<br>';
                    $errors ++;
                    continue;
                }
                if( !isset( $res[0]->is_admin ) or !$res[0]->is_admin ){
                    echo 'ломанный токен ' . $token->accessToken . ' http://vk.com/id' . $token->vkId . '<br>';
                }
                if( !isset( $bots_loading[$token->vkId] )) {
                    $bots_loading[$token->vkId] = 0;
                }
                $bots_loading[$token->vkId]++;
            }
        }
        echo 'end';
        $mor = $this->checkMorituri();
        if ( $errors || $mor ) {
            VkHelper::send_alert('rise and shine! Tokens are dead ' . $mor , 670456 );
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

    private function checkMorituri() {
        $errors = '';
        $tokens = AccessTokenUtility::getMorituri();
        foreach( $tokens as $token ) {
            $res = VkHelper::get_vk_time($token->accessToken);
            if (isset($res->error)) {
                $errors .= $token->vkId . ' сдох(, ';
            }
        }

        return $errors;
    }
}
