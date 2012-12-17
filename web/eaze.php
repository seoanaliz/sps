<?php
    /* Don't Forget to turn on mod_rewrite!  */

    define( 'WITH_PACKAGE_COMPILE', false  );

    if ( !WITH_PACKAGE_COMPILE ) {
        include_once 'lib/Eaze.Core/Logger.php';
        include_once 'lib/Base.Tree/ITreeFactory.php';
        include_once 'lib/Base.Tree/TreeFactory.php';
    }

    include_once 'lib/Eaze.Core/Package.php' ;

    Package::LoadClasses( 'Convert', 'DateTimeWrapper', 'IFactory', 'Dataset' );

    // Initialize Logger
    Logger::Init( ELOG_DEBUG  );
    Logger::Init( ELOG_WARNING );

    mb_internal_encoding( 'utf-8' );
    mb_http_output( 'utf-8' );

    BaseTreeFactory::SetCurrentMode( TREEMODE_ADJ );

    if ( defined( 'WITH_PACKAGE_COMPILE' ) && WITH_PACKAGE_COMPILE ) Logger::Info( 'With package compiled' );

    Request::Init();
    $__level = Request::getParameter( '__level' );
    if ( !is_null( $__level ) ) {
        Logger::LogLevel( $__level );
    }
    SiteManager::DetectSite();

    Logger::Info( __METHOD__, 'Done' );
?>