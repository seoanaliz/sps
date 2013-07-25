<?php
/**
 * SaveArticleControl Action
 * @package    SPS
 * @subpackage Site
 * @author     Shuler
 */
class SaveAccessTokenControl extends BaseControl
{

    /**
     * Entry Point
     */
    public function Execute()
    {
        $result = array(
            'success' => false
        );
        $token   =   Request::getString('accessToken');
        $vk_id   =   Request::getString('vkId');
        $app_id  =   Request::getInteger('appId');
        $version =   Request::getInteger('version');

        if( !$token || !$vk_id || !$app_id ) {
            die( ObjectHelper::ToJSON( $result));
        }

        $accessToken = AccessTokenFactory::GetOne( array('vkId'=> $vk_id, 'appId' => $app_id ));
        if (!empty( $accessToken)) {
            $accessToken->accessToken = $token;
            $result['success'] = AccessTokenFactory::Update( $accessToken);
        } else {
            $accessToken = new AccessToken();
            $accessToken->vkId = $vk_id;
            $accessToken->accessToken = $token;
            $accessToken->appId = $app_id;
            $accessToken->createdAt = DateTimeWrapper::Now();
            $accessToken->statusId  = StatusUtility::Enabled;
            $accessToken->version   = $version ? $version : 1;
            $result['success'] = AccessTokenFactory::Add( $accessToken);
        }

        die( ObjectHelper::ToJSON( $result));
    }

}

