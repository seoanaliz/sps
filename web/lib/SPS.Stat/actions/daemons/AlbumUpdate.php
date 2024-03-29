<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 10.09.12
 * Time: 12:32
 * To change this template use File | Settings | File Templates.
 */
class AlbumUpdate
{
    public function execute() {
        set_time_limit( 1000 );
        $publics = StatPublics::get_our_publics_list();
        $last_update = VkAlbums::get_last_update_time();

        if ( $last_update - time() < 86400 && $last_update )
            die('Не сейчас');
        $ts = wrapper::morning( time());
        foreach ( $publics as $public ) {
            //получаем 2 массива альбомов паблика - из базы и из вк
            //сравниваем, 3 возможности
            $vk_albums  = VkAlbums::get_vk_public_albums(  $public['id'] );
            $our_albums = VkAlbums::get_public_album_list( $public['id'] );
            foreach( $vk_albums as $vk_album ) {
                $alum_stat = VkAlbums::get_vk_album_stats( $public['id'], $vk_album->aid );
                $params = array(
                    'public_id'         =>  $public['id'],
                    'album_id'          =>  $vk_album->aid,
                    'ava'               =>  $vk_album->thumb_src,
                    'name'              =>  $vk_album->title,
                    'likes_quantity'    =>  $alum_stat['likes'],
                    'comments_quantity' =>  $alum_stat['comments'],
                    'photos_quantity'   =>  $vk_album->size
                );

                VkAlbums::save_point( $params, $ts);

                //пересечение массивов
                if ( $our_albums && in_array( $vk_album->aid, $our_albums )) {
                    $this->update_album( $params, 'update' );
                    $key = array_search( $vk_album->aid, $our_albums );
                    unset( $our_albums[$key] );
                //если в вк есть альбом, у нас не внесенный в бд
                } else {
                    $this->update_album( $params, 'insert');
                    VkAlbums::set_album_state($vk_album->aid, $public['id'], 2 );
                }

            }

            //не найденные в вк альбомы( удаленные )
            foreach( $our_albums as $missed_in_check ) {
                VkAlbums::set_album_state( $missed_in_check, $public['id'], 1 );
            }
        }
    }

    public function update_album( $params, $act )
    {
        if ( $act === 'insert') {
            $sql = 'INSERT INTO '
                    .  TABLE_ALBUMS .
                        ' (public_id, album_id, photos_quantity, likes_quantity,comments_quantity,name,ava)
                    VALUES (@public_id,@album_id,@photos_quantity,@likes_quantity,@comments_quantity,@name,@ava)';
        } else {
            $sql =  'UPDATE '
                        . TABLE_ALBUMS .
                    ' SET
                         photos_quantity   = @photos_quantity,
                         likes_quantity    = @likes_quantity,
                         comments_quantity = @comments_quantity,
                         name  = @name,
                         ava   = @ava
                    WHERE
                        public_id = @public_id
                        AND album_id  = @album_id';
        }

        $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
        $cmd->SetInteger( '@public_id', $params['public_id'] );
        $cmd->SetInteger( '@album_id',  $params['album_id'] );
        $cmd->SetInteger( '@photos_quantity',   $params['photos_quantity'] );
        $cmd->SetInteger( '@likes_quantity',    $params['likes_quantity'] );
        $cmd->SetInteger( '@comments_quantity', $params['comments_quantity'] );
        $cmd->SetString(  '@name', $params['name'] );
        $cmd->SetString(  '@ava',  $params['ava'] );
        $cmd->Execute( );
    }
}
