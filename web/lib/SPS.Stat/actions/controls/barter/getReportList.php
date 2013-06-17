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
        $status         =   Request::getString( 'status' );
        $sort_by        =   strtolower( Request::getString( 'sortBy' ));
        $sortReverse    =   Request::getInteger( 'sortReverse' );
        $target_public  =   Request::getString ( 'targetPublicId' );
        $barter_public  =   Request::getString ( 'barterPublicId' );
        $group_id       =   Request::getString( 'groupId');

        $time_from = $time_from ? date( 'Y-m-d H:i:s', $time_from ) : 0;
        $time_to   = $time_to   ? date( 'Y-m-d H:i:s', $time_to ) : 0;
        $order_array = array( 'posted_at', 'visitors', 'subscribers', 'status' );
        $sort_by  =  in_array( $sort_by, $order_array ) ? $sort_by : ( strtolower( $status ) == 'complete' ? 'posted_at': '   created_at DESC NULLS LAST, posted_at  ' );
//        $sort_by  = ' "' . $sort_by . '" ';
        $sort_by .= $sortReverse ? '' : ' DESC';
        $sort_by .= ' NULLS LAST ';

        $default_group  = GroupsUtility::get_default_group( $user_id, Group::BARTER_GROUP );
        if ( !$group_id ) {
            $group_id       = $default_group->group_id;
        }
        if( !GroupsUtility::has_access_to_group( $group_id, $user_id ))
            die( ObjectHelper::ToJSON( array( 'response' => 'access denied' )));

        if ( !$state) {
              $status_array = array( 1,2,3);
        } elseif( strtolower( $status ) == 'complete' ) {
            $status_array = array( 4,6 );
        } elseif(strtolower( $status ) == 'false' ) {
            $status_array = array(5);
        } else {
            $status_array = array( 4,5,6 );
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
        if( $group_id ) {
            $group_id = explode( ',', $group_id );
            $search['_groups_ids'] = $group_id;
        }

        $options = array( 'orderBy' => $sort_by );
//        $options = array( 'orderBy' => ' "posted_at" desc NULLS LAST, "created_at" desc NULLS LAST ');

        $res     =   BarterEventFactory::Get( $search, $options, 'tst' );

        die( ObjectHelper::ToJSON( array('response' => StatBarter::form_response( $res, $default_group->group_id ))));
    }
}
