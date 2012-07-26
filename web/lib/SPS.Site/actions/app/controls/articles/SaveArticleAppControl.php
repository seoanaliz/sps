<?php
    Package::Load( 'SPS.Site' );

    /**
     * SaveArticleControl Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class SaveArticleAppControl {

        /**
         * Entry Point
         */
        public function Execute() {
            $result = array(
                'success' => false
            );
            echo ObjectHelper::ToJSON($result);
        }
    }
?>