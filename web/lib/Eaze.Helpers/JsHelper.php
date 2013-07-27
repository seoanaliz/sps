<?php
    /**
     * Helps to render JS
     *
     * @package Eaze
     * @subpackage Helpers
     * @since 1.3
     * @author sergeyfast
     * @static
     */
    class JsHelper extends AssetHelper {

        /**
         * Minify scripts or not
         * @var bool
         */
        public static $Minify = true;

        /**
         * Max Group Size for minify
         * @var int
         */
        public static $MaxGroups = 20;

        /**
         * Default Hostname
         * @var string default hostname
         */
        public static $Hostname = 'static';

        /**
         * Current Type
         * @var string
         */
        private static $type = self::JS;

        /**
         * Variables
         * @var array
         */
        private static $vars = array();

        /**
         * Add File
         * @param string $file single JS file
         * @param string $mode browser mode
         */
        public static function PushFile( $file, $mode = self::AnyBrowser ) {
            parent::addFile( self::$type, $file, $mode );
        }


        /**
         * Add multiple JS file
         * @param string[] $files array of JS files
         * @param string $mode browser mode
         */
        public static function PushFiles( $files, $mode = self::AnyBrowser ) {
            foreach ( $files as $file ) {
                self::PushFile( $file, $mode );
            }
        }


        /**
         * Add multiple JS grouped files
         * @param $groups array of js grouped files
         * @return void
         */
        public static function PushGroups( $groups ) {
            foreach( $groups as $mode => $files ) {
                self::PushFiles( $files, $mode );
            }
        }


        /**
         * Add JS line to JS code
         * @param string $line
         * @param string $mode browser mode
         */
        public static function PushLine( $line, $mode = self::AnyBrowser ) {
            parent::addLine( self::$type, $line, $mode );
        }


        public static function AddVar($varName, $value) {
            self::$vars[$varName] = $value;
        }


        /**
         * Flush All Modes
         * @return string
         */
        public static function Flush() {
            if ( self::$PostProcess ) {
                $result =  self::setFlushPoint( self::$type, self::$Minify, self::$Hostname, self::$MaxGroups );

                $result .= self::FlushVars();

                return $result;
            }

            $result = '';
            foreach ( self::$BrowserModes as $mode ) {
                $result .= parent::flushMode( self::$type, $mode, self::$Minify, self::$Hostname, self::$MaxGroups ) . "\n";
            }

            $result .= self::FlushVars();

            return $result;
        }


        public static function FlushVars(){
            $result = '';
            if (self::$vars) {
                $result = PHP_EOL.'<script type="text/javascript">';
                foreach (self::$vars as $varName=>$value){
                    $result .= PHP_EOL.'var ' . $varName . ' = ' . self::FlushVarValue($value) . ';' . PHP_EOL;
                }
                $result .= PHP_EOL.'</script>'.PHP_EOL;
            }

            return $result;
        }

        public static function FlushVarValue($value){
            if (is_int($value) || is_float($value)) {
                return $value;
            }

            if (is_string($value)) {
                return '"' . $value . '"';
            }

            if (is_array($value)) {
                $array = '{';
                foreach ($value as $k => $v) {
                    $array .= '"' . $k . '": ' . self::FlushVarValue($v) . ',';
                }
                return $array . '}';
            }

            if (is_null($value)) {
                return 'none';
            }

            if (is_bool($value)) {
                return $value ? 'true' : 'false';
            }

        }


        /**
         * Remove File from Queue
         * @static
         * @param string $file
         * @param string $mode
         * @return bool
         */
        public static function RemoveFile( $file, $mode = self::AnyBrowser ) {
            return parent::deleteFile( self::$type, $file, $mode );
        }


        /**
         * Init Helper
         * @static
         * @param bool   $minify
         * @param int    $maxGroups
         * @param string $hostname
         * @return void
         */
        public static function Init( $minify = true, $maxGroups = 25, $hostname = 'static' ) {
            self::$Minify    = $minify;
            self::$MaxGroups = $maxGroups;
            self::$Hostname  = $hostname;
        }

        public static function includeCombined($files) {
            $revision = AssetHelper::GetRevision();
            $jsDir = __ROOT__ . '/shared/js/';
            $cacheDir = $jsDir . 'cache/';
            $cacheFilename = 'js_' . md5(join('_', $files) . $revision) . '.js';
            if (Site::IsDevel() || !file_exists($cacheDir . $cacheFilename)) {
                $combined = array ();
                foreach ($files as $file) {
                    $combined []= '//# ' . $file;
                    $combined []= file_get_contents($jsDir . ltrim($file, '/')) . "\n";
                }
                file_put_contents($cacheDir . $cacheFilename, join("\n", $combined));
            }
            echo '<script type="text/javascript" src="' . Site::GetWebPath('/shared/js/cache/' . $cacheFilename) . '"></script>';
        }
    }
?>