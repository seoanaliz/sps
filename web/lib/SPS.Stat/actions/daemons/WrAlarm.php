<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 24.10.12
 * Time: 18:27
 * To change this template use File | Settings | File Templates.
 */
new stat_tables();
class WrAlarm
{
    const PAUSE = 1;

    private $ids = '';
    private $error_text = '';
    private $connect;
    private $wasted_array = array();

    public function Execute()
    {


        set_time_limit(10000);
  
        $this->connect = ConnectionFactory::Get( 'tst' );
        $publics = $this->get_monitoring_publs();
        $this->check_in_search( $publics );
//        $this->check_block( $publics );

//        $report = $this->form_report_2();
//        if ( $report )
//            $this->send_report( $report );
    }

    public function check_block( $publics_array )
    {
        $publics_array = array_chunk( $publics_array, 25 );

        foreach( $publics_array as $publs ) {
            $code = '';
            $return = "return{";
            foreach( $publs as $public ) {
                $id = $public['public_id'];
                $code   .= 'var a' . $id . ' = API.wall.get({"owner_id":-' . $id . ',"count":1 });';
                $return .=  "\"a$id\":a$id,";
            }

            $code .= trim( $return, ',' ) . "};";
            $res = VkHelper::api_request( 'execute', array( 'code' => $code ), 0 );
            sleep(self::PAUSE);
            $susp_ids = array();
            foreach( $res as $apublic_id => $body ) {
                $public_id =  trim( $apublic_id, 'a');
                if ( empty( $body )) {
                    $susp_ids[] = $public_id;
                } else {
                    if( !$public_id ) {
                        print_r($res);
                        die();
                    }
                    StatPublics::set_state( $public_id, 'active', true, $this->connect );
                }
            }

            foreach( $susp_ids as $id ) {
                $res = VkHelper::api_request( 'wall.get', array(
                    'owner_id'  =>   '-' . $id,
                    'count'     =>   1
                ), 0 );
                if( !isset($res->error))
                    continue;
                if ( substr_count( $res->error->error_msg, 'community members' ) > 0 ) {
                    StatPublics::set_state( $id, 'closed', true, $this->connect );
                    $this->wasted_array[$id] = 'closed';
                } elseif ( substr_count( $res->error->error_msg, 'blocked' ) > 0 ) {
                    StatPublics::set_state( $id, 'active', false, $this->connect );
                    StatPublics::set_state( $id, 'in_search', false, $this->connect );
                    $this->wasted_array[$id] = 'blocked';
                }
            }
        }
    }

    public function check_in_search( $publics_array )
    {
        $publics_array = array_chunk( $publics_array, 25 );

        foreach( $publics_array as $publs ) {
            $code = '';
            $return = "return{";
            foreach( $publs as $public ) {
                $id   = $public['public_id'];
//                $name = preg_replace( '/[^\w\d]/', ' ', $public['name']);

                $name = str_replace( '"', ' ', $public['name']);
                $name = str_replace( "'", ' ', $name);
                $name = htmlspecialchars_decode( $name );
                $code   .= 'var a' . $id . ' = API. groups.search({"q":"' . $name . '","count":50});';
                $return .=  "\"a$id\":a$id,";

            }

            $code .= trim( $return, ',' ) . "};";
            $res = VkHelper::api_request( 'execute', array( 'code' => $code ), 0 );
            sleep(self::PAUSE);
            foreach( $res as $apublic_id => $search_result ) {

                if ( $apublic_id == 'error' ) {
                    break;
                }
                $public_id = trim( $apublic_id, 'a');
                if( !is_array( $search_result ))
                    continue;
                unset( $search_result[0]);
                foreach( $search_result as $entry ){
                    if( (int)$entry->gid == (int)$public_id ) {
                        StatPublics::set_state( $public_id, 'in_search', true, $this->connect );
                        continue(2);
                    }
                }

                $this->wasted_array[$public_id] = 'search';
                StatPublics::set_state( $public_id, 'in_search', false, $this->connect );
            }
        }
    }

    public function save_name( $public_id, $name )
    {
        $sql = 'UPDATE TABLE_STAT_PUBLICS SET name = @name WHERE vk_id=@public_id';
        $cmd = new SqlCommand( $sql, $this->connect );
        $cmd->SetInteger( '@public_id', $public_id );
        $cmd->SetString ( '@name', $name );
        $cmd->Execute();
    }

    public function get_monitoring_publs( )
    {
        $sql = 'SELECT vk_id,name
                FROM stat_publics_50k
                WHERE   quantity > 30000';
        $cmd = new SqlCommand( $sql, $this->connect );
        $ds  = $cmd->Execute();
        $res = array();
        while ( $ds->Next()) {
            $res[] = array(
                'public_id' =>  $ds->GetInteger( 'vk_id' ),
                'name'      =>  $ds->GetValue( 'name' )
            );
        }
        return $res;
    }

    public function get_id_arr()
    {
        $sql = "select vk_id
                FROM ". TABLE_STAT_PUBLICS ."
                WHERE quantity > 50000
                ORDER BY vk_id";
        $cmd = new SqlCommand( $sql, $this->connect);
        $ds = $cmd->Execute();
        $res = array();
        while ( $ds->Next()) {
            $res[] = $ds->getInteger( 'vk_id' );
        }
        return $res;
    }

