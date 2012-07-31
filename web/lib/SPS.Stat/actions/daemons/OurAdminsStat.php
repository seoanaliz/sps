<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 31.07.12
 * Time: 14:12
 * To change this template use File | Settings | File Templates.
 */
class OurAdminsStat
{
    private $admins_list = array();

    public function execute() {
        $this->admins_list = $this->get_admins_list();

        $publics = TargetFeedFactory::Get();
        foreach ($publics as $public) {

            if( $public->type != 'vk'             ||
                $public->externalId <   35806186  ||
                $public->externalId >   36959733  ||
                $public->externalId ==  25678227  ||
                $public->externalId ==  26776509  ||
                $public->externalId ==  27421965 )
                continue;
            $this->get_posts( $public->externalId );


        }
    }

    public function get_posts($public_id)
    {
        $offset = $this->get_offset( $public_id );

        $params = array(
            'owner_id'  =>      '-' . $public_id,
            'offset'    =>      $offset,
            'count'     =>      100,
            'filter'    =>      'owner'
        );

        $posts = wrapper::vk_api_wrap('wall.get', $params);

        echo $this->otstup . '' . $posts[0];
        if ($posts[0] < $offset)
            return false;
        unset ($posts[0]);
        foreach ($posts as $post) {
            echo '<br>' . $date_to . ' ' . $post->date . '<br>';

            if ( $post->date <= $date_to ) {
                echo 'proc<br>';
                return false;

            }
        }
        return true;

    }

    public function get_admins_list()
    {
        $sql = 'SELECT vk_id FROM ' . TABLE_OADMINS;
        $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
        $ds = $cmd->Execute();

        $res = array();
        while ( $ds->Next() ) {
            $res[] = $ds->getValue( 'vk_id', TYPE_INTEGER );
        }
        return $res;
    }

    public function get_date_to($wall_id)
    {
        $sql = 'SELECT MAX(post_time) FROM our_publs_adms_posts WHERE publ_id=' . $wall_id;
        $this->db_wrap('query', $sql);
        $a = $this->db_wrap('get_row');
        return $a['max'];
    }

    public function get_publ_info($url, $user = false)
    {

        $url = trim($url, '/');
        $url = explode('/', $url);
        $url = end($url);

        $entry  = $this->vk_api_wrap('groups.getById', array('gids' =>  $url));
        $entry = $entry[0];
        $result['id']           = $entry->gid;
        $result['name']         = $entry->name;
        $result['shortname']    = $entry->screen_name;
        $result['type']         = $entry->type;
        $result['photo']        = $entry->photo;

        return $result;
    }

    public function add_admin($id)
    {
        $admin = $this->get_user_info($id);
//            print_r($admin);
        foreach($this->admins_list as $ad)
        {
            if ($ad['vk_id'] == $admin['id'])
                return $admin['id'];
        }


        $sql = "INSERT INTO
                            our_publics_admins(  vk_id,
                                                name,
                                                shortname,
                                                publ_id,
                                                ava,
                                                posts_count,
                                                topics)
                            VALUES             (
                                                 {$admin['id']},
                                                '{$admin['name']}',
                                                '{$admin['shortname']}',
                                                 {$this->public_id},
                                                '{$admin['photo']}',
                                                  0,
                                                  0
                                                )
                            ";
        print_r($sql);
        $this->db_wrap('query', $sql);
        $this->admins_list[] = $admin['id'];
        return $admin['id'];
    }

    private function get_offset($public_id)
    {
        $sql = 'SELECT MAX(id),vk_post_id FROM ' . TABLE_OADMINS_POSTS . ' WHERE public_id=@public_id';
        $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
        $cmd->SetInteger('@public_id', $public_id);
        $ds = $cmd->Execute();
        $ds->next();
        $offset = $ds->getValue('max', TYPE_INTEGER);
        return $offset ? $offset : 0;
    }
}
