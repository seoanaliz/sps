<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 28.10.12
 * Time: 18:06
 * To change this template use File | Settings | File Templates.
 */
class getReportList
{
    public function execute()
    {
        error_reporting(0);
        $offset =   Request::getInteger( 'offset' );
        $limit  =   Request::getInteger( 'limit' ) ? Request::getInteger( 'limit' ) : 25;

        $search =   array(
            'page'     =>   round( $offset / $limit ),
            'pageSize' =>   $limit
        );
        $options = array();
        $res    =   BarterEventFactory::Get( $search, $options, 'tst' );


        die( ObjectHelper::ToJSON( array('response' => StatBarter::form_response( $res ))));
    }
}
