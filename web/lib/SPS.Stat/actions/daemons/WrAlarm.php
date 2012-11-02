<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 24.10.12
 * Time: 18:27
 * To change this template use File | Settings | File Templates.
 */
class WrAlarm
{
    private $ids = '';
    private $error_text = '';
    private $connect;
    private $wasted_array = array();

    public function Execute()
    {
        set_time_limit(0);
        $this->connect = ConnectionFactory::Get( 'tst' );

        $publics = $this->get_monitoring_publs(1);
//        $publics = array( array( 'public_id'=>35807216,'name' => "Теория успеха | ВКурсе"));
        $this->check_in_search( $publics );
        $publics = $this->get_monitoring_publs();
        $this->check_block( array( array( 'public' => 36959959)));
        print_r($this->wasted_array );
        $report = $this->form_report();
        if ( $report )
            $this->send_report( $report );
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
            $res = VkHelper::api_request( 'execute', array( 'code' => $code,
                'access_token' => VkHelper::get_service_access_token()), 0 );

            sleep(0.3);
            foreach( $res as $apublic_id => $body ) {
                $public_id =  trim( $apublic_id, 'a');
                if ( empty( $body) )
                {

                    $this->wasted_array[$public_id] = 'block';
                    $this->mark_blocked( $public_id );
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
                $code   .= 'var a' . $id . ' = API. groups.search({"q":"' . $name . '","count":50});';
                $return .=  "\"a$id\":a$id,";

            }
            $code .= trim( $return, ',' ) . "};";
            $res = VkHelper::api_request( 'execute', array( 'code' => $code,
                'access_token' => '06eeb8340cffbb250cffbb25420cd4e5a100cff0cea83bb1cbb13f120e10746' ), 0 );
            sleep(0.5);
            foreach( $res as $apublic_id => $search_result ) {
                if ($apublic_id == 'error') {
                    break;
                }
                $public_id = trim( $apublic_id, 'a');
                unset( $search_result[0]);
                foreach( $search_result as $entry ){
                    if( (int)$entry->gid == (int)$public_id ) {
                        continue(2);
                    }

                }
                $this->wasted_array[$public_id] = 'search';
                $this->mark_not_in_search( $public_id );
            }
        }
    }

    public function mark_blocked( $public_id )
    {
        $sql = 'UPDATE ' . TABLE_STAT_PUBLICS . ' SET active = 0 WHERE vk_id=@public_id';
        $cmd = new SqlCommand( $sql, $this->connect );
        $cmd->SetInteger( '@public_id', $public_id );
        $cmd->Execute();
    }

    public function mark_not_in_search( $public_id )
    {
        $ts = time();
        $sql = 'UPDATE ' . TABLE_STAT_PUBLICS . ' SET in_search = false WHERE vk_id=@public_id AND in_search is true ';
        $cmd = new SqlCommand( $sql, $this->connect );
        $cmd->SetInteger( '@public_id', $public_id );
        $cmd->SetInteger( '@ts', $ts );
        $cmd->Execute();
    }

    public function save_name( $public_id, $name )
    {
        $sql = 'UPDATE TABLE_STAT_PUBLICS SET name = @name WHERE vk_id=@public_id';
        $cmd = new SqlCommand( $sql, $this->connect );
        $cmd->SetInteger( '@public_id', $public_id );
        $cmd->SetString ( '@name', $name );
        $cmd->Execute();
    }

    public function get_monitoring_publs( $in_search = '' )
    {
        if ( $in_search )
            $in_search = ' AND in_search is TRUE';
        $sql = 'SELECT vk_id,name
                FROM stat_publics_50k WHERE active=1
                AND  quantity>100000
                ' . $in_search;
        $cmd = new SqlCommand( $sql, $this->connect );
        echo $cmd->getQuery();
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
        $message = '';
        $search_line = array();
        foreach( $this->wasted_array as $k=>$v ) {
            $search_line[] = $k;
        }
        $publics_info = StatPublics::get_publics_info_from_base( $search_line );
        foreach( $this->wasted_array as $k=>$v ) {
            if( $v = 'search')
                $line = ' пропал из поиска';
            else
                $line = ' заблокирован';
            $message .= "[public$k|" . $publics_info[$k]['name'] . "] " . $line . "\n";
        }
        return $message;
    }

    public function send_report( $message )
    {
        //todo нормальное обращение к паблику
        $params = array(
            'access_token'  =>  '80f5187f8bda1f858bda1f85188bf24f7988bda8bcf271bdb4e18c8e2d27aa46ff9b69d',
            'message'       =>  $message,
            'owner_id'      =>  '-' . 43503789
        );

        $res = VkHelper::api_request( 'wall.post', $params , 0 );
    }


}