    public function reset_search()
    {
//            $sql = "UPDATE publics SET in_search=1 WHERE in_search=0";
//            $this->db_wrap('query', $sql);
    }

    public function reset_blocked()
    {
//            $sql = "UPDATE publics SET active=1 WHERE active=0";
//            $this->db_wrap('query', $sql);
    }

    public function form_report()
    {
        $now = time();
        $message = '';
        $search_line = array();
        foreach( $this->wasted_array as $k=>$v ) {
            $search_line[] = $k;
        }

        if ( !empty( $search_line )) {
            $publics_info = StatPublics::get_publics_info_from_base( $search_line );
            foreach( $this->wasted_array as $k=>$v ) {
                $type =  $publics_info[$k]['page'] ? ' страницу ' : ' группу ';
                if( $v == 'search'   )
                    $line = ' Убрали из поиска' . $type . ' ';
                elseif( $v == 'closed' )
                    $line = ' Закрыли ' . $type . ' ';
                else
                    $line = ' Заблокировали' . $type . ' ';
                $message .= $line . "[public$k|" . $publics_info[$k]['name'] . "] " .  "\n";
                $message .= "Количество подписчиков: " . $publics_info[$k]['quantity'] .  " .\n";
                $message .= "Место в рейтинге " . substr( $type, 0, -3 ) . " : " . $this->get_public_place( $k, $publics_info[$k]['page']  ).  " .\n";
            }
        }

        $changed_names = StatPublics::get_public_changes( $now - 3400, $now, $this->connect );
        if ( !empty( $changed_names )) {
            $message .= "\nПоменяли название:\n";
            foreach( $changed_names as $public_id => $names) {
                $message .= "[public". $public_id ."|" . $names['new_name'] . "] ( бывший \"" . $names['old_name'] . "\")\n";
            }
        }

        return $message;
    }

    public function form_report_2()
    {
        $now = time();
        $changes = StatPublics::get_public_changes( $now - 3600, $now , $this->connect );
        foreach( $changes as $k => $v ) {
            $search_line[] = $k;
        }
        if ( empty( $search_line )) {
            die( 'Никаких изменений замечено не было!' );
        }
        $publics_info = StatPublics::get_publics_info_from_base( $search_line );

        $block_line    = '';
        $unblock_line  = '';
        $search_line   = '';
        $unsearch_line = '';
        $closed_line   = '';
        $unclosed_line = '';
        $rename_line   = '';
        $line          = '';
        foreach( $changes as $public_id => $change ){

            $type =  $publics_info[$public_id]['page'] ? ' страниц' : ' групп';
            switch ( $change['act'] ) {
                case 'active':
                    $line   = $change['active'] == 't' ? 'unblock_line' : 'block_line';
                    break;
                case 'in_search':
                    $line   = $change['in_search'] == 't' ? 'search_line' : 'unsearch_line';
                    break;
                case 'name':
                    break;
                case 'closed':
                    $line   = $change['closed'] == 't' ? 'closed_line' : 'unclosed_line';
                    break;
                default:
                    continue(2);
            }
            if( $line )
                $$line .= '- ' . $type . "у [public$public_id|" . $publics_info[$public_id]['name'] . "] " . "\n";
            else {
                $rename_line .= "- [public". $public_id ."|" . $change['new_name'] . "] ( бывший \"" . $change['old_name'] . "\")\n";
                $line = 'rename_line';
            }
            $$line .= "-- Количество подписчиков: " . $publics_info[$public_id]['quantity'] .  " .\n";
            $$line .= "-- Место в рейтинге " . $type . " : " . $this->get_public_place( $public_id, $publics_info[$public_id]['page'] ). " .\n\n";
        }
        return       ( $block_line ? "Заблокировали: \n" . $block_line . "\n": '' ) .
            ( $unblock_line ? "Разблокировали: \n" . $unblock_line . "\n": '' ) .
            ( $search_line ? "Убрали из поиска: \n" . $search_line . "\n": '' ) .
            ( $unsearch_line ? "Вернули в поиск: \n" . $unsearch_line . "\n": '' ) .
            ( $closed_line ? "Закрыли стену: \n" . $closed_line . "\n": '' ) .
            ( $unclosed_line ? "Открыли стену: \n" . $unclosed_line . "\n": '' ) .
            ( $rename_line ? "Переименовали(сь): \n" . $rename_line . "\n": '' );

    }

    public function send_report( $message )
    {
        //todo нормальное обращение к паблику
        $params = array(
            'access_token'  =>  '5caf485657804fac57804fac4357a81f5055780579577320723c7cbdb34b7418c582746',
            'message'       =>  $message,
            'owner_id'      =>  '-' . 43503789
        );

        $res = VkHelper::api_request( 'wall.post', $params , 0 );
    }

    public function get_public_place( $public_id, $page )
    {
        $sql = 'SELECT find_public_place( @public_id ) AS place;';
        $cmd = new SqlCommand( $sql, $this->connect);
        $cmd->SetInteger( '@public_id', $public_id );
        $cmd->SetBoolean( '@page', $page ? true : false );
        $ds = $cmd->Execute();
        $ds->Next();
        return $ds->GetInteger( 'place' );
    }
}
