<?php
/*    Package::Load( 'SPS.Articles' );
    Package::Load( 'SPS.Site' );*/
//    Package::Load( 'SPS.Stat' );

    class VkAlbums
    {
        public static function get_vk_public_albums( $public_id )
        {
            $params = array(
                'gid'           =>      $public_id,
                'need_covers'   =>      1
            );

            $res = VkHelper::api_request( 'photos.getAlbums', $params, 0 );
            if ($res->error)
                return array();
            return $res;
        }

        public static function get_public_album_list( $public_id )
        {
            $sql = 'SELECT album_id FROM '
                      . TABLE_ALBUMS .
                   ' WHERE public_id=@public_id';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
            $cmd->SetInteger( '@public_id', $public_id );
            $ds = $cmd->Execute();
            $res = array();
            while( $ds->Next()) {
                $res[] = $ds->GetInteger( 'album_id' );
            }
            return $res;
        }

        //0 - normal album, 1 - deleted, 2 - new
        public static function set_album_state( $album_id, $public_id, $state )
        {
            $sql = 'UPDATE '
                      . TABLE_ALBUMS .
                '   SET
                        state = @state
                    WHERE
                            public_id = @public_id
                        AND album_id  = @album_id';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
            $cmd->SetInteger( '@state', $state );
            $cmd->SetInteger( '@public_id', $public_id );
            $cmd->SetInteger( '@album_id',  $album_id );
            $cmd->Execute();
        }

        public static function save_point( $params, $ts )
        {
            $sql = 'INSERT INTO '
                .  TABLE_ALBUM_POINTS .
                ' (public_id, album_id, photos_quantity, likes_quantity,comments_quantity,ts)
                    VALUES (@public_id,@album_id,@photos_quantity,@likes_quantity,@comments_quantity,@ts )';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
            $cmd->SetInteger( '@public_id', $params['public_id'] );
            $cmd->SetInteger( '@album_id',  $params['album_id'] );
            $cmd->SetInteger( '@photos_quantity',   $params['photos_quantity'] );
            $cmd->SetInteger( '@likes_quantity',    $params['likes_quantity'] );
            $cmd->SetInteger( '@comments_quantity', $params['comments_quantity'] );
            $cmd->SetInteger( '@ts', $ts );

            $cmd->Execute( );

        }

        public static function get_vk_album_stats( $public_id, $album_id )
        {
            $delimiter = '-' . $public_id . '_';

            $params = array(
                'gid'       =>  $public_id,
                'aid'       =>  $album_id,
                'extended'  =>  1
            );
            $res = VkHelper::api_request( 'photos.get', $params, 0 );
            $photo_row = array();
            foreach( $res as $photo ) {
                $photo_row[] = $photo->pid;
            }
            $photo_row =   $delimiter . implode( ',' . $delimiter  , $photo_row );
            sleep( 0.3 );

            $params = array(
                'photos'        =>  $photo_row,
                'extended'      =>  1
            );
            $res = VkHelper::api_request( 'photos.getById', $params, 0 );

            $likes      =   0;
            $comments   =   0;
            foreach( $res as $photo_hd ) {
                $likes      += $photo_hd->likes->count;
                $comments   += $photo_hd->comments->count;
            }

            return array(
                'likes'   =>  $likes,
                'comments'=>  $comments
            );
        }

        public static function get_last_update_time()
        {
            $sql = 'SELECT MAX(ts) FROM ' . TABLE_ALBUM_POINTS ;
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
            $ds = $cmd->Execute();
            $ds->Next();
            return $ds->GetInteger( 'ts' );
        }

        public static function post_conv( $posts, $trig_inc = false )
        {
            $result_posts_array = array();

            foreach( $posts as $post )
            {
                $id         =   $post->owner_id . '_' . $post->pid;
                $likes      =   $post->likes->count;
                $likes_tr   =   $likes;
                $retweet    =   0;
                $time       =   $post->created;
                $text       =   TextHelper::ToUTF8( $post->text );
                $photo = array();
                $photo[] = array(
                    'id'   =>  $post->owner_id . '_' . $post->pid,
                    'desc' =>  '',
                    'url'  =>  isset( $post->src_big ) ? $post->src_big : $post->src
                );

                $result_posts_array[] = array( 'id' => $id,    'likes'  => $likes, 'likes_tr'=> $likes_tr,
                    'retweet' => $retweet, 'time'   => $time,  'text'   => $text, 'photo'    => $photo, 'link' => '',
                'text_links' => '', 'video' => '', 'music' => '', 'poll' => '', 'map' => '', 'doc' => '');
            }
            return $result_posts_array;
        }

    }
?>
