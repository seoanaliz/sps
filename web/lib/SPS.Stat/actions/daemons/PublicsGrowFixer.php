<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 16.10.12
 * Time: 11:42
 * To change this template use File | Settings | File Templates.
 */
Package::Load( 'SPS.Stat' );



class PublicsGrowFixer
{
    private $conn;
    private $time;
    public function Execute()
    {

        set_time_limit(0);
        $this->time = date( 'Y-m-d', time() - 84600 );
        $this->conn = ConnectionFactory::Get( 'tst' );
        if ( !$this->time_check() )
            die('not now!');
        $this->truncate_table();

        $publics = StatPublics::get_our_publics_list();
        foreach( $publics as $public ) {
            echo 'паблик ' . $public['id'] . '<br>';
            $this->get_public_members( $public['id'] );
        }
        $this->save_point();

    }

    public function get_public_members( $public_id )
    {
        $offset = 0;
        $i = 0;
        $code = '';
        $return = "return{";
        $timeTo = StatPublics::get_last_update_time();
        while( 1 ) {
            if ( $i == 25 ) {
                $code .= trim( $return, ',' ) . "};";
                $res = VkHelper::api_request( 'execute', array( 'code' =>  $code ), 0 );
                foreach( $res as $stack ) {
                    $this->insert_users( $stack->users);
                    if( count( $stack->users ) < 999 )
                        break(2);
                }
                sleep(0.3);
                $i = 0;
                $return = "return{";
                $code = '';

            }
            $code   .= "var a$i = API.groups.getMembers({\"gid\":$public_id, \"count\":1000, \"offset\":$offset});";
            $return .= "\" a$i\":a$i,";
            $i++;
            $offset += 1000;
        }
        echo 'population: '. $stack->count .'<br><br>';
    }

    public function insert_users( $users )
    {

        $users = implode( '),(', $users );
        $users = '(' . $users . ')';

        $sql = 'INSERT INTO temp_users_id_store VALUES ' . $users;
        $cmd = new SqlCommand( $sql, $this->conn );
        $cmd->Execute();
    }

    public function truncate_table()
    {
        $sql = 'TRUNCATE TABLE temp_users_id_store';
        $cmd = new SqlCommand( $sql, $this->conn );
        $cmd->Execute();
    }

    public function get_distinct_users_quantity()
    {
        $sql = 'SELECT COUNT( DISTINCT user_id ) FROM  temp_users_id_store';
        $cmd = new SqlCommand( $sql, $this->conn );
        echo $cmd->GetQuery();
        $ds = $cmd->Execute();
        $ds->Next();
        return $ds->GetInteger( 'count' );
    }

    public function get_users_quantity()
    {
        $sql = 'SELECT COUNT( user_id ) FROM  temp_users_id_store';
        $cmd = new SqlCommand( $sql, $this->conn );
        $ds = $cmd->Execute();
        $ds->Next();
        return $ds->GetInteger( 'count' );
    }

    public function save_point( )
    {
        $dist_users = $this->get_distinct_users_quantity();
        $all_users  = $this->get_users_quantity();
        $sql = 'INSERT INTO
                    stat_our_auditory( point_date, unique_users, all_users)
                VALUES
                    (@point_date, @unique_users, @all_users)';
        $cmd = new SqlCommand( $sql, $this->conn );
        $cmd->SetString(  '@point_date', $this->time );
        $cmd->SetInteger( '@unique_users', $dist_users );
        $cmd->SetInteger( '@all_users', $all_users );
        $cmd->Execute();
        return true;
    }

    public function time_check()
    {
        $sql = 'SELECT * FROM stat_our_auditory WHERE point_date=@point_date';
        $cmd = new SqlCommand( $sql, $this->conn );
        $cmd->SetString( '@point_date', date( 'Y-m-d' ));
        $ds = $cmd->Execute();
        if ( $ds->GetSize())
            return false;
        return true;
    }
}
