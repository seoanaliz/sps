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

    //источники a - app, w - wall(signed)
    //
    //
    const SOCIAL_BORDER = '138594589';

    private $admins_list  = array();
    private $authors_list = array();
    private $a_t;

    private $white_list = array(
        2701428,
        1715958,
        2814488,
        3969468,
        4181767,
        5155508,
        5274121,
        6810901,
        7203958,
        7875269,
        10497980,
        11716281,
        13049517,
        17662525,
        18379341,
        25766591,
        27575384,
        43032990,
        43916879,
        58540552,
        61514101,
        83475534,
        110337004,
        114080351,
        121069867,
        135339094,
        150220483,
        161113216,
        178503163,
        14412297,
        13819249,
        174517560
    );

    public function execute() {
        set_time_limit(0);

        $this->a_t = VkHelper::get_service_access_token();
        $this->admins_list = $this->get_admins_list();
        $this->authors_list = $this->get_authors_list();
        $publics = TargetFeedFactory::Get();
        foreach ( $publics as $public ) {

            if( $public->type != 'vk'               ||
                $public->externalId ==  '25678227'  ||
                $public->externalId ==  '26776509'  ||
                $public->externalId ==  '27421965'  ||
                $public->externalId ==  '34010064'  ||
                $public->externalId ==  '35807078'  ||
                $public->externalId ==  '38000341'  ||
                $public->externalId ==  '43503681'  ||
                $public->externalId ==  '43503753'  ||
                $public->externalId ==  '43503725'  ||
                $public->externalId ==  '36959733'  ||
                $public->externalId ==  '43503694'  ||
                $public->externalId ==  '38000393'  ||
                $public->externalId ==  '43157718'  ||
                $public->externalId ==  '43503630'
                )
            {

                continue;
            }
            $this->update_posts_info( $public->externalId );

//            $this->get_sign_posts( $public->externalId );
//            $this->get_sb_posts( $public->externalId, $public->targetFeedId );
        }
    }

    private function update_posts_info( $public_id )
    {
           $sql = 'select * from oadmins_posts where post_time > 1349049600 and post_time <1352980567 and public_id=@public_id';

        $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
        $cmd->SetString( '@public_id', $public_id);
        $ds = $cmd->execute();
        $posts = array();
        $likes_average = $this->get_average_likes_cast( $public_id );

        while( $ds->Next()) {
            $posts[] = '-' . $ds->GetValue('public_id') . '_' . $ds->GetValue('vk_post_id');

        }
        $posts_armfuls = array_chunk( $posts, 100 );
        foreach ( $posts_armfuls as $posts_line ) {
            $posts_line = implode( ',', $posts_line );
            $res = VkHelper::api_request( 'wall.getById', array( 'posts'  =>  $posts_line, 'access_token'=>$this->a_t));
            sleep(0.3);
            foreach( $res as $post ) {
                $this->post_analize( $post, $likes_average, 'a', 0);
            }
        }
     }

    private function get_authors_list()
    {
        $sql = 'SELECT * FROM authors';
        $cmd = new SqlCommand( $sql, ConnectionFactory::Get());
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
        foreach( $posts as $post )
            $average += $post->likes->count;
        return round( $average / count( $posts ));
    }

    public function get_sign_posts( $public_id )
    {
        var_dump( $public_id );
        if ( $public_id == '35806378' )
            return false;
        $stop_post = $this->get_last_post( $public_id );
        $offset = 0;
        while (1) {
            $params = array(
                'owner_id'  =>      '-' . $public_id,
                'offset'    =>      $offset,
                'count'     =>      100,
                'filter'    =>      'owner',
                'access_token'=>$this->a_t
            );
            $offset += 100;
            $posts  =  wrapper::vk_api_wrap( 'wall.get', $params );

            if ($posts[0] < $offset)
                break;
            unset ( $posts[0] );
//            $average = $this->get_average_likes_cast( $public_id );

            foreach ( $posts as $post ) {
                if ( $post->id == $stop_post ) {
                    echo 'proc<br>';
                    return true;
                }
                $this->post_analize( $post, $average, 'w' );
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
        $cmd->SetString('@author2', '');
        $cmd->SetString('@author',  self::SOCIAL_BORDER );
        $cmd->SetString('@author3', '670456' );
        $cmd->SetString('@author4', '176239625');
        $cmd->SetString ('@sentFrom',  '2012-07-15 00:00:00');
        $cmd->SetInteger('@feedId',  $feed_id);
        $ds = $cmd->Execute();

        $posts = array();
        $kmds  = array();
//        $likes_average = $this->get_average_likes_cast( $public_id );

        while( $ds->Next()) {
            $new_post = array();
            $post_id =  $ds->GetValue('externalId');
            $post_id = ltrim( $post_id, '-');
            $post_id = explode( '_', $post_id );
            $data = $ds->GetValue('sentAt');
            $data = explode(' ' ,$data);
            $data_a = explode( '-',$data[0]);
            $data_b = explode( ':',$data[1]);
            $data = mktime($data_b[0],$data_b[1],$data_b[2],$data_a[1], $data_a[2], $data_a[0]);
            $new_post['vk_post_id'] =   $post_id[1];
            $new_post['public_id']  =   $public_id;
            $new_post['author_id']  =   $ds->GetValue('author');
            $new_post['post_time']  =   $data;
            $new_post['complicate'] =   "'t'";
            $new_post['likes']      =   $ds->GetValue('externalLikes');
            $new_post['reposts']    =   $ds->GetValue('externalRetweets');
            $new_post['rel_likes']  =   round( $new_post['likes'] / 2 * 100, 2 );
            $new_post['source']     =   "'a'";
            $a = $ds->GetString( 'externalId', TYPE_STRING );

            $article = $ds->GetValue('articleId');
            $editor  = $this->get_editor( $article );
            if ( $editor ) {
                $new_post['author_id']  = $editor;
            } else {
                $author_k  = $this->get_real_author( $article );//пытаемся найти настоящего автора
                if ( !$author_k )
                    continue;
                $new_post['author_id']  = $author_k;
            }
            $this->save_post( $new_post );


        }
        echo '<br>-------------------------------------------------<br>';
       $likes_average = $this->get_average_likes_cast( $public_id );

        $posts_armfuls = array_chunk( $posts, 100 );
        foreach ( $posts_armfuls as $posts_line ) {
            $posts_line = implode( ',', $posts_line );
            $res = VkHelper::api_request( 'wall.getById', array( 'posts'  =>  $posts_line, 'access_token'=>$this->a_t));
            sleep(0.3);
            foreach( $res as $post ) {
               $this->post_analize( $post, $likes_average, 'a', $kmds[ $post->to_id . '_' . $post->id ] );
            }
        }
    }

    public function get_editor( $article_id) {
        $sql = 'select * from articles where "articleId"= ' . $article_id;
        $cmd = new SqlCommand( $sql, ConnectionFactory::Get());
        $ds= $cmd->execute();
        $ds->next();

        return $ds->GetValue('editor');
    }

    public function get_real_author( $article_id )
    {
        $sql = 'SELECT "authorId" FROM "authorEvents" where "articleId"=@article_id';
        $cmd = new SqlCommand( $sql, ConnectionFactory::Get() );
        $cmd->SetInteger('@article_id',  $article_id);
        $ds = $cmd->Execute();
        echo $cmd->GetQuery() . '<br>';
        $ds->Next();
        $real_author = $ds->GetInteger( 'authorId' );
        print_r($real_author);
        if ( $real_author ) {
            $author = AuthorFactory::GetById( $real_author );
            print_r($author);
            return $author->vkId;
        }
            return false;
    }

    public function get_average_likes_cast( $public_id )
    {
        if ($public_id == '35806378')
            return false;
        $posts = VkHelper::api_request( 'wall.get', array( 'owner_id' => '-' . $public_id, 'count' =>  30, 'offset' => 250, 'access_token'=>$this->a_t ) );
        return $this->get_average_like_value( $posts );
    }

    public function post_analize( $post, $average, $source, $author_id=0 )
    {
        unset( $post->attachment );

        if ( 1 )
        {
            $post->signer_id = $author_id ? $author_id : $post->signer_id;
//            if ( !in_array( $post->signer_id, $this->white_list ))
//                return true;
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
            if( count( $post->attachments  ) > 2 || strlen( $post->text ) > 200 )
                $complicate = "'true'";
            $data = $post->date;
            $new_post['vk_post_id'] =   $post->id;
            $new_post['public_id']  =   trim( $post->to_id, '-' );
            $new_post['author_id']  =   $author;
            $new_post['post_time']  =   $post->date;
            $new_post['complicate'] =   $complicate;
            $new_post['likes']      =   $post->likes->count;
            $new_post['reposts']    =   $post->reposts->count;
            $new_post['rel_likes']  =   round( $new_post['likes'] / $average * 100, 2 );
            $new_post['source']     =   "'" . $source . "'";
            $this->update_post( $new_post );
        }
        return true;
    }

    public function update_post( $post ) {
        print_r($post);
        $sql = 'UPDATE oadmins_posts  set likes=@likes, reposts = @reposts, complicate=@complicate, rel_likes=@rel_likes
              where public_id = @public_id and vk_post_id= @vk_post_id';
        $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
        $cmd->SetString('@vk_post_id', $post['vk_post_id'] );
        $cmd->SetString('@public_id', $post['public_id'] );
        $cmd->SetInteger('@likes', $post['likes'] );
        $cmd->SetInteger('@reposts', $post['reposts'] );
        $cmd->SetBoolean('@complicate', $post['complicate']);
        $cmd->SetFloat('@rel_likes', $post['rel_likes']);
        echo $cmd->getQuery();
        $cmd->execute();

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
        $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
        $cmd->SetString( '@public_id', $wall_id );
        $ds = $cmd->Execute();
        $ds->Next();
        return $ds->GetValue( 'max', TYPE_INTEGER );
    }

    public function get_publ_info( $url, $user = false )
    {
        $url = trim($url, '/');
        $url = explode('/', $url);
        $url = end($url);

        $entry  = $this->vk_api_wrap( 'groups.getById', array( 'gids' =>  $url ));
        $entry = $entry[0];
        $result['id']           = $entry->gid;
        $result['name']         = $entry->name;
        $result['shortname']    = $entry->screen_name;
        $result['type']         = $entry->type;
        $result['photo']        = $entry->photo;

        return $result;
    }

    public function add_admin( $id )
    {
        $admin = StatUsers::get_vk_user_info( $id );
        $admin = end( $admin );

        foreach( $this->admins_list as $ad )
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
        $cmd->SetInteger( '@vk_id', $admin_id );
        $ds = $cmd->Execute();
        $ds->next();
    }

    private function get_last_post( $public_id )
    {
        $sql = 'SELECT MAX(vk_post_id) FROM ' . TABLE_OADMINS_POSTS . ' WHERE public_id=@public_id';
        $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
        $cmd->SetInteger( '@public_id', $public_id );
        $ds = $cmd->Execute();
        $ds->next();
        $offset = $ds->getValue( 'max', TYPE_INTEGER );
        return $offset ? $offset : 0;
//        return $offset ? $offset : time();
    }

    public function save_post( $post )
    {
        $keys   = implode( ',', array_keys( $post ));
        $values = implode( ',', $post);
        $sql = "INSERT INTO oadmins_posts( $keys )
                    VALUES( $values )";
        $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
        echo $cmd->GetQuery();
        $cmd->Execute();
    }
}
?>
