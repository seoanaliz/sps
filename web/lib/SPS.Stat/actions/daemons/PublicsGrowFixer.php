<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 16.10.12
 * Time: 11:42
 * To change this template use File | Settings | File Templates.
 */
Package::Load( 'SPS.Stat' );
define('TMP_TABLE', 'temp_users_id_store');


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
//        $publics = array(
//            array( 'id' => 46234307 ),
//            array( 'id' => 32766117 ),
//        );
        $all_publics_ids = array();
        foreach( $publics as $public ) {
            echo 'паблик ' . $public['id'] . '<br>';
            $error_counter = 0;
            $this->create_entry( $public['id'] );
            $all_publics_ids[] = $public['id'];
            while ( !$this->get_public_members( $public['id'] )) {
                sleep(2);
                $this->delete_entry( $public['id']);
                $this->create_entry( $public['id'] );
                echo 'error detected<br>';
                $error_counter++;
                if( $error_counter > 3 )
                    Logger::Warning( "can't get vk.com/club{$public['id']} users"  );
            }
        }
        $this->create_entry( -1 );
        $this->unite_entries($all_publics_ids, -1);

//        $this->save_point();
    }

    public function get_public_members( $public_id )
    {
        $offset = 0;
        $i = 0;
        $code = '';
        $return = "return{";
        $timeTo = StatPublics::get_last_update_time();
        $check_count = 0;
        while( 1 ) {
            if ( $i == 25 ) {
                $code .= trim( $return, ',' ) . "};";
                $res = VkHelper::api_request( 'execute', array( 'code' =>  $code ), 0 );
                if  ( empty( $res )) {
                    sleep(2);
                    $res = VkHelper::api_request( 'execute', array( 'code' =>  $code ), 0 );
                    echo 'fuck<br>';
                }

                foreach( $res as $stack ) {
                    $count = count( $stack->users );
                    if( !empty( $stack->users ))
                        $this->insert_users_in_array( $stack->users, $public_id );
//                    print_r( count( $stack->users));
                    if( count( $stack->users ) < 998 )
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
        $quantity = $this->get_users_quantity( $public_id );
        if( abs( $quantity - $stack->count ) > 1000 ) {
            echo '<br> try try it again<br>';
            print_r( array($quantity, $stack->count));
            die();
            return false;
        }
        echo 'population: '. $stack->count .'<br><br>';
        return true;
    }

    public function insert_users( $users, $public_id )
    {
        $users = implode( ', ' . $public_id . '),(', $users );
        $users = '(' . $users . ',' . $public_id . ')';
        $sql = 'INSERT INTO temp_users_id_store VALUES ' . $users;
        $cmd = new SqlCommand( $sql, $this->conn );
        $cmd->Execute();
    }

    public function create_entry( $public_id )
    {
        $sql = 'INSERT INTO tst VALUES(\'{}\',@id)';
        $cmd = new SqlCommand( $sql, $this->conn );
        $cmd->SetInteger( '@id', $public_id );
        $cmd->Execute();
    }

    public function insert_users_in_array( $users, $public_id )
    {
        $users = '{' . implode( ',', $users) .'}' ;
        $sql = 'UPDATE tst SET int_array = int_array + @users where id = @id ' ;
        $cmd = new SqlCommand( $sql, $this->conn );
        $cmd->SetInteger( '@id', $public_id );
        $cmd->SetString ( '@users', $users );
//        echo $cmd->GetQuery();
        $cmd->Execute();
    }

    public function truncate_table()
    {
        $sql = 'TRUNCATE TABLE tst';
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

    public function get_users_quantity( $public_id = '' )
    {
        $add =  $public_id ? ' WHERE id = @public_id ' : '';
        $sql = 'SELECT #int_array as count FROM tst  ' . $add . 'limit 1';
        $cmd = new SqlCommand( $sql, $this->conn );
        $cmd->SetInteger( '@public_id', $public_id );
        echo '<br>'.$cmd->GetQuery();
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

    public function delete_entry( $group_id ) {
        $sql = 'DELETE * FROM tst WHERE id= @id';
        $cmd = new SqlCommand( $sql, $this->conn );
        $cmd->SetInteger( '@id', $group_id );
        $cmd->Execute();
    }

    public function unite_entries( $publics, $sum_entry ) {
        foreach( $publics as $public ) {
            $publics_string = implode(',', $publics);

            $sql = 'UPDATE tst SET int_array = int_array + (SELECT int_array FROM tst WHERE id = @id) WHERE id= @sum_entry';
            $cmd = new SqlCommand( $sql, $this->conn );
            $cmd->SetInteger( '@id', $public );
            $cmd->SetInteger( '@sum_entry', $sum_entry );
            $cmd->Execute();
        }

    }
}
