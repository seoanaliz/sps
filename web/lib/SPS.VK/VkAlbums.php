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

    }
?>
