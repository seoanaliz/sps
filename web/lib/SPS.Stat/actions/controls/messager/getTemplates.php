<?php


class getTemplates
{

    public function execute()
    {
        error_reporting( 0 );
        $user_id        =   Request::getInteger( 'userId' );
        $group_id       =   Request::getInteger( 'groupId' );
        $offset         =   Request::getInteger( 'offset' );
        $limit          =   Request::getInteger( 'limit' );

        if ( !$user_id ) {
            die(ERR_MISSING_PARAMS);
        }

        $res = MesDialogs::get_templates( $user_id, $group_id, $offset, $limit );

        die( ObjectHelper::ToJSON( array( 'response' => $res )));
    }
}
