<?php


class findTemplate
{
    public function execute()
    {
        error_reporting( 0 );

        $user_id        =   Request::getInteger( 'userId' );
        $search         =   Request::getString ( 'search'   );
        $group_id       =   Request::getstring ( 'groupId' );

        if ( !$user_id  ) {
            die(ERR_MISSING_PARAMS);
        }


        $group_id   = $group_id ? $group_id : '0';
        $search     = $search   ? $search   : '0';

        $res = MesDialogs::search_template( $search, $group_id );

        echo ObjectHelper::ToJSON( array( 'response' => $res ));
    }
}
