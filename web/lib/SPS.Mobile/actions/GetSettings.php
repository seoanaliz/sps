<?php
    Package::Load( 'SPS.Mobile' );
    
    /**
     * GetSettings Action
     * @package SPS
     * @subpackage Mobile
     * @author eugeneshulepin
     */
    class GetSettings {
    
        /**
         * Entry Point
         */
        public function Execute() {
            $response['response'] = array(
                'min_version'       => '1.0.0',
                'max_version'       => '2345662354',
                'about_app_url'     => 'http://socialboard.ru/mobile/about',
                'wall_post_message' => '',
            );

            header('Content-type: application/json');
            echo ObjectHelper::ToJSON($response);
        }
    }
?>