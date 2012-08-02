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
        set_time_limit(0);
        $this->admins_list = $this->get_admins_list();

        $publics = TargetFeedFactory::Get();
        foreach ($publics as $public) {

            if( $public->type != 'vk'             ||
                $public->externalId ==  25678227  ||
                $public->externalId ==  26776509  ||
                $public->externalId ==  27421965  ||
                $public->externalId ==  34010064  ||
                $public->externalId ==  35807078 )
                continue;

            $this->get_posts( $public->externalId );


        }
    }

    public function get_posts($public_id)
    {

        $stop_post = $this->get_last_post( $public_id );
        $offset = 0;
        while (1) {
            $params = array(
                'owner_id'  =>      '-' . $public_id,
                'offset'    =>      $offset,
                'count'     =>      100,
                'filter'    =>      'owner'
            );
            $offset += 100;
            $posts = wrapper::vk_api_wrap('wall.get', $params);

            echo $this->otstup . '' . $posts[0] . '<br>';
            if ($posts[0] < $offset)
                break;
            unset ($posts[0]);
            foreach ($posts as $post) {
                $this->post_analize($post);
                if ( $post->id == $stop_post ) {
                    echo 'proc<br>';
                    return true;
                }
            }
        }
    }

    public function post_analize($post)
    {
        ;
        unset($post->attachment);

        if (isset($post->signer_id))
        {
//                if  (substr_count($post->text, 'topic') > 0)
//                {
//                    print_r($post);
//                    die();
//                }
            $new_post = array();
            $author = $this->add_admin($post->signer_id);
            echo '<a href="vk.com/wall' . $post->to_id . '_' . $post->id . '"> ' .$post->to_id . '_' . $post->id. '</a><br>';
            if(isset($post->copy_post_id))
                $new_post['tweet_from'] = $post->copy_owner_id . '_' .$post->copy_post_id;
            else $new_post['tweet_from'] = 0;

            if (substr_count($post->text, 'topic') > 0)
                $new_post['is_topic'] = "'true'";
            else
                $new_post['is_topic'] = "'false'";
            $complicate = "'false'";
            if( count( $post->attachments  ) > 1 || strlen( $post->text ) > 200 )
                $complicate = "'true'";


            $new_post['vk_post_id'] =   $post->id;
            $new_post['public_id']  =   trim( $post->to_id, '-' );
            $new_post['author_id']  =   $author;
            $new_post['post_time']  =   $post->date;
            $new_post['complicate'] =   $complicate;
            $new_post['likes']      =   $post->likes->count;
            $new_post['reposts']    =   $post->reposts->count;
            $new_post['rel_likes']  =   0;
            $this->save_post($new_post);

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
        $admin = StatUsers::get_vk_user_info($id);

        foreach($this->admins_list as $ad)
        {
            if ($ad['vk_id'] == $admin['userId'])
                return $ad['vk_id'];
        }


        $sql = "INSERT INTO
                            oadmins(    vk_id,
                                        name,
                                        ava
                                    )
                            VALUES (
                                      @admin_id,
                                      @admin_name,
                                      @admin_photo
                                    )
                            ";
        $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
        $cmd->SetInteger('@admin_id',    $admin['userId']);
        $cmd->SetString ('@admin_photo', $admin['ava']);
        $cmd->SetString ('@admin_name',  $admin['name']);

        print_r($sql);
        $cmd->Execute();
        return $admin['userId'];
    }

    private function get_last_post($public_id)
    {
        $sql = 'SELECT MAX(vk_post_id) FROM ' . TABLE_OADMINS_POSTS . ' WHERE public_id=@public_id';

        $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
        $cmd->SetInteger('@public_id', $public_id);
        $ds = $cmd->Execute();
        $ds->next();
        $offset = $ds->getValue('max', TYPE_INTEGER);
        return $offset ? $offset : 0;
    }

    public function save_post($post)
    {

        $keys   = implode(',', array_keys($post));
        $values = implode(',', $post);

        $sql = "INSERT INTO oadmins_posts( $keys )
                    VALUES( $values )";

        $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
        $cmd->Execute();
    }
}
