<?php

    /**
     * SenderVkontakte
     * @package    SPS
     * @subpackage VK
     * @author     Shuler
     */
    class SenderVkontakte {
        protected $post_photo_array;    //массив адресов фоток
        protected $post_text;           //текст поста
        protected $attachments = '';    //аттачи
        protected $vk_access_token;
        protected $vk_group_id;         //id паблика, куда постим
        protected $vk_aplication_id;    //id аппа, с которого постим
        protected $vk_app_seckey;       //
        protected $link;                //ссылка на источник
        protected $sign;                //ссыль на пользователя, пока неактивно

        public function __construct($post_data)
        {
            $this->post_photo_array = $post_data['photo_array']; //массив вида array('photoXXXX_YYYYYYY','...')
            $this->post_text = $post_data['text'];
            $this->vk_group_id = $post_data['group_id'];
            $this->vk_app_seckey = $post_data['vk_app_seckey'];
            $this->vk_access_token = $post_data['vk_access_token'];
            $this->audio_id = $post_data['audio_id'];//массив вида array('videoXXXX_YYYYYYY','...')
            $this->video_id = $post_data['video_id'];//массив вида array('audioXXXX_YYYYYYY','...')
            //todo пока линк можно вставить только в тело сообщения(иначе фото(только фото!) не отобразится)
            $this->link = $post_data['link'];
        }

        private function qurl_request($url, $arr_of_fields, $headers = '', $uagent = '')
        {
            if (empty($url)) {
                return false;
            }

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

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
                curl_setopt($ch, CURLOPT_POSTFIELDS, $arr_of_fields);

            } else return false;

            $result = curl_exec($ch);
            if (curl_errno($ch)){
                return 'error in curl: '. curl_error($ch);
            }

            curl_close($ch);
            return $result;
        }

        public function send_post()
        {
            $try_cntr = 0; #счетчик количества попыток послать запрос
            $attachment = array();

            $fields1 = array(    'gid'           =>  $this->vk_group_id,
                                 'access_token'  =>  $this->vk_access_token );
            foreach($this->post_photo_array as $photo_adr)
            {
                //первый запрос, получение адреса для заливки фото
                $url = "https://api.vkontakte.ru/method/photos.getWallUploadServer";
                $fwd = $this->qurl_request($url, $fields1);
                $tmp = $fwd;
                //декодируем результат
                $fwd = json_decode($fwd);

                if (!empty ($fwd->error)){
                    $fwd = $fwd->error;
                    throw new exception("Error in photos.getWallUploadServer : $fwd->error_msg");
                }

                $fwd = $fwd -> response;

                sleep(1);
                $upload_url = $fwd -> upload_url;

                if(empty($fwd->upload_url)){
                    throw new exception("Smthg wrong in photos.getWallUploadServer : $tmp");
                }

                //заливка фото
                $content = $this->qurl_request($upload_url, array('file1' => '@'.$photo_adr));
                $content = json_decode($content);
                if (empty($content->photo)) {
                    throw new exception(" Error uploading photo. Response : $content");
                }

                sleep(1);
                //"закрепляем" фотку
                $url2 = "https://api.vkontakte.ru/method/photos.saveWallPhoto";
                $fields = array(    'gid'           =>  $this->vk_group_id,
                                    'server'        =>  $content->server,
                                    'hash'          =>  $content->hash,
                                    'photo'         =>  $content->photo,
                                    'access_token'  =>  $this->vk_access_token );

                $fwd2 = $this->qurl_request($url2, $fields);
                $fwd2 = json_decode($fwd2);
                if (!empty ($fwd2->error)){
                    $fwd2 = $fwd2->error;
                    throw new exception("Error in photos.saveWallPhoto : $fwd2->error_msg");
                }

                $fwd2 = $fwd2->response;
                $fwd2 = $fwd2[0];
                $attachment[] = $fwd2->id;
            }

            $attachment = implode(',', $attachment);
            //todo  добавить другие аттачи
            if (!empty($this->audio_id)){
                $attachment .= ','.implode(',', $this->audio_id);
            }

            if (!empty($this->video_id)){
                $attachment .= ','.implode(',', $this->video_id);
            }

            //todo
            if (!empty($this->link)){
                $this->post_text .= "\r\n". $this->link;
                //                 $attachment .= ','.$this->link;
            }

            $arr_fields = array('owner_id'      =>  '-'.$this->vk_group_id,
                                'message'       =>  $this->post_text,
                                'access_token'  =>  $this->vk_access_token,
                                'attachment'    =>  $attachment);
            $url3 = "https://api.vkontakte.ru/method/wall.post";
            $try_cntr = 0;
            $fwd3 = $this->qurl_request($url3, $arr_fields);
            $fwd3 = json_decode($fwd3);
            if (!empty ($fwd3->error)){
                $fwd3 = $fwd3->error;
                throw new exception("Error in wall.post : $fwd3->error_msg");
            }

            $tmp = $fwd3;
            $fwd3 = $fwd3->response;
            if (!empty($fwd3->post_id)) {
                return $fwd3->post_id;# вернет id поста
            } elseif(!empty($fwd->processing)){
                return true;
            }else{
                throw new exception("Error in response : $tmp");
            }
        }

        private function remove_tags()
        {
            $this->post_text = str_replace( '<br>', "\r\n", $this->post_text );
            $this->post_text = htmlspecialchars_decode($this->post_text);
            $this->post_text = strip_tags( $this->post_text );
        }
    }

?>