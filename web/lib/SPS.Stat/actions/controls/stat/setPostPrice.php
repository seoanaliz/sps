<?
    Package::Load( 'SPS.Stat' );
    Package::Load( 'SPS.VK' );

    /**
     * addPrice Action
     * @package    SPS
     * @subpackage Stat
     */
    class setPostPrice {

        /**
         * Entry Point
         */
        public function Execute()
        {
//            error_reporting( 0 );



            set_time_limit(0);
            $t = 0;
            $line = '';
            print_r( Oadmins::get_gen_editors_work());
            die();
//                for ($i = 1090582; $i > 1085582; $i-- ) {
//                   $line .= ',' . $i;
//                    $t++;
//                }
//            print_r( AdminsWork::get_public_authors(22));
            AdminsWork::get_author_final_score( 26, '36621560,
35806186,
36621513,
36621543,
35806721,
35806476,
35807148,
35807284,
35807216,
35807044,
35806378,
35807199,
35807078,
36959676,
36959733,
36959798,
36959959,
37140910,
37140977,
25678227,
37140953,
26776509,
38000555' );

//            echo microtime(1) . ' >>>>>>>>>>>>>>><br>' ;
//
//            echo '<br><<<<<<<<<<<<<<<' . microtime(1);
            die();
            foreach( $publics as $public_id ) {
                echo 'public_id = ' . $public_id . '<br>';
                StatPublics::get_public_users( $public_id, 'tst' );
            }
            die();
            $dialogs = MesDialogs::get_all_dialogs( 670456, 200 );
            print_r( $dialogs );
            die();
            StatUsers::set_mes_limit_ts( 670456 );

            die();
            echo '<table>';
            foreach( $table as $row ) {
                $row = '<tr>' . implode( '<td>', $row ) . '</tr>';
                echo $row;
            }

            echo '</table>';
            die();
            $publics = StatPublics::get_50k( 0, 37140953 );
            foreach( $publics as $public_id ) {
                StatPublics::get_public_users( $public_id, 1 );
                sleep(0.2);
            }
            die();

            $public_id   =   Request::getInteger( 'publId' );
            if (empty( $public_id )) {
                echo ObjectHelper::ToJSON(array( 'response' => false ));
                die();
            }

//            StatPublics::truncate_table( TABLE_TEMPL_USER_IDS );
//            StatPublics::truncate_table( TABLE_TEMPL_PUBLIC_SHORTNAMES );

            $users_array = StatPublics::get_distinct_users();

            $a = StatPublics::collect_fave_publics( $users_array );
            print_r ( $a );
            die();
            $publicId   =   Request::getInteger( 'publId' );
            $price      =   Request::getInteger( 'price' );

            if (empty($publId)) {
                echo ObjectHelper::ToJSON(array('response' => false));
                die();
            }
            $price = $price ? $price : 0;

            $query = 'UPDATE ' . TABLE_STAT_PUBLICS . ' SET price=@price WHERE vk_id=@publ_id';
            $cmd = new SqlCommand( $query, ConnectionFactory::Get( 'tst' ));
            $cmd->SetInteger( '@publ_id', $publicId );
            $cmd->SetInteger( '@price',   $price );
            $cmd->Execute();

            echo ObjectHelper::ToJSON(array( 'response' => true ));
        }

        public static function delete_non_friends( $user_id )
        {
            $access_token = StatUsers::get_access_token( $user_id );
            $params = array(
                'access_token'    =>    $access_token,
                'count'           =>    1000
            );
            $followers = VkHelper::api_request('subscriptions.getFollowers', $params );
            sleep(0.5);
            $params = array(
                'access_token'    =>   $access_token,
                'count'           =>   1000,
                'offset'          =>   1000
            );
            $followers2 = VkHelper::api_request('subscriptions.getFollowers', $params );

            $followers  = array_merge( $followers->users, $followers2->users );

            $res = array();
            $sql = 'select rec_id from ' . TABLE_MES_DIALOGS. ' WHERE user_id = 13049517';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
            $ds = $cmd->Execute();
            while( $ds->Next() ) {
                $res[] = $ds->GetInteger(  'rec_id');
            }
            $result = array_intersect( $followers, $res );
            print_r( count( $result ));
            echo '<br><br><br><br>';
            $uids = implode( ',', $result );
            sleep(0.4);
            $params = array(
                'uids'          =>  $uids,
                'access_token'  =>  $access_token,
            );
            $res = VkHelper::api_request( 'friends.areFriends', $params );

            $friends = array();
            foreach( $res as $fgf ) {
                echo  $fgf->friend_status . '<br>';
                if ( $fgf->friend_status == 3 ) {

                    continue;
                }

                $friends[] = $fgf->uid;
            }
            print_r( count( $friends ));

            foreach( $result as $rec_id ) {
                $dialog_id = MesDialogs::get_dialog_id( $user_id, $rec_id );
                $sql = 'delete from ' . TABLE_MES_DIALOGS . ' where user_id=@user_id and rec_id=@rec_id';
                $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
                $cmd->SetInteger( '@rec_id', $rec_id );
                $cmd->SetInteger( '@user_id', $user_id );
                $cmd->Execute();
                echo $cmd->GetQuery() . '<br>';
                $sql = 'delete from ' . TABLE_MES_GROUP_DIALOG_REL . ' where dialog_id=@dialog_id';
                $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
                $cmd->SetInteger( '@dialog_id', $dialog_id );
                $cmd->Execute();
                echo $cmd->GetQuery() . '<br>';
            }
        }

        public function get_gender_likes( $public_id, $post_id, $access_token = 0 )
        {
            $params = array( 'owner_id'  =>  '-' . $public_id,
                'post_id'   =>  $post_id,
                'count'     =>  1000
            );
            print_r( $this->vk_execute( 'wall.getLikes', $params, 1000, 'user->uid' ));
            die();
            if ( $access_token )
                $params['access_token'] = $access_token;
            while (1) {
                $res = VkHelper::api_request( 'wall.getLikes', $params );
            }
        }

        //оболочка для execute
        //$method - метод VK API
        //$params - список параметров для метода(offset не нужен)
        //$offset_step - шаг оффсета
        //$get_param - название параметра, который нужно получить(напр. для group.getMembers - users)
        public function vk_execute( $method, $params, $offset_step, $get_param, $save_func = '' )
        {
            $params['offset'] = 0;
            $result = array();
            while (1) {
                $values = '';
                $code = '';
                $return = "return{";
                for ( $i = 0; $i < 25; $i++ ) {
                    $query_line = '';
                    foreach( $params as $parameter => $value ) {
                        $query_line .= '"' . $parameter . '":' . $value . ',';
                    }

                    $query_line = '({' . trim( $query_line, ',' ) . '})';
                    $code   .= "var a$i = API.$method$query_line;";
                    $return .= "\"a$i\":a$i,";
                    $params['offset'] += $offset_step;
                }

                $code .= trim( $return, ',' ) . "};";
                $res = VkHelper::api_request( 'execute', array( 'code' => $code ));

                foreach( $res as $query_result ) {
//                    $values .= implode( ',', $query_result->$get_param ) . ',';
//                    if ( $save_func ) {
//                        $values = '';
//                    }
                    $result[] = $query_result;
                }
//                $result[] = $values;
                if ( count( $res->a24->$get_param ) < $offset_step )
                    break;
//                echo '<br>' . count( explode( ',', $values )) . '<br>';
                sleep(0.5);
            }
            print_r( $result );
            return $values;
        }

    }

?>
