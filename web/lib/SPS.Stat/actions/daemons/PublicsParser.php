<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 10.09.12
 * Time: 12:32
 * To change this template use File | Settings | File Templates.
 */
class PublicsParser
{

    const LIMIT = 10000;
    const REQUESTS_PER_LAUNCH = 20;
    const PUBICS_PER_REQUEST  = 200;
    const PAUSE = 2;
    private $current_public;


    public function __construct()
    {
        $this->get_state();
    }
    public function execute() {
        set_time_limit(240);
        $i = 0;
        echo 'Начианаем с: ', $this->current_public, '<br>';
        while( $i++ < self::REQUESTS_PER_LAUNCH) {
            $ms = microtime(1);
            $take_counter = rand(50, self::PUBICS_PER_REQUEST);
            $params = array(
                'gids'      =>  implode( ',', range( $this->current_public, $this->current_public + $take_counter )),
                'fields'    =>  'members_count'
            );
            $res = VkHelper::api_request( 'groups.getById', $params );
            sleep( self::PAUSE );
            if( !$res)
                continue;
            $new_entries = array();
            foreach( $res as $public ) {
                if( $public->name == 'DELETED' && $this->current_public > 51000000 && $public->members_count == 0) {
                    $this->set_state( 0, $this->current_public );
                    die();
                }
                if( $public->type != 'page' && $public->type != 'group' && $public->type != 'club' )
                    continue;
                if ( $public->members_count > self::LIMIT && !VkPublicFactory::Get( array( 'vk_id' => $public->gid ))) {
                    $entry = new VkPublic();
                    $entry->vk_id = $public->gid;
                    $entry->ava   = $public->photo;
                    $entry->name  = $public->name;
                    $entry->closed =  $public->is_closed;
                    $entry->quantity = $public->members_count;
                    $entry->short_name =  $public->screen_name;
                    $entry->is_page    =  $public->type == 'page' ? true : false;
                    $entry->sh_in_main =  true;
                    $new_entries[] = $entry;
                }
            }
            if( $new_entries ) {
                VkPublicFactory::AddRange( $new_entries );
            }
            echo 'добавил: ', count($new_entries),'<br>', round(microtime(1) - $ms, 2),'<br>';

            $this->current_public += $take_counter;
            $this->set_state($this->current_public);
        }
    }

    public function get_state( )
    {
        $sql = 'SELECT * FROM stat_parser limit 1';
        $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
        $ds = $cmd->Execute();
        if( $ds->Next()) {
            $this->current_public = $ds->GetInteger('current_public');

        }
    }

    public function set_state( $current_public = 0, $max_public = null , $reset = 0 )
    {
        $sql = 'update stat_parser set current_public = @current_public, max_public=@max_public';
        $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
        $cmd->SetInt( '@current_public', $current_public );
        $cmd->SetInt( '@max_public', $max_public);
        $cmd->Execute();
    }
}
