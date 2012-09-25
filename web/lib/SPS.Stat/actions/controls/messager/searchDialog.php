<?php


class searchDialog
{

    public function execute()
    {
        error_reporting( 0 );
        $user_id        =   Request::getInteger( 'userId' );
        $search         =   Request::getString ( 'search' );

        if ( !$search || !$user_id ) {
            die(ERR_MISSING_PARAMS);
        }

        $res = MesDialogs::search_dialogs( $user_id, $search );
        if ( $res == 'no access_token' )
            die( ObjectHelper::ToJSON( array( 'response' => false, 'err_mes'   =>  'user is not authorized' )));
        die( ObjectHelper::ToJSON( array( 'response' => $res )));
    }
}
