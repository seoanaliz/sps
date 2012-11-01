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

    public function Execute()
    {
        set_time_limit(0);
        $this->connect = ConnectionFactory::Get( 'tst' );

        $publics = $this->get_monitoring_publs();
        $this->check_block( $publics );
        $this->check_in_search( $publics );
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
                'access_token' => '06eeb8340cffbb250cffbb25420cd4e5a100cff0cea83bb1cbb13f120e10746' ), 0 );
            sleep(0.3);
            foreach( $res as $apublic_id => $body ) {
                if ( empty( $body) )
                {
                    $this->mark_blocked( trim( $apublic_id, 'a'));
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

    public function get_monitoring_publs()
    {
        $sql = 'SELECT vk_id,name FROM stat_publics_50k where active=1 and quantity>500000';
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


}
