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
    const PAUSE = 3;
    const REQUESTS_PER_QUERY = 20;

    private $conn;
    private $time;
    private $vld_publics = array(
         43157718
        ,38000555
        ,43503575
        ,43503460
        ,43503503
        ,43503550
        ,43503725
        ,43503431
        ,43503315
        ,43503298
        ,43503235
        ,43503264
    );

    public function Execute()
    {

        $this->conn = ConnectionFactory::Get( 'tst' );
        set_time_limit(0);

        $this->truncate_table();
        $this->time = date( 'Y-m-d', time() - 84600 );

        if ( !$this->time_check() )
            die('not now!');

        $publics = StatPublics::get_our_publics_list();
        echo 'всего должно быть записей: ', count($publics), '<br>';
        $publ_do_it_couter = 0;
        foreach( $publics as $public ) {
            if ( !$this->check_entry_exsists( $public['id'] ))
                echo 'не нашел паблик ' . $public['id'] . '<br>';
            echo '<br>паблик ' . $public['id'] . '<br>';
            for( $try = 0; $try < 3; $try++) {
                $this->create_public_entry( $public['id']);
                if( !$this->get_public_members( $public['id'] )) {
                    echo '<br>try again <br>';
                    $this->clear_public_entry( $public['id'] );
                    continue;
                }
                $publ_do_it_couter ++;
                break;
            }
        }
//        echo '<br>alarm!!<br>Всего: ', $publ_do_it_couter, ' ( а должно было ', count($publics),' )<br> <br> ';
        $this->set_sum_entry();
        $this->save_point();

        $this->set_sum_entry( 'vld_publics', $this->vld_publics );
        $this->save_point( 'vld_publics' );
    }

    public function get_public_members( $public_id )
    {
        $offset = 0;
        $i = 0;
        $code = '';
        $return = "return{";
        while( 1 ) {
            if ( $i == self::REQUESTS_PER_QUERY ) {
                $code .= trim( $return, ',' ) . "};";
                $res = VkHelper::api_request( 'execute', array( 'code' =>  $code ), 0 );
                if ( empty( $res )) {
                    echo 'fuck';
                    Logger::Warning( 'Failed request \'execute\' to VK API ' );
                    return false;
                }

                foreach( $res as $stack ) {

                    if ( empty( $stack->users )) {
                        print_r($stack);
                        echo 'fuck  empty( $stack->users )';
                        return false;
                    }

                    $this->save_public_users( $public_id, $stack->users );
                    if( count( $stack->users ) < 999 ) {
                        $quantity = $stack->count;
                        break(2);
                    }
                }
                sleep( self::PAUSE );
                $i = 0;
                $return = "return{";
                $code = '';
            }
            $code   .= "var a$i = API.groups.getMembers({\"gid\":$public_id, \"count\":1000, \"offset\":$offset});";
            $return .= "\" a$i\":a$i,";
            $i++;
            $offset += 1000;
        }
        $count = $this->get_users_count( $public_id );


        return ( abs( $count - $quantity ) < 100 );
    }


    function save_public_users( $public_id, $users ) {
        if( !is_array( $users ) || empty( $users ))
            return false;

        $sql = 'UPDATE temp_users_ids_store SET user_ids = user_ids + @new_ids WHERE  public_id = @public_id';
        $cmd = new SqlCommand( $sql, $this->conn );
        $cmd->SetString ( '@new_ids', '{' . implode( ',', $users ) . '}');
        $cmd->SetInteger( '@public_id', $public_id );
        $cmd->Execute();
    }

    function clear_public_entry( $public_id ) {
        $sql = 'DELETE FROM temp_users_ids_store WHERE  public_id = @public_id';
        $cmd = new SqlCommand( $sql, $this->conn );
        $cmd->SetInteger( '@public_id', $public_id );
        echo $cmd->GetQuery() . '<br>';
        $cmd->Execute();
    }

    function check_entry_exsists( $public_id )
    {
        $sql = 'SELECT * FROM temp_users_ids_store WHERE  public_id = @public_id';
        $cmd = new SqlCommand( $sql, $this->conn );
        $cmd->SetInteger( '@public_id', $public_id );
        $ds = $cmd->Execute();
        return $ds->Next();
    }

    function get_users_count( $public_id ) {
        $sql = 'SELECT #user_ids as count FROM temp_users_ids_store WHERE  public_id = @public_id';
        $cmd = new SqlCommand( $sql, $this->conn );
        $cmd->SetInteger( '@public_id', $public_id );
        echo $cmd->GetQuery();
        $ds = $cmd->Execute();
        $ds->Next();
        return $ds->GetInteger( 'count' );
    }

    function create_public_entry( $public_id, $type = 'public_info' ) {
        $sql = 'INSERT INTO temp_users_ids_store VALUES ( @id, \'{}\', @type )';
        $cmd = new SqlCommand( $sql, $this->conn );
        $cmd->SetInteger( '@id', $public_id );
        $cmd->SetString(  '@type', $type );
        echo $cmd->GetQuery();
        $cmd->Execute();
    }

    public function truncate_table()
    {
        $sql = 'TRUNCATE TABLE temp_users_ids_store';
        $cmd = new SqlCommand( $sql, $this->conn );
        $cmd->Execute();
    }

    public function insert_users( $users )
    {
        if( !$users )
            return false;
        $users = implode( '),(', $users );
        $users = '(' . $users . ')';

        $sql = 'INSERT INTO temp_users_ids_store VALUES ' . $users;
        $cmd = new SqlCommand( $sql, $this->conn );
        $cmd->Execute();
    }

    public function set_sum_entry( $type = 'all_publics', $ids = '' )
    {
        static $i = 0;
        if ( !$ids ) {
            $sql = "SELECT public_id FROM temp_users_ids_store WHERE type = 'public_info'";
            $cmd = new SqlCommand( $sql, $this->conn );
            $ds = $cmd->Execute();
            $ids = array();
            while( $ds->Next()) {
                $ids[] = $ds->GetInteger( 'public_id' );
            }
        }
        $this->create_public_entry( $i++, $type );
        foreach( $ids as $public_id ) {
            $sql = "UPDATE temp_users_ids_store
                    SET user_ids = user_ids + COALESCE(( SELECT user_ids FROM temp_users_ids_store  WHERE public_id = @public_id), '{}' )
                    WHERE type = @type
                ";
            $cmd = new SqlCommand( $sql, $this->conn );
            $cmd->SetInteger( '@public_id', $public_id );
            $cmd->SetString ( '@type',      $type );
            echo $cmd->GetQuery() . '<br>';
            $cmd->Execute();
        }
    }

    public function get_users_quantity($type = 'all_publics')
    {
        $sql = 'SELECT #user_ids as count FROM temp_users_ids_store WHERE type = @type';
        $cmd = new SqlCommand( $sql, $this->conn );
        $cmd->SetString( '@type', $type );
        $ds = $cmd->Execute();
        $ds->Next();
        return $ds->GetInteger( 'count' );
    }

    public function get_distinct_users_quantity( $type = 'all_publics' )
    {
        $sql = 'SELECT # uniq(sort(user_ids)) as count FROM temp_users_ids_store WHERE type = @type';
        $cmd = new SqlCommand( $sql, $this->conn );
        $cmd->SetString( '@type', $type );
        $ds = $cmd->Execute();
        $ds->Next();
        return $ds->GetInteger( 'count' );
    }

    public function save_point( $type = 'all_publics' )
    {
        $dist_users = $this->get_distinct_users_quantity( $type );
        $all_users  = $this->get_users_quantity( $type );
        print_r(array( $dist_users, $all_users));
        $sql = 'INSERT INTO
                    stat_our_auditory( point_date, unique_users, all_users, type)
                VALUES
                    (@point_date, @unique_users, @all_users, @type )';
        $cmd = new SqlCommand( $sql, $this->conn );
        $cmd->SetString(  '@point_date', $this->time );
        $cmd->SetInteger( '@unique_users', $dist_users );
        $cmd->SetInteger( '@all_users', $all_users );
        $cmd->SetString(  '@type', $type );
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
