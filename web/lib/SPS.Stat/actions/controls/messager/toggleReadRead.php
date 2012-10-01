    <?php


class toggleReadRead
{
    public function execute()
    {
//        error_reporting( 0 );

        $user_id        =   Request::getInteger( 'userId' );
        $group_id       =   Request::getInteger( 'groupId' );

        if ( !$user_id || !$group_id) {
            die(ERR_MISSING_PARAMS);
        }

        $dialog['id'] = MesGroups::toggle_read_unread_gr($user_id, $group_id );

        echo ObjectHelper::ToJSON( array( 'response' => true ));
    }
}
