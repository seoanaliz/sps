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
}
