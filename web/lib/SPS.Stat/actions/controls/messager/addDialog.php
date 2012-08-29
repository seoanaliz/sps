    <?php


class addDialog
{
    public function execute()
    {
        error_reporting( 0 );

        $user_id        =   Request::getInteger( 'userId' );
        $rec_id         =   Request::getInteger( 'recId' );
        $status_id      =   Request::getInteger( 'statId' );

        $status_id      =   $status_id ? $status_id : 0;

        if ( !$user_id || !$rec_id) {
            die(ERR_MISSING_PARAMS);
        }
        $recip  = StatUsers::is_our_user( $rec_id );
        $dialog = StatUsers::add_user( $rec_id );

        if (! ($dialog['id'] = MesDialogs::addDialog($user_id, $rec_id, $status_id ))) {
            echo  ObjectHelper::ToJSON(array('response' => false));
            die();
        }

        echo ObjectHelper::ToJSON( array( 'response' => $dialog ));
    }
}
