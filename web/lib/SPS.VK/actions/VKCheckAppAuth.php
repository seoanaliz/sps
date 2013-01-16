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
            if (!empty(Page::$RequestData[1]) && Page::$RequestData[1] == 'editor/') {
                $editor = Session::getObject('Editor');
                if (empty($editor)) {
                    die();
                } else {
                    Response::setBoolean('__editorMode', true);
                    return true;
                }
            }

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
                $viewer_id = Session::getInteger('authorId');;
            }

            $viewer_id = 49158237;

            // ищем чувака в базе
            if (!empty($viewer_id)) {
                $author = AuthorFactory::GetOne(
                    array('vkId' => $viewer_id)
                );
            } else {
                $author = null;
            }

            if (empty($author)) {
                if (!empty($silent)) {
                    //echo ObjectHelper::ToJSON(array('error' => 'auth'));
                    die();
                }
                return 'empty';
            }

            Response::setObject('__Author', $author);
            Session::setObject('Author', $author);
            Session::setInteger('authorId', $viewer_id);
        }
    }
?>