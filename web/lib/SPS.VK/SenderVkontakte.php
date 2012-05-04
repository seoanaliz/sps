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
        protected $link;                //ссылка на источник, не в тексте
        protected $sign;                //ссыль на пользователя, пока неактивно
        protected $header;              //заголовок ссылки
        const METH = 'https://api.vk.com/method/';

        public function __construct($post_data)
        {
            $this->post_photo_array = $post_data['photo_array']; //массив вида array('photoXXXX_YYYYYYY','...')
            $this->post_text = $post_data['text'];
            $this->vk_group_id = $post_data['group_id'];
            $this->vk_app_seckey = $post_data['vk_app_seckey'];
            $this->vk_access_token = $post_data['vk_access_token'];
            $this->audio_id = $post_data['audio_id'];//массив вида array('videoXXXX_YYYYYYY','...')
            $this->video_id = $post_data['video_id'];//массив вида array('audioXXXX_YYYYYYY','...')
            $this->link = $post_data['link'];
            $this->header = $post_data['header'];
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
                curl_setopt($ch, CURLOPT_POSTFIELDS, $arr_of_fields);

            } else return false;

            $result = curl_exec($ch);
            if (curl_errno($ch)){
                throw new Exception('error in curl: '. curl_error($ch)) ;
            }

            curl_close($ch);
            return $result;
        }

        public function send_post()
        {
            $attachment = array();
            $fields1 = array(    'gid'           =>  $this->vk_group_id,
                                 'access_token'  =>  $this->vk_access_token);
            //        if (is_array($this->post_photo_array)){
            foreach($this->post_photo_array as $photo_adr)
            {
                //первый запрос, получение адреса для заливки фото
                $url = self::METH . "photos.getWallUploadServer";
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
                $url2 = self::METH . "photos.saveWallPhoto";
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
            //        }
            $attachment = implode(',', $attachment);
            //todo  добавить другие аттачи
            if (!empty($this->audio_id)){
                $attachment .= ','.implode(',', $this->audio_id);
            }

            if (!empty($this->video_id)){
                $attachment .= ',' . implode(',', $this->video_id);
            }
            if (is_array($this->post_photo_array)) echo 'count = ' .  count($this->post_photo_array) . '<br>';

            if (($this->post_text == '©' || $this->post_text == '') && !is_array($this->post_photo_array)){
                $this->post_text =  "&#01;";
            }

            $arr_fields = array('owner_id'      =>  '-'.$this->vk_group_id,
                                'message'       =>  $this->post_text,
                                'access_token'  =>  $this->vk_access_token,
                                'attachment'    =>  $attachment);
            $url3 = self::METH . "/wall.post";

            $fwd3 = $this->qurl_request($url3, $arr_fields);

            $fwd3 = json_decode($fwd3);
            if (!empty ($fwd3->error)){

                $fwd3 = $fwd3->error;
                throw new exception("Error in wall.post : $fwd3->error_msg");
            }

            $tmp = $fwd3;
            $fwd3 = $fwd3->response;

            if (!empty($fwd3->post_id)) {
                if (!empty($this->link)){
                    $attachment .= ',' . $this->link;
                    sleep(0.3);
                    //удаление поста
                    $url = self::METH . 'wall.delete';

                    $params = array(
                        'owner_id'      =>  '-' . $this->vk_group_id,
                        'post_id'       =>  $fwd3->post_id,
                        'access_token'  =>  $this->vk_access_token
                    );
                    $fwd = $this->qurl_request($url, $params);
                    $fwd = json_decode($fwd);
                    if (!empty ($fwd3->error)){
                        $fwd3 = $fwd3->error;
                        throw new exception("Error in wall.delete : $fwd->error_msg");
                    }

                    $arr_fields = array(
                        'owner_id'      =>  '-'.$this->vk_group_id,
                        'message'       =>  $this->post_text,
                        'access_token'  =>  $this->vk_access_token,
                        'attachment'    =>  $attachment
                    );
                    $url3 = self::METH . "/wall.post";
                    sleep(0.3);

                    $fwd3 = $this->qurl_request($url3, $arr_fields);
                    $fwd3 = json_decode($fwd3);
                    if (!empty ($fwd3->error)){
                        $fwd3 = $fwd3->error;
                        throw new exception("Error in wall.post : $fwd3->error_msg");
                    }
                    return true;
                }

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

        //!!!распознование капчи - долгий и неблагодарный процесс(до полуминуты,
        // + он может вернуть нераспознанную)
        // нужно учитывать это время
        //
        //возвращает массив с номером капчи и ее распознанностью,
        // false в случае неправильной разгадки/недоступности работников распознавания
        //исключение в остальных случаях
        private function get_captcha()
        {


        }
    }

?>