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
        $user_id        =   AuthVkontakte::IsAuth();
        $offset         =   Request::getInteger( 'offset' );
        $limit          =   Request::getInteger( 'limit' ) ? Request::getInteger( 'limit' ) : 25;
        $state          =   Request::getString ( 'state' );#мониторы/результаты
        $time_from      =   Request::getInteger( 'timeFrom' );
        $time_to        =   Request::getInteger( 'timeTo' );
        $status         =   Request::getInteger( 'status' );
        $sort_by        =   strtolower( Request::getString( 'sortBy' ));
        $sortReverse    =   Request::getInteger( 'sortReverse' );
        $target_public  =   0;#Request::getString ( 'targetPublicId' );
        $barter_public  =   0;#Request::getString ( 'barterPublicId' );
        $group_id       =   Request::getInteger( 'groupId');

        $time_from = $time_from ? date( 'Y-m-d H:i:s', $time_from ) : 0;
        $time_to   = $time_to   ? date( 'Y-m-d H:i:s', $time_to ) : 0;
        $order_array = array( 'posted_at', 'visitors', 'subscribers', 'status' );
        $sort_by  =  in_array( $sort_by, $order_array ) ? $sort_by : ( strtolower( $status ) == 'complete' ? 'posted_at': '  posted_at DESC NULLS LAST, created_at ' );
//        $sort_by  = ' "' . $sort_by . '" ';
        $sort_by .= $sortReverse ? '' : 'DESC';
        $sort_by .= ' NULLS LAST ';


        if ( !$group_id ) {
            $default_group  = GroupsUtility::get_default_group( $user_id, 1 );
            $group_id       = $default_group->group_id;
        }
        if( !GroupsUtility::has_access_to_group( $group_id, $user_id ))
            die( ObjectHelper::ToJSON( array( 'response' => false, 'err_mes' => 'access denied' )));


        if( $status ) {
            $status_array = array( $status );
        } elseif( strtolower( $state ) == 'complete' ) {
            $status_array = array( 4,6 );
        } else {
            $status_array = array( 1,2,3,4,5,6 );
        }

        $search = array(
            '_statusNE'     =>   7,
            'page'          =>   round( $offset / $limit ),
            'pageSize'      =>   $limit,
            '_status'       =>   $status_array,
            '_posted_atGE'  =>   $time_from ,
            '_posted_atLE'  =>   $time_to,
            '_barter_public'=>   $barter_public,
            '_target_public'=>   $target_public,
        );
        if( strtolower( $state ) != 'complete' )
            $search['standard_mark'] = true;

        if( $group_id ) {
            $group_id = explode( ',', $group_id );
            $search['_groups_ids'] = $group_id;
        }
//        print_r( $search );
        $options = array( 'orderBy' => $sort_by );
//        $options = array( 'orderBy' => ' "posted_at" desc NULLS LAST, "created_at" desc NULLS LAST ');

        $res     =   BarterEventFactory::Get( $search, $options, 'tst' );
        die( ObjectHelper::ToJSON( array('response' => StatBarter::form_response( $res ))));
    }
}
