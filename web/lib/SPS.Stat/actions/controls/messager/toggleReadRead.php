    <?php


class toggleReadRead
{
    public function execute()
    {
        error_reporting( 0 );

        $user_id        =   Request::getInteger( 'userId' );
        $group_id       =   Request::getInteger( 'groupId' );
        $read           =   Request::getBoolean( 'read' );


        if ( !$user_id || !$group_id ) {
            die(ERR_MISSING_PARAMS);
        }
        MesGroups::delete_unread_unread_list( $user_id, $group_id );

        echo ObjectHelper::ToJSON( array( 'response' => true ));
    }
}
