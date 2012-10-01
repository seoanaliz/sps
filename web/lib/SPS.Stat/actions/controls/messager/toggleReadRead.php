    <?php


class toggleReadRead
{
    public function execute()
    {
//        error_reporting( 0 );

        $user_id        =   Request::getInteger( 'userId' );
        $group_id       =   Request::getInteger( 'groupId' );
        $read           =   Request::getBoolean( 'read' );

        if ( !$user_id || !$group_id) {
            die(ERR_MISSING_PARAMS);
        }

        MesGroups::toggle_read_unread_gr( $user_id, $group_id, $read );

        echo ObjectHelper::ToJSON( array( 'response' => true ));
    }
}
