<?php
    /* Don't Forget to turn on mod_rewrite!  */

    // Initialize Logger
    include_once 'lib/Eaze.Core/Logger.php';
    Logger::Init(ELOG_WARNING);
    //Logger::Init( ELOG_DEBUG, Logger::FileMode  );

    define( 'WITH_PACKAGE_COMPILE', false  );
    define( 'WITH_AUTOLOAD', false  );

    include_once 'lib/Eaze.Core/Package.php' ;

    Package::Load('Eaze.Database/PgSql');

    Package::Load( 'Base.Tree' );
    Package::Load( 'Base.VFS' );

    Package::Load( 'SPS.Common' );
    Package::Load( 'SPS.System' );
    Package::Load( 'SPS.Articles' );
    Package::Load( 'SPS.VK' );
    Package::Load( 'SPS.FB' );
    Package::Load( 'SPS.Site' );
    Package::Load( 'SPS.Site/base' );
    Package::Load( 'SPS.App' );


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