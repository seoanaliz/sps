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
        $offset         =   Request::getInteger( 'offset' );
        $limit          =   Request::getInteger( 'limit' ) ? Request::getInteger( 'limit' ) : 25;
        $state          =   Request::getString ( 'state' );#мониторы/результаты
        $time_from      =   Request::getInteger( 'timeFrom' );
        $time_to        =   Request::getInteger( 'timeTo' );
        $status         =   Request::getInteger( 'status' );
        $sort_by        =   strtolower( pg_escape_string( Request::getString( 'sortBy' )));
        $sortReverse    =   Request::getInteger( 'sortReverse' );
        $target_public  =   0;#Request::getString ( 'targetPublicId' );
        $barter_public  =   0;#Request::getString ( 'barterPublicId' );

//        if ( $target_public || $barter_public ) {
//            $info = StatBarter::get_page_name( array( $target_public, $barter_public ));
//            print_r( $info);
//            $barter_public = !empty( $info['barter'] ) ? $info['barter']['id'] : 0 ;
//            $target_public = !empty( $info['target'] ) ? $info['target']['id'] : 0 ;
//        }

        $time_from = $time_from ? date( 'Y-m-d H:i:s', $time_from ) : 0;
        $time_to   = $time_to   ? date( 'Y-m-d H:i:s', $time_to ) : 0;
        $order_array = array( 'posted_at', 'visitors', 'subscribers', 'status' );
        $sort_by  =  in_array( $sort_by, $order_array ) ? $sort_by : 'posted_at';
        $sort_by  = ' "' . $sort_by . '" ';
        $sort_by .= $sortReverse ? '' : 'DESC';
        $sort_by .= ' NULLS LAST ';

        if( $status ) {
            $status_array = array( $status );
        } elseif( strtolower( $state ) == 'complete' )
            $status_array = array( 4,5 );
        else
            $status_array = array( 1,2,3,4,5 );

        $search = array(
            '_statusNE'     =>   6,
            'page'          =>   round( $offset / $limit ),
            'pageSize'      =>   $limit,
            '_status'       =>   $status_array,
            '_posted_atGE'  =>   $time_from ,
            '_posted_atLE'  =>   $time_to,
            '_barter_public'=>   $barter_public,
            '_target_public'=>   $target_public
        );

//        $options = array( 'orderBy' => $sort_by );
        $options = array( 'orderBy' => ' "posted_at" desc NULLS LAST ');

        $res     =   BarterEventFactory::Get( $search, $options, 'tst' );
        die( ObjectHelper::ToJSON( array('response' => StatBarter::form_response( $res ))));
    }
}
