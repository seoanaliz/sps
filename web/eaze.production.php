<?php
    /* Don't Forget to turn on mod_rewrite!  */

    define( 'WITH_PACKAGE_COMPILE', true  );

    include_once 'lib/Eaze.Core/Logger.php';
    // Initialize Logger
    Logger::Init( ELOG_WARNING  );
    include_once 'lib/Eaze.Core/Package.php' ;

    Package::LoadClasses( 'Convert', 'DateTimeWrapper', 'IFactory', 'Dataset', 'Editor', 'Author', 'User', 'AuthorEventUtility' );

    mb_internal_encoding( 'utf-8' );
    mb_http_output( 'utf-8' );
    ini_set('display_errors', '0');

    BaseTreeFactory::SetCurrentMode( TREEMODE_ADJ );

    if ( defined( 'WITH_PACKAGE_COMPILE' ) && WITH_PACKAGE_COMPILE ) Logger::Info( 'With package compiled' );

    Request::Init();
    //if ( Request::getRemoteIp() == '127.0.0.1' ) {
        $__level = Request::getParameter( '__level' );
        if ( !is_null( $__level ) ) {
            Logger::LogLevel( $__level );
        }
    //}
    SiteManager::DetectSite();

    Logger::Info( __METHOD__, 'Done' );
?>