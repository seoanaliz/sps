    <?php


class deleteTemplate
{
    public function execute()
    {
        error_reporting( 0 );

        $user_id        =   Request::getInteger( 'userId' );
        $tmplId         =   Request::getInteger( 'tmplId'   );

        if ( !$user_id || !$tmplId ) {
            die(ERR_MISSING_PARAMS);
        }

        echo ObjectHelper::ToJSON( array(
            'response' => MesDialogs::del_template( $tmplId )
        ));
    }
}
