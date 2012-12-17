<?php
    /* Don't Forget to turn on mod_rewrite!  */

    // Initialize Logger
    include_once 'lib/Eaze.Core/Logger.php';
    Logger::Init(ELOG_WARNING);
    // Logger::Init( ELOG_DEBUG, Logger::FileMode  );
    Logger::Debug(PHP_EOL . PHP_EOL. ' START ' . PHP_EOL . PHP_EOL)   ;

    define( 'WITH_PACKAGE_COMPILE', false  );
    define( 'WITH_AUTOLOAD', false  );

    include_once 'lib/Eaze.Core/Package.php' ;

    //if ( !WITH_PACKAGE_COMPILE ) {
    //    include_once 'lib/Base.Tree/ITreeFactory.php';
    //    include_once 'lib/Base.Tree/TreeFactory.php';
    //}

    if (!(defined('WITH_AUTOLOAD') && WITH_AUTOLOAD)){
        Package::Load( 'Eaze.Core');
        Package::Load( 'Eaze.Site');
        Package::Load( 'Eaze.Modules');
        Package::Load( 'Eaze.Model' );
        Package::Load( 'Base.Tree' );
        Package::Load( 'Base.VFS' );
        Package::Load( 'SPS.Common' );
        Package::Load( 'SPS.System' );
        Package::Load( 'SPS.Articles' );
        Package::Load( 'SPS.VK' );
        Package::Load( 'SPS.FB' );
        Package::Load( 'SPS.Site' );
        Package::Load( 'SPS.App' );
    }
    // загружаем независимо
    Package::Load('Eaze.Database/PgSql');

    mb_internal_encoding( 'utf-8' );
    mb_http_output( 'utf-8' );

    if ( defined( 'WITH_PACKAGE_COMPILE' ) && WITH_PACKAGE_COMPILE ) Logger::Info( 'With package compiled' );

    Request::Init();
    $__level = Request::getParameter( '__level' );
    if ( !is_null( $__level ) ) {
        Logger::LogLevel( $__level );
    }
    SiteManager::DetectSite();

    Logger::Info( __METHOD__, 'Done' );
?>
