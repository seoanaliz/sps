<?php
    Package::Load( 'SPS.VK' );

    /**
     * VKCheckAppAuthSilent Action
     * @package    SPS
     * @subpackage VK
     * @author     Shuler
     */
    class VKCheckAppAuthSilent {

        /**
         * Entry Point
         */
        public function Execute() {
            $author = Session::getObject('Author');
            if (empty($author)) {
                echo ObjectHelper::ToJSON(array('error' => 'auth'));
                die();
            }
        }
    }
?>