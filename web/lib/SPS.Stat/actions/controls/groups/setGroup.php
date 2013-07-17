<?
    Package::Load( 'SPS.Stat' );

    /**
     * addPrice Action
     * @package    SPS
     * @subpackage Stat
     */

//создает новую группу
    class setGroup {

        /**
         * Entry Point
         */
        public function Execute() {

            $user_id    =   AuthVkontakte::IsAuth();
            $groupId    =   Request::getInteger( 'groupId' );
            $groupName  =   Request::getString ( 'groupName' );
            $ava        =   Request::getString ( 'ava' );
            $comments   =   Request::getString ( 'comments' );
            $general    =   Request::getInteger( 'general' );
            $type       =   ucfirst( Request::getString( 'type' ));
            $type_array = array( 'Stat', 'Mes', 'Barter' );
            if ( !$type || !in_array( $type, $type_array ))
                $type    = 'Stat';
            $groupId    = $groupId  ? $groupId : 0;

            if ( !$groupName || !$user_id ) {
                die(ERR_MISSING_PARAMS);
            }
            $users = array();
            if( $type == 'Barter' ) {
                $users = GroupsUtility::$barter_watchers;
                $users[] = $user_id;
                $group_source = Group::BARTER_GROUP;
            } elseif ( $type == 'Stat' ) {
                $group_source = Group::STAT_GROUP;
            }
            if ( !GroupsUtility::check_name( $user_id, $group_source, $groupName ))
                die( ObjectHelper::ToJSON(array('response' => false, 'err_mess' =>  'already exist')));
            //если не задан id - создаем группу, задан - обновляем
            if ( !$groupId ) {

                $group = new Group;
                $group->created_by  =   $user_id;
                $group->name        =   $groupName;
                $group->source      =   $group_source;
                $group->status      =   1;
                $group->type        =   GroupsUtility::Group_Private;
                $group->users_ids   =   $users;

                GroupFactory::Add( $group, array( BaseFactory::WithReturningKeys => true ));
                if( !$group->group_id)
                    die( ObjectHelper::ToJSON( array( 'response' => false )));
                if ( $type == 'Stat'  ) {
                    //прикрепляем группу к юзеру
                    $groupUser = new GroupUser($group->group_id, $user_id, Group::STAT_GROUP);
                    GroupUserFactory::Add($groupUser);
                }
            } else {
                $group = GroupFactory::GetOne( array( 'group_id' => $groupId ));
                $default_group = GroupsUtility::get_default_group( $user_id, $group_source );
                if ( empty( $group ) || $group->group_id === $default_group->group_id )
                    die( ObjectHelper::ToJSON( array( 'response' => false, 'err_mes' => 'access denied' )));
                $group->name = $groupName;
                if ( !GroupFactory::Update( $group, array()))
                    die( ObjectHelper::ToJSON( array( 'response' => false )));
            }
            die( ObjectHelper::ToJSON( array( 'response' => $group->group_id )));


            if( !StatUsers::is_Sadmin( $user_id )) {
                 die( ObjectHelper::ToJSON(array('response' => false)));
            }

            if ( $m_class::check_group_name_used( $user_id, $groupName ))  {
                die( ObjectHelper::ToJSON(array('response' => false, 'err_mess' =>  'already exist')));
            }

            if ( $general && !StatUsers::is_Sadmin( $user_id ) ) {
                die( ObjectHelper::ToJSON(array('response' => false)));
            }

            //если мы создаем general группу, ее надо применить ко всем юзерам, посему
            //вместо id текущего юзера мы посылаем массив всех
//            elseif ( $general && !$groupId )
//                $user_id = StatUsers::get_users();

            $newGroupId = $m_class::setGroup( $ava, $groupName, $comments, $groupId );

            if ( !$newGroupId ) {
                die( ObjectHelper::ToJSON(array( 'response' => false )));
            }

            if ( !$groupId )
                $m_class::implement_group( $newGroupId, $user_id );

            die( ObjectHelper::ToJSON( array( 'response' => $newGroupId )));
        }

    }

?>
