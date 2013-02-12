<?php
    Package::Load( 'SPS.Site' );

    /**
     * ParseUrlControl Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class ParseUrlControl {

        /**
         * Entry Point
         */
        public function Execute() {
            $result = UrlParser::Parse(Request::getString('url'));
            $callback = Request::getString('callback');

            $result = mb_check_encoding($result, 'UTF-8') ? $result : utf8_encode($result);

            if (!empty($callback)) {
                echo "$callback (" . ObjectHelper::ToJSON($result) . ");";
            } else {
                echo ObjectHelper::ToJSON($result);
            }
        }
    }
?>