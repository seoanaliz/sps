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

            if (!empty($callback)) {
                echo "$callback (" . ObjectHelper::ToJSON($result) . ");";
            } else {
                echo ObjectHelper::ToJSON($result);
            }
        }
    }
?>