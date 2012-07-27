<?php
    Package::Load( 'SPS.VK' );

    /**
     * VKCheckAppAuth Action
     * @package    SPS
     * @subpackage VK
     * @author     Shuler
     */
    class VKCheckAppAuth {

        public function Execute() {
            $silent     = Request::getBoolean('silent');
            $api_id     = Request::getInteger('api_id');
            $viewer_id  = Request::getInteger('viewer_id');
            $secret     = AuthVkontakte::$AuthSecret;
            $auth_key   = Request::getString('auth_key');
            $auth_key_trust = md5($api_id . '_' . $viewer_id . '_' . $secret);

            if (empty($silent)) {
                if ($auth_key != $auth_key_trust) {
                    return 'empty';
                }
            } else {
                $viewer_id = -1;
                $author = Session::getObject('Author');
                if (!empty($author->vkId)) {
                    $viewer_id = $author->vkId;
                }
            }

            // ищем чувака в базе
            $author = AuthorFactory::GetOne(
                array('vkId' => $viewer_id)
            );

            if (empty($author)) {
                if (!empty($silent)) {
                    echo ObjectHelper::ToJSON(array('error' => 'auth'));
                    die();
                }
                return 'empty';
            }

            // определяем паблики, к которым у чувака есть доступ вообще
            $targetFeedIds = $author->targetFeedIds;
            if (empty($targetFeedIds)) {
                $targetFeedIds = array(-1 => -1); //это важно для дальнейших запросов к базе
            }

            Response::setObject('__Author', $author);
            Session::setObject('Author', $author);
            Session::setInteger('author_id', $viewer_id);
            Session::setArray('targetFeedIds', $targetFeedIds);
        }
    }
?>