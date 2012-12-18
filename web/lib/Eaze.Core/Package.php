<?php
    if ( !defined( 'CONFPATH_CACHE' ) ) {
        define( 'CONFPATH_CACHE', 'cache' );
    }

    /**
     * Package Loader
     *
     * @package    Eaze
     * @subpackage Core
     * @author     sergeyfast
     */
    class Package {

        /**
         * File flag for WITH_PACKAGE_COMPILE check
         */
        const CompiledEaze = 'compiled.eaze';

        /**
         * WITH_PACKAGE_COMPILE constant name
         */
        const WithPackageCompile = 'WITH_PACKAGE_COMPILE';

        /**
         * Loaded Packages
         *
         * @var array
         */
        public static $Packages = array();

        /**
         * Loaded Files
         *
         * @var array
         */
        public static $Files = array();

        /**
         * Structure of LIB Directory
         *
         * @var array
         */
        public static $LibStructure = array();


        /**
         * Loaded Classes
         * @var array system = before uri, uri = after uri
         */
        public static $LoadedClasses = array( 'system' => array(), 'uri' => array() );

        /**
         * Current Uri
         * @var array
         */
        public static $CurrentUri;

        /**
         * Force disable package compiling
         * @var bool
         */
        private static $ForceDisableCompile = false;

        /**
         * Begin URI
         * @param $uri
         */
        public static function BeginUri( $uri ) {
            self::$CurrentUri = $uri;

            // include uri
            if ( Package::WithPackageCompile() ) {
                $file = self::GetCompiledFilename( 'type', true );
                if ( is_file( $file ) ) {
                    require_once $file;
                }
            }
        }


        /**
         * Get Compiled Filename from Cache
         * @param string $type system | uri
         * @param bool   $withRealPath
         * @return string
         */
        public static function GetCompiledFilename( $type, $withRealPath = false ) {
            $fileType = $type == 'system' ? $type : md5( self::$CurrentUri );
            $result   = sprintf( 'package_%s.php', $fileType );

            if ( $withRealPath ) {
                $result = __ROOT__ . '/' . CONFPATH_CACHE . '/' . $result;
            }

            return $result;
        }


        /**
         * Include System Package File
         */
        public static function IncludeSystem() {
            $file = self::GetCompiledFilename( 'system', true );
            if ( is_file( $file ) ) {
                require_once $file;
            }
        }


        /**
         * Save Loaded classes to cache file
         */
        public static function Shutdown() {
            foreach ( self::$LoadedClasses as $type => $classes ) {
                if ( $classes ) {
                    Logger::Checkpoint();

                    $buffer = '';
                    foreach ( $classes as $filename ) {
                        $filePath = self::$LibStructure[$filename];
                        $content  = file_get_contents( $filePath );
                        $content  = rtrim( trim( trim( $content ), PHP_EOL ), '?>' );
                        $buffer  .= $content . '?>';
                    }

                    file_put_contents( self::GetCompiledFilename( $type, true ), $buffer, FILE_APPEND | LOCK_EX );
                    Logger::Info( 'Writing %d classes to %s', count( $classes ), $type );
                }
            }
        }


        /**
         * Load Package
         *
         * @param string $name
         * @return bool
         */
        public static function Load( $name ) {
            return true;
        }


        /**
         * Check PHP Filename and existence
         * @static
         * @param  string $file       filename
         * @param  string $packageDir directory path with trailing slash
         * @return bool
         */
        public static function CheckPHPFilename( $file, $packageDir ) {
            if ( $file == '.'
                || $file == '..'
                || strpos( $file, '.php' ) === false
                || !is_file( $packageDir . $file )
            ) {
                return false;
            }

            return true;
        }


        /**
         * Init Constants __LIB__ & __ROOT__
         *
         * @return void
         */
        public static function InitConstants() {
            if ( !defined( '__LIB__' ) ) {
                define( '__LIB__', realpath( dirname( __FILE__ ) . '/..' ) );
            }

            if ( !defined( '__ROOT__' ) ) {
                define( '__ROOT__', realpath( dirname( __FILE__ ) . '/../..' ) );
            }
        }


        /**
         * Load Lib Directory Structure
         *
         * @return void
         */
        private static function initLibStructure() {
            $libDir = __LIB__ . '/';

            /** @var $libInfo DirectoryIterator */
            /** @var $packageInfo DirectoryIterator */
            /** @var $subPackageInfo DirectoryIterator */

            Logger::Checkpoint();
            $libIterator = new FilesystemIterator( $libDir, FilesystemIterator::SKIP_DOTS );
            foreach ( $libIterator as $libInfo ) {
                // Project.Package
                if ( $libInfo->isDir() ) {
                    $packageIterator = new FilesystemIterator( $libInfo->getPathname(), FilesystemIterator::SKIP_DOTS );
                    foreach ( $packageIterator as $packageInfo ) {
                        // Project.SubPackage
                        if ( $packageInfo->isDir() && $packageInfo->getFilename() != 'actions' ) {
                            $subpackageIterator = new FilesystemIterator( $packageInfo->getPathname(), FilesystemIterator::SKIP_DOTS );
                            foreach ( $subpackageIterator as $subPackageInfo ) {
                                if ( self::CheckPHPFilename( $subPackageInfo->getFilename(), $subPackageInfo->getPath() . '/' ) ) {
                                    Package::$LibStructure[$subPackageInfo->getFilename()] = $subPackageInfo->getPathname();
                                }
                            }
                        } else {
                            if ( self::CheckPHPFilename( $packageInfo->getFilename(), $packageInfo->getPath() . '/' ) ) {
                                Package::$LibStructure[$packageInfo->getFilename()] = $packageInfo->getPathname();
                            }
                        }
                    }
                }
            }
            Logger::Info( 'Lib Structure was initialized: %d classes', count( Package::$LibStructure ) );
        }


        /**
         * Load Classes
         * @param string   $args [optional]
         * @param string   $_    [optional]
         */
        public static function LoadClasses( $args = null, $_ = null ) {
            $classes = func_get_args();
            foreach ( $classes as $class ) {
                self::LoadClass( $class );
            }
        }


        /**
         * Load Class by Name
         *
         * @param string $className
         * @return bool
         */
        public static function LoadClass( $className ) {
            if ( class_exists( $className, false ) || interface_exists( $className, false ) ) {
                return true;
            }

            if ( !Package::$LibStructure ) {
                Package::initLibStructure();
            }

            $fileName = $className . '.php';
            if ( isset( Package::$LibStructure[$fileName] ) ) {
                /** @noinspection PhpIncludeInspection */
                require_once( Package::$LibStructure[$fileName] );

                Package::$LoadedClasses[Package::$CurrentUri ? 'uri' : 'system'][$className] = $fileName;
                return true;
            }

            return false;
        }


        /**
         * Flush Compiled Cache (php files from Package)
         */
        public static function FlushCompiledCache() {
            $cacheDir = __ROOT__ . '/' . CONFPATH_CACHE . '/';
            $d = dir( $cacheDir );
            while ( false !== ( $file = $d->read() ) ) {
                if ( self::CheckPHPFilename( $file, $cacheDir ) && strpos( $file, 'package_' ) === 0 ) {
                    unlink( $cacheDir . $file );
                }
            }
            $d->close();
        }


        /**
         * Eaze Compile Packages Code
         * Remove packages from cache if not in WITH_PACKAGE_COMPILE or Flush Cache if WITH_PACKAGE_COMPILE && compiled.eaze do not exist
         */
        public static function DoCompiledCacheOperations() {
            if ( Package::WithPackageCompile() ) {
                $packageCompiledFlag = sprintf( '%s/%s/%s', __ROOT__, CONFPATH_CACHE, Package::CompiledEaze );
                $handle = fopen($packageCompiledFlag, 'c+');
                if ( flock ( $handle, LOCK_EX | LOCK_NB ) ) {
                    $pid = fgets( $handle, 4096 );
                    if ( empty( $pid ) ) {
                        $pid = getmypid();
                        Logger::Info( "Lock of " . Package::CompiledEaze . " acquired with pid $pid. Flushing compiled cache" );
                        fputs($handle, $pid);
                        Package::FlushCompiledCache();
                        touch( $packageCompiledFlag );
                    }
                } else {
                    Logger::Info( "Lock of " . Package::CompiledEaze . " already exists. Turning WITH_PACKAGE_COMPILE off" );
                    self::$ForceDisableCompile = true;
                }
            }
        }


        /**
         * Get With Package Compiled Constant value
         * @return bool
         */
        public static function WithPackageCompile() {
            if ( defined( Package::WithPackageCompile ) && !self::$ForceDisableCompile ) {
                if ( WITH_PACKAGE_COMPILE ) {
                    return true;
                }
            }

            return false;
        }
    }

    Package::InitConstants();
    Package::DoCompiledCacheOperations();

    spl_autoload_register( 'Package::LoadClass' );
    if ( Package::WithPackageCompile() ) {
        register_shutdown_function( 'Package::Shutdown' );
        Package::IncludeSystem();
    }
?>