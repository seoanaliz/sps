<?php
    Package::Load( 'SPS.VK' );

    /**
     * VKLoginRedirect Action
     * @package    SPS
     * @subpackage VK
     * @author     Eugene Kulikov
     */
    class VKLoginRedirect {

        public function Execute() {
            $code = Request::getString('code');
            $redirectUrl = Request::getString('to') ?: '/';
            if ($code) {
                $protocol = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') ? 'https' : 'http';
                $tokenGetUrl = 'https://oauth.vk.com/access_token'.
                    '?client_id='. AuthVkontakte::$AppId .
                    '&client_secret='. AuthVkontakte::$Password .
                    '&code='. $code .
                    '&redirect_uri=' . urlencode($protocol . '://' . trim(Request::GetHTTPHost(), '/') . '/vk-login/?to=' . $redirectUrl);
                $answer = json_decode(
                    VkHelper::connect($tokenGetUrl, $setCookie = null, $usePost = null, $includeHeaderInOutput = false),
                    $array=true
                );
                if (isset($answer['user_id'])) {
                    if (isset($answer['access_token'])) {
                        $code = 'return {"permissions": API.getUserSettings(), "publics": API.groups.get({filter: "admin"})};';

                        $wasError = false;
                        try {
                            $apiAnswer = VkHelper::api_request(
                                    'execute',
                                    array(
                                        'uid' => $answer['user_id'],
                                        'access_token' => $answer['access_token'],
                                        'code' => $code
                                    ));
                        } catch (Exception $E) {
                            $wasError = true;
                            Logger::Error('login VK API error: ' . $E->getMessage());
                        }
                        if (!$wasError) {
                            // object(stdClass)#228 (2) { ["permissions"]=> int(1376256) ["publics"]=> array(1) { [0]=> int(27421965) } } ﻿
                            if (property_exists($apiAnswer, 'permissions') && property_exists($apiAnswer, 'publics')) {
                                if (($apiAnswer->permissions & VkHelper::PERM_GROUPS) &&
                                    ($apiAnswer->permissions & VkHelper::PERM_GROUP_STATS) &&
                                    ($apiAnswer->permissions & VkHelper::PERM_OFFLINE)
                                ) {
                                    $accessToken = new AccessToken();
                                    $accessToken->vkId = $answer['user_id'];
                                    $accessToken->accessToken = $answer['access_token'];
                                    $accessToken->appId = AuthVkontakte::$AppId;
                                    $accessToken->createdAt = DateTimeWrapper::Now();
                                    $accessToken->statusId  = StatusUtility::Enabled;
                                    AccessTokenFactory::Add($accessToken);
                                    
                                    
                                } else {
                                    Logger::Error('login permissions problem for user: ' . $answer['user_id'] . ' - permissions are: ' . $apiAnswer->permissions . ' instead of: ' . (VkHelper::PERM_GROUPS + VkHelper::PERM_GROUP_STATS + VkHelper::PERM_OFFLINE));
                                }
                            }
                        }
                    }
                    AuthVkontakte::Logout();
                    AuthVkontakte::Login($answer['user_id']);
                }
            }

            Response::SetString('redirect', $redirectUrl);
            return 'redirect';
        }
    }
?>