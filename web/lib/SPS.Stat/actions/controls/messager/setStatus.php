<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 06.08.12
 * Time: 16:05
 * To change this template use File | Settings | File Templates.
 */
class setStatus
{
    public function execute()
    {
        error_reporting( 0 );

        $dialog_id  =   Request::getInteger( 'dialogId' );
        $status     =   Request::getString(  'status' );

        $status = $status ? $status : '';
        if ( !$dialog_id ) {
            die(ERR_MISSING_PARAMS);
        }

        if ( MesDialogs::set_status( $dialog_id, $status ) )
            echo ObjectHelper::ToJSON(array( 'response' => true ) );
        else
            echo ObjectHelper::ToJSON(array( 'response' => false ) );

    }
}
