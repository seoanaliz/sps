<?php
//обновление данных по топу пабликов, раз в день ~6 ночи
    Package::Load( 'SPS.Stat' );

    set_time_limit(16000);
//    error_reporting( 0 );
    class WrTopics extends wrapper
    {
        private $ids;
        private $page_script_address = 'http://vk.com/al_page.php';

        public function Execute()
        {
            $this->ids = StatPublics::get_50k_publics();

            if ( !$this->check_time() )
                die('Не сейчас');


            $this->update_quantity();
            $this->update_public_info();
            $this->check_admins();
        }



        public function check_time()
        {
            $sql = 'SELECT MAX(time) FROM ' . TABLE_STAT_PUBLICS_POINTS . ' LIMIT 1';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
            $ds = $cmd->Execute();
            $ds->Next();
            $diff =  time() - $ds->getValue('max', TYPE_INTEGER);
            if (self::TESTING)
                echo '<br>differing = ' . $diff . '<br>';
            if ($diff < 86400 )
                return false;
            if ($diff > 86400 * 2 )
                return ($diff / 86400);
            return 1;

        }

        //обновляет информацию об администраторах пабликов
        public function check_admins()
        {
            foreach( $this->ids as $id ) {
                $this->delete_admins( $id );
                $this->find_admins( $id );
            }

        }

        public function delete_admins( $id )
        {
            $sql = 'DELETE FROM ' . TABLE_STAT_ADMINS . '
                    WHERE publ_id = @publ_id';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
            $cmd->SetInteger( '@publ_id', $id );
            $cmd->Execute();
        }

        public function find_admins( $id )
        {
            sleep(0.3);

            $params = array(
                'act'   =>  'a_get_contacts',
                'al'    =>  1,
                'oid'   =>  $id
            );

            $k = $this->qurl_request( $this->page_script_address, $params );#, $this->headers
            $k = explode(' <div class="image">', $k );
            unset( $k[ 0 ] );
            $admins = array();

            foreach( $k as $admin_html ) {
                echo $admin_html;
                $admin = $this->get_admin( 'a href="/' . $admin_html );

//                print_r($admin);
                if ( $admin['vk_id'] ) {

                    echo "Вливаем админа " . $admin[ 'vk_id' ] . "<br>";
                    $sql = 'INSERT INTO ' . TABLE_STAT_ADMINS . ' (
                                                vk_id,
                                                role,
                                                "name",
                                                ava,
                                                publ_id
                                             )
                                        VALUES (
                                                @vk_id,
                                                @role,
                                                @name,
                                                @ava,
                                                @publ_id
                                            )';
//                    $this->db_wrap('query', $query);
                    $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
                    $cmd->Setinteger('@publ_id',$id );
                    $cmd->SetInteger('@vk_id',  $admin['vk_id'] );
                    $cmd->SetString( '@role',   $admin['role'] );
                    $cmd->SetString( '@name',   $admin['name'] );
                    $cmd->SetString( '@ava',    $admin['ava'] );
                    $cmd->Execute();
                }

            }
//            die();
            return;

        }

        public function get_admin( $contact_html )
        {
            if ( preg_match( '/href="\/(.+?)"/', $contact_html, $matches ) )
                $link = VkHelper::get_stripped_text( $matches[1] );

            if ( preg_match( '/<div class="extra_info.+?>(.+?)<\/div>/', $contact_html, $matches ) )
                $cont =  VkHelper::get_stripped_text( $matches[1] );
            if ( preg_match( '/<div class="desc.+?>(.+?)<\/div>/', $contact_html, $matches ) )
                $desc =  VkHelper::get_stripped_text( $matches[1] );
                $desc =  VkHelper::get_stripped_text( $matches[1] );

            if ( preg_match( '/<img src="(.+?)"/', $contact_html, $matches ) )
                $ava = $this->$matches[1];

            if ( substr_count($ava, 'deactivated' ) )
                return false;

            //var_dump($link);
            if( !$link && !$desc && !$cont ) {
                echo 'fail!<br>';
                return false;
            }
            $user_data = array();
            if ( $link ) {
                $user_data = StatUsers::get_vk_user_info( $link );
                $user_data = reset( $user_data );

                var_dump($user_data);
            }
            $res = array(
                'role'  =>  TextHelper::ToUTF8( VkHelper::get_stripped_text( $desc . ' ' . $cont ) ),
                'name'  =>  VkHelper::get_stripped_text( $user_data['name'] ),
                'vk_id' =>  $user_data[ 'userId' ],
                'ava'   =>  isset( $ava )? $ava : $user_data['ava']
            );

            return $res;

        }

        //проверяет изменения в пабликах(название и ава)
        public function update_public_info()
        {
            if (self::TESTING)
                echo '<br>update_public_info<br>';
            $i = 0;
            $ids = '';
            $count = count( $this->ids );
            foreach( $this->ids as $id ) {
                if ( $i == 450 || $i == $count - 1 )
                {
                    $params  = array(
                        'gids'  =>  $ids
                    );

                    $res = $this->vk_api_wrap('groups.getById', $params);
                    foreach($res as $public) {
                        $sql = 'UPDATE ' . TABLE_STAT_PUBLICS . ' SET
                                                name=@name,
                                                ava=@photo
                                WHERE
                                                vk_id=@vk_id';
                        $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
                        $cmd->SetInteger('@vk_id',  $public->gid);
                        $cmd->SetString( '@name',   $public->name );
                        $cmd->SetString( '@photo',  $public->photo);
                        $cmd->Execute();
                    }

                   $count -= 450;
                   $ids = '';
                   $i = 0;
                }
                $i ++;
                $ids .=  $id . ',';
            }
        }

        //обновление данных по каждому паблику(текущее количество, разница со вчерашним днем)
        public function set_public_grow( $publ_id, $quantity, $last_up_time )
        {
            $sql = 'SELECT quantity FROM ' . TABLE_STAT_PUBLICS_POINTS .
                   ' WHERE
                        id=@publ_id
                        AND (
                                time = @time - 86400 * 7
                            OR  time = @time - 86400 * 30
                            )
                   ORDER BY time DESC';

            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ) );
            $cmd->SetInteger( '@time',     $last_up_time );
            $cmd->SetInteger( '@publ_id',  $publ_id );
            $ds = $cmd->Execute();

            while( $ds->Next() ) {
                $quan_arr[] = $ds->getValue('quantity', TYPE_INTEGER);
            }

            if ( isset ( $quan_arr[1] ) ) {
                $diff_rel_mon = round( ( $quantity / $quan_arr[1] - 1) * 100, 2 );
                $diff_abs_mon = $quantity - $quan_arr[1];
            } else {
                $diff_rel_mon = 0;
                $diff_abs_mon = 0;
            }

            if ( isset ( $quan_arr[0] ) ) {
                $diff_rel_week = round( ( $quantity / $quan_arr[0] - 1) * 100, 2 );
                $diff_abs_week = $quantity - $quan_arr[0];
            } else {
                $diff_rel_week = 0;
                $diff_abs_week = 0;
            }

            $sql = 'UPDATE ' . TABLE_STAT_PUBLICS . '
            SET
                quantity=@new_quantity,
                diff_abs=(@new_quantity - quantity),
                diff_rel=round( ( @new_quantity/quantity - 1 ) * 100, 2 ),
                diff_abs_week   =   @diff_abs_week,
                diff_rel_week   =   @diff_rel_week,
                diff_abs_month  =   @diff_abs_month,
                diff_rel_month  =   @diff_rel_month
            WHERE vk_id=@publ_id';

            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ) );
            $cmd->SetInteger( '@publ_id',          $publ_id );
            $cmd->SetInteger( '@diff_abs_week',    $diff_abs_week );
            $cmd->SetInteger( '@diff_abs_month',   $diff_abs_mon );
            $cmd->SetFloat(   '@diff_rel_week',    $diff_rel_week );
            $cmd->SetFloat(   '@diff_rel_month',   $diff_rel_mon );
            $cmd->SetFloat(   '@new_quantity',     $quantity + 0.1 );
