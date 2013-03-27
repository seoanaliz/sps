<?php
    Package::Load( 'SPS.Site' );
    
    /**
     * TextRender
     * @package SPS
     * @subpackage Site
     * @author eugeneshulepin
     */
    class TextRender implements IModule {

        /**
         * Init Text Renderer
         */
        public static function Init(DOMNodeList $params) {
            Template::$Functions["reldate"] = 'TextRender::FullDateString( \\1 )';
            Template::$Functions["reldateshort"] = 'TextRender::ShortDateString( \\1 )';
        }

        /**
         * @param DateTimeWrapper $date
         * @return string
         */
        public static function FullDateString( $date ) {
            return strftime( "%#d %B %Y", $date->format( "U" ) );
        }

        /**
         * @param DateTimeWrapper $date
         * @return string
         */
        public static function ShortDateString( $date ) {
            return strftime( "%#d %B", $date->format( "U" ) );
        }
    }
?>