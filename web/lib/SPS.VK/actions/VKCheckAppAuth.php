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
            $api_id     = Request::getInteger('api_id');
            $viewer_id  = Request::getInteger('viewer_id');
            $secret     = 'X1zsnZdfoL1ywzRODpEg';
            $auth_key   = Request::getString('auth_key');
            $auth_key_trust = md5($api_id . '_' . $viewer_id . '_' . $secret);

            if ($auth_key != $auth_key_trust) {
                return 'empty';
            }

            // ищем чувака в базе
            $author = AuthorFactory::GetOne(
                array('vkId' => $viewer_id)
            );

            if (empty($author)) {
                return 'empty';
            }

            // определяем паблики, к которым у чувака есть доступ вообще
            if (!empty($author->targetFeedIds)) {
                $targetFeedIds = explode(',', $author->targetFeedIds);
            }
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