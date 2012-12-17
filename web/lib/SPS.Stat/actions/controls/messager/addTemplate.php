<?php


class addTemplate
{
    public function execute()
    {
        error_reporting( 0 );

        $user_id        =   Request::getInteger( 'userId' );
        $text           =   Request::getString ( 'text'   );
        $group_ids      =   Request::getString ( 'groupIds' );
        $tmpl_id        =   Request::getInteger( 'tmplId');

        $tmpl_id    =   isset( $tmpl_id ) ? $tmpl_id   : 0;
        $group_ids  =   $group_ids        ? $group_ids : 0;

        if ( !$user_id || !$text || !$group_ids ) {
            die(ERR_MISSING_PARAMS);
        }

        if ( $tmpl_id )
            $res = MesDialogs::edit_template( $user_id, $tmpl_id, $text, $group_ids );
        else
            $res = MesDialogs::add_template( $user_id, $text, $group_ids );

        echo ObjectHelper::ToJSON( array( 'response' => $res ? $res : false ));
    }
}
