<?php


class manageFriend
{
    public function execute()
    {
//        error_reporting( 0 );
        $user_id        =   Request::getInteger( 'userId' );
        $act            =   Request::getString( 'act' );
        $rec_id         =   Request::getInteger( 'recId' );
        if ( !$user_id || !$rec_id ) {
            die(ERR_MISSING_PARAMS);
        }
        if ( StatUsers::manage_friend( $user_id, $rec_id, strtolower( $act ) == 'add' ))
            die( ObjectHelper::ToJSON( array( 'response' => true )));
        else
            die( ObjectHelper::ToJSON( array( 'response' => false )));
    }
}
