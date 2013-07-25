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
                        self::updateUserDataFromApi($answer['user_id'], $answer['access_token']);
                    }
                    AuthVkontakte::Logout(); // чтобы очистить куку клиентской авторизации (vk_app_xxxxxx)
                    AuthVkontakte::Login($answer['user_id']);
                }
            }

            Response::SetString('redirect', $redirectUrl);
            return 'redirect';
        }

        protected static function updateUserDataFromApi($vkId, $accessToken) {
            $code = 'return {
                "permissions": API.getUserSettings(),
                "publics": API.groups.get({filter: "admin"})
            };';
            $wasError = false;
            try {
                $apiAnswer = VkHelper::api_request('execute',
                        array(
                            'uid' => $vkId,
                            'access_token' => $accessToken,
                            'code' => $code
                        ));
            } catch (Exception $E) {
                $wasError = true;
                error_log('login VK API error: ' . $E->getMessage());
            }
            if (!$wasError) {
                if (property_exists($apiAnswer, 'permissions') && property_exists($apiAnswer, 'publics')) {
                    if (($apiAnswer->permissions & VkHelper::PERM_GROUPS) &&
                        ($apiAnswer->permissions & VkHelper::PERM_GROUP_STATS) &&
                        ($apiAnswer->permissions & VkHelper::PERM_OFFLINE) &&
                        ($apiAnswer->permissions & VkHelper::PERM_WALL)
                    ) {
                        $existingToken = AccessTokenFactory::GetOne( array(
                            'vkId' => $vkId,
                        ));

                        if (!$existingToken) {
                            self::addAccessToken($vkId, $accessToken);
                        } else {
                            $existingToken->createdAt   = DateTimeWrapper::Now();
                            $existingToken->accessToken = $accessToken;
                            $existingToken->version     = AuthVkontakte::$Version;
                            AccessTokenFactory::Update($existingToken);
                        }
                        EditorsUtility::SetTargetFeeds($vkId, $apiAnswer->publics);
                    } else {
                        error_log('login permissions problem for user: ' . $vkId . ' - permissions are: ' . $apiAnswer->permissions . ' instead of: ' . (VkHelper::PERM_GROUPS + VkHelper::PERM_GROUP_STATS + VkHelper::PERM_OFFLINE));
                    }
                }
            }
        }

        protected static function addAccessToken($vkId, $accessToken) {
            $accessTokenData = new AccessToken();
            $accessTokenData->vkId = $vkId;
            $accessTokenData->accessToken = $accessToken;
            $accessTokenData->appId = AuthVkontakte::$AppId;
            $accessTokenData->createdAt = DateTimeWrapper::Now();
            $accessTokenData->statusId  = StatusUtility::Enabled;
            $accessTokenData->version   = AuthVkontakte::$Version;
            return AccessTokenFactory::Add($accessTokenData);
        }
    }
?>