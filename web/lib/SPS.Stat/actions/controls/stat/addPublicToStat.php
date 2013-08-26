<?php
/**
 * setCpp      Action
 * @package    SPS
 * @subpackage Stat
 * @author     kulikov
 * @task       #18899
 */
class addPublicToStat {

    public function Execute() {
        $result = array ( 'success' => false );
        $userVkId = AuthVkontakte::IsAuth();
        if ( $userVkId ) {
            $publicId        =   Request::getString('externalPublicIdString');

            $userAccessToken = AccessTokenFactory::GetOne(array('vkId' => $userVkId, 'appId' => AuthVkontakte::$AppId ));
            if (!$userAccessToken ) {
                $result['message'] = 'Relogin please';
                die( ObjectHelper::ToJSON($result));
            }
            try {
                $publicInfo      =   StatPublics::getPublicInfo( $publicId, $userAccessToken->accessToken );
            } catch ( Exception $e ) {
                $result['message'] = 'Wrong data';
                die( ObjectHelper::ToJSON($result));
            }

            if ( $publicInfo && $publicInfo->quantity > WrTopics::STAT_QUANTITY_LIMIT ) {
                if ( $publicInfo ) {
                    if ( !isset( $publicInfo->admin_level) || $publicInfo->admin_level <= 1 ) {
                        $result['message'] = 'Access denied';
                        die( ObjectHelper::ToJSON($result));
                    }

                    $check  = VkPublicFactory::Get( array( 'vk_id' => $publicInfo->gid ));
                    if ( empty ( $check )) {
                        $public = new VkPublic();
                        $public->vk_id  = $publicInfo->gid;
                        $public->active = true;
                        $public->active = true;
                        $public->inLists= false;
                        $public->ava    = $publicInfo->photo_medium;
                        $public->name   = $publicInfo->name;
                        $public->closed = $publicInfo->is_closed;
                        $public->is_page    = $publicInfo->type == 'page';
                        $public->quantity   = $publicInfo->members_count;

                        $result['success'] = VkPublicFactory::Add( $public );

                    } elseif( $publicInfo && $publicInfo->quantity <= StatPublics::STAT_QUANTITY_LIMIT ) {
                        $result['message'] = 'your community is too small';
                    } else {
                        $result['message'] = 'Already exsits';
                    }
                } else {
                    $result['message'] = 'Wrong data';
                }
            }

        }
        echo ObjectHelper::ToJSON($result);
    }
}
?>