<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 31.07.12
 * Time: 14:12
 * To change this template use File | Settings | File Templates.
 */
Package::Load( 'SPS.Stat' );

class OurAdminsStat
{
    const SOCIAL_BORDER = '138594589';

    private $admins_list  = array();
    private $authors_list = array();

    public function execute() {
        set_time_limit(0);
        $this->admins_list = $this->get_admins_list();
        $this->authors_list = $this->get_authors_list();

        $publics = TargetFeedFactory::Get();
        foreach ($publics as $public) {

            if( $public->type != 'vk'             ||
                $public->externalId ==  25678227  ||
                $public->externalId ==  26776509  ||
                $public->externalId ==  27421965  ||
                $public->externalId ==  34010064  ||

                $public->externalId ==  35807078 )
                continue;

            $this->get_sign_posts( $public->externalId );
//            $this->get_sb_posts( $public->externalId, $public->targetFeedId );
        }
    }

    private function get_authors_list()
    {
        $sql = 'SELECT * FROM authors';
        $cmd = new SqlCommand( $sql, ConnectionFactory::Get() );
        $ds = $cmd->Execute();
        $res = array();
        while( $ds->next() ) {
            $res[$ds->GetValue( 'authorId', TYPE_INTEGER)] = $ds->GetValue( 'vkId', TYPE_INTEGER );
        }
       return $res;
    }

    private function get_average_like_value( $posts )
    {
        $average = 0;
        foreach( $posts as $post)
            $average += $post->likes->count;
        return round( $average / count( $posts ));
    }

    public function get_sign_posts( $public_id )
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
            $posts = wrapper::vk_api_wrap( 'wall.get', $params );

            echo $this->otstup . '' . $posts[0] . '<br>';
            if ($posts[0] < $offset)
                break;
            unset ( $posts[0] );

            $average = $this->get_average_like_value($posts);


            foreach ( $posts as $post ) {

                if ( $post->id == $stop_post ) {
                    echo 'proc<br>';
                    return true;
                }
                $this->post_analize( $post, $average );
            }
        }
    }

    public function get_sb_posts( $public_id, $feed_id )
    {

        echo $public_id . '<br>';
        $sql =  'select * from "articleQueues"
                where
                    author<>@author2
                    AND author<>@author
                    AND author<>@author3
                    AND author<>@author4
                    AND "sentAt">@sentFrom
                    AND "externalId"<>\'1\'
                    AND "targetFeedId"=@feedId';

        $cmd = new SqlCommand( $sql, ConnectionFactory::Get() );
//        $cmd->SetString('@author',    self::SOCIAL_BORDER);
        $cmd->SetString('@author2',   '');
        $cmd->SetString('@author',   self::SOCIAL_BORDER );
        $cmd->SetString('@author3',   '670456' );
        $cmd->SetString('@author4', '176239625');
        $cmd->SetString ('@sentFrom',  '2012-07-15 00:00:00');//date( 'Y-m-d 00:00:00', $this->get_date_to( $public_id )));
        $cmd->SetInteger('@feedId',  $feed_id);
        echo $cmd->GetQuery() . '<br>';
        $ds = $cmd->Execute();

        $posts = array();
        $kmds  = array();
        while( $ds->Next() ) {

            $a = $ds->GetString('externalId', TYPE_STRING);

            $posts[]    = $a;
            $article    = $ds->GetValue('articleId');
            $author     = $ds->GetString('author', TYPE_STRING);//редактор
            echo 'editor:  ' . $author . ' ' . $article . '<br>';
            $author_mb  = $this->get_real_author( $article );//пытаемся найти настоящего автора
            echo 'author_mb:  ' . $author_mb . '<br>';

            $author = $author_mb ? $author_mb : $author;
            $kmds[$a] = $author;

        }
        echo '<br>-------------------------------------------------<br>';
       $likes_average = $this->get_average_likes_cast( $public_id );

        $posts_armfuls = array_chunk( $posts, 100 );
        foreach ( $posts_armfuls as $posts_line ) {
            $posts_line = implode( ',', $posts_line );
            echo '<br>' . $posts_line;
            $res = VkHelper::api_request( 'wall.getById', array( 'posts'  =>  $posts_line));
            foreach( $res as $post ) {
               $this->post_analize( $post, $likes_average, $kmds[ $post->to_id . '_' . $post->id ] );
            }
        }

    }

    public function get_real_author( $article_id )
    {
        $sql = 'SELECT "authorId" FROM articles where "articleId"=@article_id';
        $cmd = new SqlCommand( $sql, ConnectionFactory::Get() );
        $cmd->SetInteger('@article_id',  $article_id);
        $ds = $cmd->Execute();

        $ds->Next();
        $real_author = $ds->GetValue( 'authorId', TYPE_INTEGER );
        return $real_author ? $this->authors_list[ $real_author ] : false;
    }

    public function get_average_likes_cast( $public_id )
    {
        $posts = VkHelper::api_request('wall.get', array( 'owner_id' => '-' . $public_id, 'count' =>  30 ) );
        return $this->get_average_like_value( $posts );
    }

    public function post_analize( $post, $average, $author_id=0 )
    {
        echo 'author_id = ' . $author_id . '<br>';
        unset($post->attachment);

        if ( isset( $post->signer_id ) || $author_id )
        {
            $post->signer_id = $author_id ? $author_id : $post->signer_id;
            $new_post = array();
            $author = $this->add_admin( $post->signer_id );
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

//            print_r($post);

            $new_post['vk_post_id'] =   $post->id;
            $new_post['public_id']  =   trim( $post->to_id, '-' );
            $new_post['author_id']  =   $author;
            $new_post['post_time']  =   $post->date;
            $new_post['complicate'] =   $complicate;
            $new_post['likes']      =   $post->likes->count;
            $new_post['reposts']    =   $post->reposts->count;
            $new_post['rel_likes']  =   round( $new_post['likes'] / $average * 100, 2 );
            $this->save_post( $new_post );

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

    public function get_date_to( $wall_id )
    {
        $sql = 'SELECT MAX(post_time) FROM ' . TABLE_OADMINS_POSTS .' WHERE public_id=@public_id' ;
        $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
        $cmd->SetString( '@public_id', $wall_id );
        $ds = $cmd->Execute();
        $ds->Next();
        return $ds->GetValue( 'max', TYPE_INTEGER );
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
        $admin = StatUsers::get_vk_user_info( $id );
        $admin = end( $admin );


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


        $cmd->Execute();
        return $admin['userId'];
    }

    public function check_in_base( $admin_id )
    {

        $sql = 'select vk_id from ' . TABLE_OADMINS . ' where vk_id=@vk_id';
        $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
        $cmd->SetInteger('@vk_id', $admin_id);
        $ds = $cmd->Execute();
        $ds->next();

    }

    private function get_last_post( $public_id )
    {


        $sql = 'SELECT MAX(vk_post_id) FROM ' . TABLE_OADMINS_POSTS . ' WHERE public_id=@public_id';

        $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
        $cmd->SetInteger('@public_id', $public_id);
        $ds = $cmd->Execute();
        $ds->next();
        $offset = $ds->getValue('max', TYPE_INTEGER);
//        return $offset ? $offset : 0;
        return $offset ? $offset : time();
    }

    public function save_post( $post )
    {
        $keys   = implode(',', array_keys( $post ) );
        $values = implode(',', $post);

        $sql = "INSERT INTO oadmins_posts( $keys )
                    VALUES( $values )";

        $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
        echo $cmd->GetQuery();
        $cmd->Execute();
    }
}
?>
