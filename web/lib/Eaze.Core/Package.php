<?php
    if ( !defined( 'CONFPATH_CACHE' ) ) {
        define( 'CONFPATH_CACHE', 'cache' );
    }

    /**
     * Package Loader
     *
     * Filename = Class name
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
         * Handle for GetLock & ReleaseLock
         * @var resource
         */
        private static $lockHandle;


        /**
         * Using Windows
         * @var bool
         */
        private static $isWindows = false;

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
         * Get Lock Depending on System (only in WITH_PACKAGE_COMPILE)
         * @return bool
         */
        public static function GetLock() {
            if ( !self::WithPackageCompile() ) {
                return false;
            }

            if ( self::$isWindows ) {
                if ( !is_file( self::GetPackageCompiledFlagFile() . '.lock' )
                    && touch( self::GetPackageCompiledFlagFile() . '.lock' ) )
                {
                    return true;
                }

                return false;
            } else { // Unix with flock LOCK_NB
                $handle = fopen( self::GetPackageCompiledFlagFile(), 'r+');
                if ( flock ( $handle, LOCK_EX | LOCK_NB ) ) {
                    self::$lockHandle = $handle;
                    return true;
                }

                return false;
            }
        }


        /**
         * Release Lock (only in WITH_PACKAGE_COMPILE)
         * @return bool
         */
        public static function ReleaseLock() {
            if ( !self::WithPackageCompile() ) {
                return false;
            }

            if ( self::$isWindows ) {
                return unlink( self::GetPackageCompiledFlagFile() . '.lock' );
            } else {
                return flock( self::$lockHandle, LOCK_UN );
            }
        }


        /**
         * Save Loaded classes to cache file
         */
        public static function Shutdown() {
            $hasClasses = false;
            foreach( self::$LoadedClasses as $classes ) {
                if ( $classes ) {
                    $hasClasses = true;
                    break;
                }
            }

            if ( $hasClasses ) {
                if ( self::GetLock() ) {
                    foreach ( self::$LoadedClasses as $type => $classes ) {
                        if ( $classes ) {
                            Logger::Checkpoint();

                            $buffer = '';
                            foreach ( $classes as $filenames ) {
                                foreach( $filenames as $filename => $filepath ) {
                                    $content  = file_get_contents( $filepath );
                                    $content  = self::FormatPhpFileForCompile( $content );
                                    $buffer .= $content;
                                }
                            }

                            file_put_contents( self::GetCompiledFilename( $type, true ), $buffer, FILE_APPEND | LOCK_EX );
                            Logger::Info( 'Writing %d classes to %s', count( $classes ), $type );

                        }
                    }
                    self::ReleaseLock();
                } else {
                    Logger::Info( 'Failed to get lock on compiled packages' );
                }
            }
        }


        /**
         * Format PHP File Content For Compilation
         * @param string $content
         * @return string
         */
        public static function FormatPhpFileForCompile( $content ) {
            $content = rtrim( trim( trim( $content ), PHP_EOL ), '?>' ) . '?>';
            return $content;
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

            if ( strtoupper( substr( PHP_OS, 0, 3 ) ) === 'WIN' ) {
                self::$isWindows = true;
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
                                    Package::$LibStructure[$subPackageInfo->getFilename()][] = $subPackageInfo->getPathname();
                                }
                            }
                        } else {
                            if ( self::CheckPHPFilename( $packageInfo->getFilename(), $packageInfo->getPath() . '/' ) ) {
                                Package::$LibStructure[$packageInfo->getFilename()][] = $packageInfo->getPathname();
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

            // Get Last Class of Namespace
            if ( strpos( $className, '\\' ) !== false ) {
                $classNames = explode( '\\', $className );
                $className  = end( $classNames );
            }

            if ( !Package::$LibStructure ) {
                Package::initLibStructure();
            }

            $fileName = $className . '.php';
            if ( isset( Package::$LibStructure[$fileName] ) ) {
                /** @noinspection PhpIncludeInspection */
                foreach( Package::$LibStructure[$fileName] as $includeFile ) {
                    require_once( $includeFile );
                    Package::$LoadedClasses[Package::$CurrentUri ? 'uri' : 'system'][$className][] = $includeFile;
                }

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
            $packageCompiledFlag = self::GetPackageCompiledFlagFile();
            if ( Package::WithPackageCompile() ) {
                if ( !file_exists( $packageCompiledFlag ) ) {
                    Package::FlushCompiledCache();
                    touch( $packageCompiledFlag );
                }
            } else if ( defined( Package::WithPackageCompile ) && file_exists( $packageCompiledFlag ) ) {
                unlink( $packageCompiledFlag );
                Package::FlushCompiledCache();
                Logger::Info( 'Removing old package cache' );
            }
        }


        /**
         * Get Compiled Lock Filename
         * @return string
         */
        public static function GetPackageCompiledFlagFile() {
            return sprintf( '%s/%s/%s', __ROOT__, CONFPATH_CACHE, Package::CompiledEaze );
        }



        /**
         * Get With Package Compiled Constant value
         * @return bool
         */
        public static function WithPackageCompile() {
            if ( defined( Package::WithPackageCompile ) ) {
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