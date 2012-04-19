<?php
header("Content-Type: text/html; charset=utf-8");
class Vsend{

    protected $post_photo_array;    //массив адресов фоток
    protected $post_text;           //текст поста
    protected $attachments = '';    //аттачи
    protected $vk_access_token;
    protected $vk_group_id;         //id паблика, куда постим
    protected $vk_aplication_id;    //id аппа, с которого постим
    protected $vk_app_seckey;       //
    protected $link;                //ссылка на источник
    protected $sign;                //ссыль на пользователя, пока неактивно
    protected $header;              //заголовок ссылки
    const METH = 'https://api.vk.com/method/';
    
    public function __construct($post_date)
    {
        $post_date = json_decode($post_date);
        print_r($post_date);
        $this->post_photo_array     = $post_date -> photo_array; //массив путей к фоткам
        $this->post_text            = $post_date -> text;
        $this->vk_group_id          = $post_date -> group_id;
        $this->vk_app_seckey        = $post_date -> vk_app_seckey;
        $this->vk_access_token      = $post_date -> vk_access_token;
        $this->audio_id             = $post_date -> audio_id;//массив вида array('videoXXXX_YYYYYYY','...')
        $this->video_id             = $post_date -> video_id;//массив вида array('audioXXXX_YYYYYYY','...')
        $this->link                 = $post_date -> link;
        $this->header               = $post_date -> header; 
    }
    
    private function qurl_request($url, $arr_of_fields, $headers = '', $uagent = '')
    {
        if (empty($url)) {
            return false; 
        }
        
        $ch = curl_init($url);
        print_r($arr_of_fields);
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
            echo "<br>error in curl: ". curl_error($ch) ."<br>";
            throw new Exception('error in curl: '. curl_error($ch)) ;
        }
  
        curl_close($ch); 
        return $result; 
    }
    
    public function send_post()
    {
        $try_cntr = 0; #счетчик количества попыток послать запрос
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
                    echo '<br>ERROR!<br>';
                    print_r($fwd);
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
    
                    print_r($fwd2);
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
            $attachment .= ','.implode(',', $this->video_id);
        }
          
        if($this->post_text == ''){
            $this->post_text = $this->header;
        }
        
        if ($this->post_text =='©' || $this->post_text == '') {
            $this->post_text = "&#01;";
        }
            
        $arr_fields = array('owner_id'      =>  '-'.$this->vk_group_id,
                            'message'       =>  $this->post_text,
                            'access_token'  =>  $this->vk_access_token,
                            'attachment'    =>  $attachment);
        $url3 = self::METH . "/wall.post";
       
        
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
            if (!empty($this->link)){
                $attachment .= ',' . $this->link;
                $url = self::METH . 'wall.delete';
                
                $params = array(
                                'owner_id'      =>  '-'.$this->vk_group_id,
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
                        
                $try_cntr = 0;
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

//данные тестового паблика
//$vk_group_id = 27421965;
//$vk_aplication_id = 2842244; //id аппа
//$vk_access_token = 'c7ac1842c3ddb889c3ddb88974c3f6e60dcc3ddc3d8801f85c16a3c7336abd4'; //
//$vk_app_seckey = V1us1w3lbkoaapuYiddg;

//заголовок - поле header
try{
    echo 'start<br>';
    $a = new Vsend($post_array);
    echo $a->send_post();

 
} catch (Exception $e){
    $err = $e->getMessage();
    echo '<br>'.$err;

}
?>