//            echo $cmd->GetQuery();
//            die();
            $cmd->Execute();

        }

        //собирает количество поситителей в пабликах
        public function update_quantity()
        {
            $time = $this->morning( time() );
            $i = 0;
            $return = "return{";
            $code = '';
            $timeTo = StatPublics::get_last_update_time();
            foreach( $this->ids as $id ) {

                if ( $i == 25 or !next( $this->ids ) ) {
                    if ( !next( $this->ids ) ) {
                        $code   .= "var a$id = API.groups.getMembers({\"gid\":$id, \"count\":1});";
                        $return .= "\" a$id\":a$id,";
                    }

                    $code .= trim( $return, ',' ) . "};";

                    if (self::TESTING)
                        echo '<br>' . $code;
                    $res = $this->vk_api_wrap('execute', array('code' =>  $code));

                    foreach($res as $key => $entry) {

                        $key = str_replace( 'a', '', $key );
                        $sql = "INSERT INTO " . TABLE_STAT_PUBLICS_POINTS . " (id,time,quantity) values(@id,@time,@quantity)";
                        $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
                        $cmd->SetInteger( '@id',        $key );
                        $cmd->SetInteger( '@time',      $time );
                        $cmd->SetInteger( '@quantity',  $entry->count );
                        $cmd->Execute();

                        $this->set_public_grow( $key, $entry->count, $timeTo );
//

                    }

                    sleep(0.3);
                    $i = 0;
                    $return = "return{";
                    $code = '';
                }

                $code   .= "var a$id = API.groups.getMembers({\"gid\":$id, \"count\":1});";
                $return .= "\" a$id\":a$id,";
                $i++;
            }

        }


}

?>