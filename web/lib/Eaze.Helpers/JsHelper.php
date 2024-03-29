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

        /**
         * Принимает список имён переменных из Response, возвращает обёрнутый в тег <script> код,
         * создающий в js переменные с этими же именами и значениями
         * 
         * Пример:
         * @example
         *      JsHelper::Vars('gridTypes', 'targetFeeds');
         * Вернёт:
         *       <script type="text/javascript">
         *           var gridTypes = {"content":...};
         *           var targetFeeds = {"26":{"targetFeedId":...};
         *       </script>
         * @return string
         */
        public static function Vars() {
            $neededKeys = array_flip(func_get_args());
            $all = Response::getParameters();
            $foundVars = array_intersect_key($neededKeys, $all);
            
            // сообщим, если не нашли какие-то из желаемых переменных
            if (count($foundVars) !== count($neededKeys)) {
                error_log('[Output vars] ' . __METHOD__ . '(): some values that we wanted are missing: [' . join(', ', array_keys(array_diff_key($neededKeys, $all))) . ']');
            }

            $outputParts = array ();
            foreach ($foundVars as $name => $_) {
                $outputParts []= "var $name = " . json_encode($all[$name]) . ';';
            }

            return $outputParts ? '<script type="text/javascript">'. "\n    " . join("\n    ", $outputParts) ."\n</script>\n"  : '';
        }
    }
?>