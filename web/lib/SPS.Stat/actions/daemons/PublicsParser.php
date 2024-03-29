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

    const LIMIT = 30000;
    const REQUESTS_PER_LAUNCH = 120;
    const PUBLICS_PER_REQUEST  = 500;
    const PAUSE = 0.4;
    private $current_public;
    private $base_publics = [];

    public function execute() {
        set_time_limit(240);
        $i = 0;
        echo 'Начинаем с: ', $this->current_public, '<br>';
        $this->set_base_publics();
        while( $i++ < self::REQUESTS_PER_LAUNCH) {
            $this->get_state();
            if ( $this->current_public > 69000000 ) {
                $this->set_state();
                die();
            }
            $ms = microtime(1);
            $take_counter = rand( 450, self::PUBLICS_PER_REQUEST);
            $params = array(
                'gids'      =>  implode( ',', range( $this->current_public, $this->current_public + $take_counter )),
                'fields'    =>  'members_count'
            );
            $res = VkHelper::api_request( 'groups.getById', $params );
            sleep( self::PAUSE );
            if( !$res)
                continue;
            $new_entries = array();
            $update_entries = 0;
            foreach( $res as $public ) {
                sleep( self::PAUSE );
                if ( !isset( $public->type) || !isset( $public->members_count ) || $public->type != 'page' && $public->type != 'group' && $public->type != 'club' )
                    continue;

                $check = isset($this->base_publics[$public->gid]);
                if ( $public->members_count > self::LIMIT && !$check )  {
                    $entry = new VkPublic();
                    $entry->vk_id = $public->gid;
                    $entry->ava   = $public->photo;
                    $entry->name  = $public->name;
                    $entry->closed =  $public->is_closed;
                    $entry->quantity = $public->members_count;
                    $entry->short_name =  $public->screen_name;
                    $entry->is_page    =  $public->type == 'page' ? true : false;
                    $entry->sh_in_main =  true;
                    $entry->active     =  true;
                    $entry->inLists = false;
                    $entry->updated_at = DateTimeWrapper::Now()->modify('-1 day');
                    $new_entries[] = $entry;
                    echo 'add http://vk.com/club' . $public->gid . '<br>';
                } elseif ( $check && $this->base_publics[$public->gid] < self::LIMIT && $public->members_count > self::LIMIT) {
                    $pub = VkPublicFactory::GetOne(['vk_id' => $public->gid]);
                    $pub->quantity = $public->members_count;
                    $pub->active   =  true;
                    VkPublicFactory::Update($pub);
                    $update_entries++;
                }
            }
            if( $new_entries ) {
                VkPublicFactory::AddRange( $new_entries );
            }
            echo 'добавил: ', count($new_entries),'<br>', round(microtime(1) - $ms, 2),' обновил ' . $update_entries . '<br>';
            $update_entries = 0;
            $this->current_public += $take_counter;
            $this->set_state($this->current_public);
            $this->set_tries(0);
        }
    }

    public function get_state( )
    {
        $sql = 'SELECT * FROM stat_parser limit 1';
        $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
        $ds = $cmd->Execute();
        if( $ds->Next()) {
            $this->current_public = $ds->GetInteger('current_public');
            $tries = $ds->GetInteger( 'tries');
            if( $tries > 3 ) {
                $this->current_public += 1000;
                $tries = 0;
                $this->set_state($this->current_public);
            } else{
                $this->set_tries( ++$tries );
            }
        }
    }

    public static function set_tries( $tries )
    {
        $sql = 'update stat_parser set tries = @tries';
        $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
        $cmd->SetInt( '@tries', $tries );
        $cmd->Execute();
    }

    public function set_state( $current_public = 0, $max_public = null , $reset = 0 )
    {
        $sql = 'update stat_parser set current_public = @current_public, max_public=@max_public';
        $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
        $cmd->SetInt( '@current_public', $current_public );
        $cmd->SetInt( '@max_public', $max_public);
        $cmd->Execute();
    }

    public function set_base_publics( ) {
        if ( empty($this->base_publics)) {
            $result = [];
            $sql = 'select vk_id, quantity from stat_publics_50k';
            $cmd = new SqlCommand($sql, ConnectionFactory::Get('tst'));
            $ds = $cmd->Execute();
            while( $ds->Next()) {
                $result[$ds->GetInteger('vk_id')] = $ds->GetInteger('quantity');
            }
            $this->base_publics = $result;
        }
    }
}
