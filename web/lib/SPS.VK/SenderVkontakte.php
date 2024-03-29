<?php
    Package::Load( 'SPS.Stat' );

    class ChangeSenderException extends Exception{}

    /**
     * SenderVkontakte
     * @package    SPS
     * @subpackage VK
     * @author     Shuler
     */

    class SenderVkontakte {

        private $change_admin_errors = array( 5, 7, 15, 214 );
        private $post_photo_array;    //массив адресов фоток
        private $post_text;                     //текст поста
        private $attachments = '';              //аттачи
        public  $vk_access_token;
        private $vk_group_id;                   //id паблика, куда постим
        private $vk_aplication_id;              //id аппа, с которого постим
        private $link;                          //ссылка на источник
        private $sign;                          //ссыль на пользователя, пока неактивно
        private $header;                        //заголовок ссылки
        private $audio_id = array();            //заголовок ссылки
        private $video_id = array();            //заголовок ссылки
        private $repost_post;

        private $post_try_counter = 0;

        const METH                  =   'https://api.vk.com/method/';
        const ANTIGATE_KEY          =   '20f6dc1d30ea218fe78f1c58131c9dda';
        const TESTING               =    false;
        const FALSE_COUNTER         =    3; //количество попыток совершить какое-либо действие
        const ALBUM_NAME            =    'wall photo';
        const CAPTCHA_ERROR_CODE    =    14;

        //(например, получение разгаданной капчи)

        public function __construct( $post_data = null )
        {
            if (!is_null($post_data)) {
                $this->post_photo_array =   isset( $post_data['photo_array'] ) ? $post_data['photo_array'] : array();
                $this->post_text        =   $this->text_corrector( $post_data['text'] );
                $this->vk_group_id      =   $post_data['group_id'] ;
                $this->vk_access_token  =   $post_data['vk_access_token'];
                $this->audio_id         =   isset( $post_data['audio_id'] ) ? $post_data['audio_id'] : array();//массив вида array('videoXXXX_YYYYYYY','...')
                $this->video_id         =   isset( $post_data['video_id'] ) ? $post_data['video_id'] : array();//массив вида array('audioXXXX_YYYYYYY','...')
                $this->link             =   $post_data['link'];
                $this->header           =   $post_data['header'];
                $this->repost_post      =   $post_data['repost_post'];

            }

        }

        private function qurl_request( $url, $arr_of_fields, $headers = '', $uagent = '' )
        {
            if (empty($url)) {
                return false;
            }

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            //        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));

            if (is_array($headers)) { // если заданы какие-то заголовки для браузера
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            }

            if (!empty($uagent)) { // если задан UserAgent
                curl_setopt($ch, CURLOPT_USERAGENT, $uagent);
            } else{
                curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; rv:2.0.1) Gecko/20100101 Firefox/4.0.1)');
            }

            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            if (is_array($arr_of_fields)) {
                curl_setopt( $ch, CURLOPT_POSTFIELDS, $arr_of_fields );

            } else return false;

            $result = curl_exec($ch);
            if (curl_errno($ch)){
                echo "<br>error in curl: ". curl_error($ch) ."<br>";
                throw new Exception('error in curl: '. curl_error($ch)) ;
            }

            curl_close($ch);
            return $result;
        }

        //возвращаемые значения
        //Удачная отсылка
        //      -ХХХ_УУУ - id поста (ХХХ - id паблика, УУУ - поста в этом паблике)
        //Неудачная
        //      исключение 'please change admin'  -
        //          всплыла капча и не удалось ее убить антигейтом,
        //          либо слишком много сообщений от данного издателя,
        //          либо токен сгорел
        //
        //      исключения на все остальное
        public function send_post()
        {
            sleep( rand( 0, 12 ));
            $photo_array = array();
            $meth = 'wall';
//            if ( substr_count($this->post_text, '@') > 0 || substr_count($this->post_text, '[') > 0 ){
//                return false;
//            }
            foreach( $this->post_photo_array as $photo_adr ) {
                $photo_array[] = $this->load_photo( $photo_adr, $meth );
                sleep(0.5);
            }
            echo 1;
            $attachments = array_merge( $photo_array, $this->audio_id, $this->video_id );
            if (  $this->post_text =='©' || ( $this->post_text == '' && count( $attachments ) == 1 )) {
//            $this->post_text = "&#01;";
            }
            if( count( $photo_array ) == 0 && $this->link ) {
                $attachments[] = $this->link;

            }
            $check_id = $this->post( $attachments );
            sleep(2);

            if ( $this->link ) {
                $attachments[] = $this->link;
                $arr = explode( '_', $check_id );
                $this->edit_post( $attachments, array_pop($arr));
            }

            sleep(2);

            if ( !$check_id )
                throw new exception( "can't find post: vk.com/public" . $this->vk_group_id );
            else
                return '-' . $this->vk_group_id . '_' . $check_id;
        }

        public function repost($captcha = array())
        {
            $this->post_try_counter ++;
            if( $this->post_try_counter > 3 ) {
                throw new Exception('too many tries');
            }

            $params = array(
                'object'    =>  'wall' . $this->repost_post,
                'message'   =>  $this->post_text,
                'gid'       =>  $this->vk_group_id,
                'access_token' => $this->vk_access_token
            );
            if(!empty($captcha )) {
                $params = array_merge($params, $captcha);
            }

            $res = VkHelper::api_request( 'wall.repost', $params );
            if (isset ($res->error )) {
                if ( $res->error->error_code == self::CAPTCHA_ERROR_CODE ) {
                    return $this->repost( array(
                        'captcha_key'   =>  $this->captcha( $res->error->captcha_img ),
                        'captcha_sid'   =>  $res->error->captcha_sid
                    ));
                } elseif ( in_array( $res->error->error_code, $this->change_admin_errors )) {
                    throw new ChangeSenderException(  $res->error->error_msg );
                } else {
                    throw new Exception( 'Error in wall.post: ' . $res->error->error_code
                        . ', public: '. $this->vk_group_id );
                }
            }

            if ( isset( $res->success) && $res->success && isset( $res->post_id ))
                return  '-' . $this->vk_group_id . '_' . $res->post_id;
            else {
                throw new Exception( 'Strange response on wall.repost: ' . ObjectHelper::ToJSON( $res )
                    . ', public: '. $this->vk_group_id );
            }
        }

        public function text_corrector( $text )
        {
            $text = strip_tags( $text );
            //cURL пытается найти файл по любой строке, начинающейся с @
//            preg_replace( '/^@/', '^ @', $text );
            if ( $text && $text[0] == '@')
                $text = ' ' . $text;
            $text = preg_replace('/\[([^|]+)(\|)([^|]+)]/', '@$1($3)', $text);
            return $text;
        }

        private function remove_tags()
        {
            $this->post_text = str_replace( '<br>', "\r\n", $this->post_text );
            $this->post_text = htmlspecialchars_decode($this->post_text);
            $this->post_text = strip_tags( $this->post_text );
        }

        //!!!распознование капчи - долгий и неблагодарный процесс(до полуминуты,
        // + он может вернуть нераспознанную)
        // нужно учитывать это время
        //если повезет, возвращает  текст капчи,
        // false в случае неправильной разгадки/недоступности работников распознавания
        private function captcha( $url )
        {
            //не требующие пока изменений настройки
            $domain="antigate.com";
            $rtimeout = 5;
            $mtimeout = 120;
            $is_phrase = 0;
            $is_regsense = 0;
            $is_numeric = 0;
            $min_len = 0;
            $max_len = 0;
            $is_russian = 1;

            $try_counter = 0;
            while (true) {
                $try_counter ++;
                if ($try_counter > 3)
                    return false;
                $tmpCaptcha  = Site::GetRealPath('temp://upl_' . md5($url) . '.jpg');
                $jp = file_get_contents($url);
                file_put_contents($tmpCaptcha, $jp);

                if (!file_exists($tmpCaptcha)) {
                    return false;
                }
                $postdata = array(
                    'method'        => 'post',
                    'key'           => self::ANTIGATE_KEY,
                    'file'          => '@' . $tmpCaptcha,
                    'phrase'        => $is_phrase,
                    'regsense'      => $is_regsense,
                    'numeric'       => $is_numeric,
                    'min_len'       => $min_len,
                    'max_len'       => $max_len,
                );

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL,             "http://$domain/in.php" );
                curl_setopt($ch, CURLOPT_RETURNTRANSFER,  1 );
                curl_setopt($ch, CURLOPT_TIMEOUT,         60 );
                curl_setopt($ch, CURLOPT_POST,            1 );
                curl_setopt($ch, CURLOPT_POSTFIELDS,      $postdata );
                $result = curl_exec($ch);
                unlink( $tmpCaptcha);
                if (curl_errno($ch)) {
                    throw new Exception('Error in captcha recoginition, ' . curl_error($ch));
                }
                curl_close($ch);

                if (strpos($result, "ERROR")!==false) {
                    throw new Exception('Error in captcha recoginition, ' . $result );
                } else {
                    $ex = explode("|", $result);
                    $captcha_id = $ex[1];
                    $waittime = 0;
                    sleep($rtimeout);
                    while(true) {
                        $result = file_get_contents("http://$domain/res.php?key=" . self::ANTIGATE_KEY . '&action=get&id=' . $captcha_id);
                        if (strpos($result, 'ERROR') !== false) {
                            continue(2);
                        }
                        if ($result=="CAPCHA_NOT_READY") {
                            $waittime += $rtimeout;
                            if ($waittime > $mtimeout) {
                                throw new Exception('Error in captcha recoginition, too long'  );
                            }
                            sleep($rtimeout);
                        } else {
                            $ex = explode( '|', $result );
                            if ( trim( $ex[ 0 ] )=='OK' )
                                return trim($ex[1]);
                        }
                    }

                }
            }
            throw new Exception('Error in captcha recoginition, too long' );
        }

        //$post_id  = idпаблика_idпоста
        public function delete_post( $full_post_id )
        {
            list( $owner_id, $post_id ) = explode( '_', $full_post_id );
            if( $post_id ) {
                $owner_id = trim($owner_id, '-');
                $params = array(
                    'owner_id'      =>  '-' . $owner_id,
                    'post_id'       =>  $post_id,
                    'access_token'  =>  $this->vk_access_token
                );
                $res = VkHelper::api_request( 'wall.delete', $params );

                $check = VkHelper::api_request( 'wall.getById', array( 'posts' => $full_post_id ));
                if( empty( $check ))
                    return true;
                throw new Exception('Failed on deleting ' . $post_id . ', ' . $res );
            }
            return false;
        }

        public function delete_photo( $full_photo_id )
        {
            sleep(1);
            list( $owner_id, $photo_id ) = explode( '_', $full_photo_id );
            if( $photo_id ) {
                $params = array(
                    'oid'           =>  $owner_id,
                    'pid'           =>  $photo_id,
                    'access_token'  =>  $this->vk_access_token
                );
                if( VkHelper::api_request( 'photos.delete', $params ))
                    return true;
            }
            return false;
        }

        //нужно для однотипных названий (альбом 1, альбом 2)
        //возвращает массив о последнем таком альбоме:
        // id, количество фото в нем, сколько всего c таким названием
        private function get_album( $title_search = '' )
        {
            $title_search = $title_search ? $title_search : self::ALBUM_NAME;
            $title_search = trim ( mb_convert_case($title_search, MB_CASE_LOWER, "UTF-8" ) );
            $params = array(
                'gid'   =>  $this->vk_group_id
            );

            $res = VkHelper::api_request( 'photos.getAlbums', $params );

            $i = 1;
            $album_id = '';
            foreach ( $res as $album ) {
                if ( substr_count( mb_convert_case( $album->title, MB_CASE_LOWER, "UTF-8" ), $title_search ) > 0 ) {
                    $i++;
                    $album_id   = $album->aid;
                    $album_size = $album->size;
                }
            }

            $res = array( 'id' => $album_id, 'counter' =>   $i, 'size'  => $album_size );

            if ( $i > 1 )
                return( $res );

            return false;

        }

        public function get_album_size( $full_album_id )
        {
            sleep(0.4);
            list( $public_id, $album_id ) = explode( '_', $full_album_id );
            if( $album_id ) {
                $params = array(
                    'gid'           =>  $public_id,
                    'aids'          =>  $album_id,
                    'access_token'  =>  $this->vk_access_token
                );
                $res = VkHelper::api_request( 'photos.getAlbums', $params );
                return $res[0]->size;
            }
            return false;
        }

        private function create_album( $counter = 1, $privacy = 1, $title = '' )
        {
            $counter = $counter ? $counter : 1;
            $title = $title ? $title : self::ALBUM_NAME . ' ' . $counter;

            $params = array(
                'gid'       =>  $this->vk_group_id,
                'title'     =>  $title,
                'privacy'   =>  $privacy,
            );
            $res = VkHelper::api_request( 'photos.createAlbum', $params );
            return  $res->aid  ;
        }

        private function post( $attaches, $captcha = array()) {
            $this->post_try_counter ++;
            if($this->post_try_counter > 3) {
                throw new Exception('too many tries');
            }
            $attaches = implode( ',', $attaches );
            $params = array(
                'owner_id'      =>  '-' . $this->vk_group_id,
                'message'       =>  $this->post_text,
                'access_token'  =>  $this->vk_access_token,
                'attachment'    =>  $attaches,
                'from_group'    =>  1
            );

            if(!empty($captcha )) {
                $params = array_merge($params, $captcha);
            }
            $res = VkHelper::api_request( 'wall.post', $params, false );
            sleep(0.5);
            if ( isset( $res->post_id ))
                return $res->post_id;

            elseif( isset( $res->processing ))
                return true;

            elseif ( isset( $res->error ))
                if( $res->error->error_code  == self::CAPTCHA_ERROR_CODE && isset( $res->error->captcha_img)) {
                    return $this->post($attaches, array(
                        'captcha_key'   =>  $this->captcha( $res->error->captcha_img ),
                        'captcha_sid'   =>  $res->error->captcha_sid
                    ));
                } elseif ( in_array( $res->error->error_code, $this->change_admin_errors )) {
                    throw new ChangeSenderException($res->error->error_msg);

                } else {
                    throw new Exception( 'Error in wall.post: ' . $res->error->error_msg
                        . ', public: '. $this->vk_group_id );
                }
        }

        private function delivery_check( $attacments_count )
        {

            $time_after = VkHelper::get_vk_time();
            if ( !$time_after )
                die();
            sleep(2);
            $params = array(
                'owner_id'      =>  '-' . $this->vk_group_id,
                'count'         =>  5,
                'access_token'  =>  $this->vk_access_token,
            );

            $res = VkHelper::api_request( 'wall.get', $params, false );

            unset( $res[0] );
            $text2 = substr( preg_replace( "/[\s]+/", '', $this->post_text ), 0, 95 );
            foreach ( $res as $post ) {
                $text1 = $this->text_corrector( htmlspecialchars_decode( $post->text ), ENT_NOQUOTES, 'UTF-8' );
                $text1 =  substr( preg_replace( "/[\s]+/", '', $text1 ), 0, 95 );
                if (
                    //$attacments_count === count( $post->attachments )  &&
                    abs( $post->date - $time_after ) < 10 )  {
                    return $post->id;
                }
            }
            return true;
        }

        //todo
        private function edit_post( $attaches, $post_id )
        {
            $attaches = implode( ',', $attaches );
            sleep(0.3);
            $params = array(
                'owner_id'      =>  '-' . $this->vk_group_id,
                'post_id'       =>  $post_id,
                'message'       =>  $this->post_text,
                'access_token'  =>  $this->vk_access_token,
                'attachments'   =>  $attaches,
                'from_group'    =>  1
            );

            $res = VkHelper::api_request( 'wall.edit', $params, false );

        }

        //todo описания фоток матьматьмать
        public function load_photo( $path, $destination = 'wall', $caption = '' )
        {
            if ( !file_exists( $path ))
                throw new exception( " Can't find file : $path for vk.com/public" . $this->vk_group_id);
            if ( filesize( $path) / 1024 < 3)
                throw new exception( " File damaged : $path for vk.com/public" . $this->vk_group_id);
            $aid = '';
            switch ( $destination ) {
                case 'wall':
                    $method_get_server = 'photos.getWallUploadServer';
                    $method_save_photo = 'photos.saveWallPhoto';
                    $photo_list        = 'photo' ;
                    break;
                case 'album':
                    $album = $this->get_album();

                    if ( !$album || $album['size'] > 470 )
                        $aid = $this->create_album( $album[ 'counter' ] );
                    else
                        $aid = $album['id'];

                    $method_get_server  =   'photos.getUploadServer';
                    $method_save_photo  =   'photos.save';
                    $photo_list         =   'photos_list' ;
                    break;
                default:
                    return false;
            }

            $params = array(
                'gid'           =>  $this->vk_group_id,
                'access_token'  =>  $this->vk_access_token,
                'aid'           =>  $aid
            );

            //первый запрос, получение адреса для заливки фото
            $res = VkHelper::api_request( $method_get_server, $params, false );
                print_r($params);

            sleep( 0.5 );
            if ( !isset( $res->upload_url )) {
                if ( isset ( $res->error) && in_array( $res->error->error_code, $this->change_admin_errors )) {
                    throw new ChangeSenderException($res->error->error_msg);
                } elseif(isset ( $res->error)) {
                    throw new exception( " Error uploading photo. Response : " . $res->error->error_msg
                        . " in post to vk.com/public" . $this->vk_group_id );
                } else
                    throw new exception( " Error uploading photo. Response : " . ObjectHelper::ToJSON( $res )
                        . " in post to vk.com/public" . $this->vk_group_id );
            }
            $upload_url = $res->upload_url;

            $photo_size = ImageHelper::GetImageSizes( $path );

            if ( $photo_size['width'] > 2000 || $photo_size['height'] > 2000 ) {
                ImageHelper::Resize( $path, $path, 2000, 2000, 80 );
            }

            //заливка фото
            $content = $this->qurl_request( $upload_url, array('file1' => '@' . $path ) );
            $content = json_decode( $content );
            if ( empty( $content->$photo_list )) {
                throw new exception(" Error uploading photo. Response : $content in post to vk.com/public" . $this->vk_group_id );
            }
            sleep( 1 );

            //"закрепляем" фотку
            $params = array(
                'gid'           =>  $this->vk_group_id,
                'server'        =>  $content->server,
                'hash'          =>  $content->hash,
                'photo'         =>  $content->$photo_list,
                'photos_list'   =>  $content->$photo_list,
                'access_token'  =>  $this->vk_access_token,
                'aid'           =>  $aid,
                'caption'       =>  $caption
            );

            $res = VkHelper::api_request( $method_save_photo, $params );
            if( isset( $res->error ))
                ;
            $res = $res[0];

            if( $destination == 'wall' )
                return $res->id;
            return "photo" . $res->owner_id . "_" . $res->pid;
        }
    }

?>
