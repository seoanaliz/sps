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
        $status         =   strtolower(Request::getString( 'status' ));
        $sort_by        =   strtolower( Request::getString( 'sortBy' ));
        $sortReverse    =   Request::getInteger( 'sortReverse' );
        $target_public  =   0;#Request::getString ( 'targetPublicId' );
        $barter_public  =   0;#Request::getString ( 'barterPublicId' );
        $group_id       =   Request::getString( 'groupId');
        $all            =   Request::getInteger( 'allEntries') ? true : false;

        $condition_array = array('complete', 'false' );
        if ( !in_array( $status, $condition_array ))
            $status = false;
        elseif( $status == 'false')
            $status = array( 5 );
        else
            $status = array(4,6);

        $time_from = $time_from ? date( 'Y-m-d H:i:s', $time_from ) : 0;
        $time_to   = $time_to   ? date( 'Y-m-d H:i:s', $time_to ) : 0;
        $order_array = array( 'posted_at', 'visitors', 'subscribers', 'status' );
        $sort_by  =  in_array( $sort_by, $order_array ) ? $sort_by : ( strtolower( $status ) == 'complete' ? 'posted_at': '   created_at DESC NULLS LAST, posted_at  ' );
//        $sort_by  = ' "' . $sort_by . '" ';
        $sort_by .= $sortReverse ? '' : 'DESC';
        $sort_by .= ' NULLS LAST ';
        $default_group = GroupsUtility::get_default_group( $user_id, Group::BARTER_GROUP );
        if ( !$all && !$group_id )
            $group_id = $default_group->group_id;

        if (  $all || !$group_id ) {
            $group_id = GroupsUtility::get_all_user_groups( $user_id, Group::BARTER_GROUP );
        } else {
            $group_id = explode( ',', $group_id );
        }
//        if( !GroupsUtility::has_access_to_group( $group_id, $user_id ))
//            die( ObjectHelper::ToJSON( array( 'response' => 'access denied' )));


        if( $status ) {
            $status_array = $status;
        } elseif( strtolower( $state ) == 'complete' ) {
            $status_array = array( 4,5,6 );
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
            '_groups_ids'   =>   $group_id
        );
//        if( strtolower( $state ) != 'complete' )
//            $search['standard_mark'] = true;

        $options = array( 'orderBy' => $sort_by );
//        $options = array( 'orderBy' => ' "posted_at" desc NULLS LAST, "created_at" desc NULLS LAST ');

        $res     =   BarterEventFactory::Get( $search, $options, 'tst' );
        die( ObjectHelper::ToJSON( array('response' => StatBarter::form_response( $res, $user_id ))));
    }
}
