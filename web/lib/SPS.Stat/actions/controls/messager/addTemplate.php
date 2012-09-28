    <?php


class addTemplate
{
    public function execute()
    {
        error_reporting( 0 );

        $user_id        =   Request::getInteger( 'userId' );
        $text           =   Request::getString ( 'text'   );
        $group_ids      =   Request::getstring ( 'groupIds' );

        if ( !$user_id || !$text ) {
            die(ERR_MISSING_PARAMS);
        }

        if (!$group_ids)
            $group_ids = '0';
        $res = MesDialogs::add_template( $text, $group_ids);

        echo ObjectHelper::ToJSON( array( 'response' => $res ));
    }
}
