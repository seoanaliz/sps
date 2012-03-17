<?php
   
    class ParserVkontakte {
        
        private $page_adr;

        private $count;

        const PAGE_SIZE = 20;

        const WALL_URL = 'http://vk.com/wall-';
        

        public function __construct($public_id = '') {
            if ($public_id) $this->page_adr = self::WALL_URL. $public_id;
        }
        
        //затычка, должен возвращать id, ссылку на аву по короткому названию
        //(http://vk.com/blabla)
        //из-за нее при создании класса не обязательно задавать page_adr
        public function get_public_info($url) {
            $a = $this->get_page($url);
            preg_match('/(?s)"public_avatar">.{0,6}<img src="(.*?jpg).*?>/', $a, $match); 
            $avatara = $match[1];
        }


        //возвращает Json с постами. поля:
        //likes - лайки
        //id - внутренний id поста в контакте
        //text
        //time - пусто
        //retweet - пусто
        //link - пусто
        //photo - масив фото
        //      id - внутренний id фотки в ко
        //      url - адрес фотки
        //      desc - описание фотки,
        //video - масив видео
        //      id - внутренний id
        //music - масив музыки
        //      id - внутренний id
        public function get_posts($page_number) {
            $offset = $page_number * self::PAGE_SIZE;
            if (!isset($this->count))
                $this->get_posts_count();
            
            if ($offset > $this->count) {
                //echo 'Ошибочка вышла, в паблике меньше записей';
                return false; 
            }

            //echo $this->page_adr."?offset=$offset".'<br>';
            $a = $this->get_page($this->page_adr."?offset=$offset");
            $document = phpQuery::newDocument($a);
            $hentry = $document->find('div.post_info');

            //разбираем страницу по постам
            $posts = array();
            $t = 0;
            foreach ($hentry as $el) {
                $pq = pq($el); 
                
                //лайки
                $likes = $pq->find('div.post_like')->text(); 
                if (!$likes) $likes = 0;
                $posts[$t]['likes'] = (int)$likes;
               
                //время
//                $time = $pq->find('div.replies > div.reply_link_wrap');
//                echo $time->find('span')->text();
////                iconv( "windows-1251", "utf-8", $time->find('span')->text());
                $posts[$t]['time'] = '';
                
                if (!$likes) $likes = 0;
                $posts[$t]['likes'] = (int)$likes;
                
                //контактовский номер поста
                $id = $pq->find('div.reply_link_wrap')->attr('id');
                if (!$id) throw new Exception(__CLASS__.'::' .__FUNCTION__.' не удалось получить id поста со стены ' . $this->page_adr);
                $posts[$t]['id'] = str_replace('wpe_bottom-', '', $id);
                
                //ретвит
                $posts[$t]['retweet'] = '';
                
                //ссылка
                $posts[$t]['link'] = '';
                
                //текст
                $text = $pq->find('div.wall_post_text')->html(); 
                    if (substr_count($text, '<span style="display: none">') > 0){
                        $text = explode('<span style="display: none">', $text);
                        $text = end($text);
                    }
                $text = $this->remove_tags($text);
           
                $posts[$t]['text'] = $text;

                //изображения
                $img_arr = array();
                $vid_arr = array();
                $mus_arr = array();
                $image = 0;
                $video = 0;
                $music = 0;
                
                foreach($pq->find('a') as $link){
                    $oncl = pq($link)->attr('onclick');
//                     echo $oncl . '<br>';
                       
                    if (substr_count($oncl, 'showPhoto') > 0){
                        preg_match("/showPhoto\('(.*?)',/", $oncl, $match);
                        if (!isset($match[1])) continue;
                        $img_arr[$image]['id'] = $match[1];
                        preg_match("/temp:({.*?})/", $oncl, $match);
                        if (isset($match[1])){
                            $match[1] = str_replace('x_:', '"x_":', $match[1]);
                            $match[1] = str_replace('y_:', '"y_":', $match[1]);
                            $match[1] = str_replace('z_:', '"z_":', $match[1]);
                            $match[1] = str_replace('base', '"base"', $match[1]);
                            $match =  (array)json_decode($match[1]);
                            
                            $link = $match['base'];
                            if (isset($match['z_'])) $postlink = $match['z_'][0];
                            else
                                if (isset($match['y_'])) $postlink = $match['y_'][0];
                                else
                                    if (isset($match['x_'])) $postlink = $match['x_'][0];
                                    else  {
                                    }
                            
                            $img_arr[$image]['url']  = $link.$postlink.'.jpg';
//                            echo $img_arr[$image]['url']. '<br>';
                            $image++; 
                        }
                      
                    }elseif (substr_count($oncl, 'showVideo') > 0){
                        
                         preg_match("/showVideo\('(.*?)',/", $oncl, $match);
                         if (!isset($match[1])) continue;
                         $vid_arr[$video]['id'] = $match[1];
//                         echo $vid_arr[$video]['id'];
                         $video++;
                         //playAudioNew('15779852_132162251_-22739050_5477')
                    }elseif(substr_count($oncl, 'playAudio') > 0){
                         preg_match("/playAudioNew\('(.*?)'/", $oncl, $match);
                         if (!isset($match[1])) continue;
                         $mus_arr[$music]['id'] = $match[1];
                         $music++;
                    }
                }

                //$this->get_photo_desc($img_arr);

                $posts[$t]['photo'] = $img_arr;
                $posts[$t]['video'] = $vid_arr;
                $posts[$t]['music'] = $mus_arr;
                
                //время TODO
                $posts[$t]['time'] = '';
                
                $t++;         
            }

            $photos_descr = $this->get_photos_descr($posts);

            unset($post);
            unset($pic);

            foreach ($posts as &$post) {
                if (empty($post['photo'])) continue;

                foreach($post['photo'] as &$pic){
                    if (!empty($photos_descr[$pic['id']])) {
                        $pic['descr'] = $photos_descr[$pic['id']];
                    }
                }
            }

            return $posts;
        }

        private function get_photos_descr($posts) {
            $result = array();
            //получаем описание всех фоток
            $curlData = array();

            foreach ($posts as $post) {
                if (empty($post['photo'])) continue;
                foreach($post['photo'] as $pic){
                    $curlData[] = 'http://vk.com/photo' . $pic['id'];
                }
            }

            while (!empty($curlData)) {
                $curlResult = $this->multiRequest($curlData);

                foreach ($curlResult as $i => $curlResultItem) {
                    if( mb_strpos($curlResultItem, '<button id="msg_back_button">') !== false ) {
                        continue;
                    }

                    $picId = str_replace('http://vk.com/photo', '', $curlData[$i]);

                    preg_match("/\"id\":\"{$picId}\".*?\"desc\":\"(.*?)\",\"hash\"/", $curlResultItem, $matches);
                    if (isset($matches[1]) && substr_count($matches[1], 'href') == 0){
                        $descr = $this->remove_tags($matches[1]);
                        $result[$picId] = $descr;
                    }

                    unset($curlData[$i]);
                }

                sleep(1);
            }

            return $result;
        }
        
        //возвращает количество постов паблика()
        public function get_posts_count() {
            $a = $this->get_page($this->page_adr);
            preg_match('/<div class="summary" id="fw_summary">(.*?)<\/div/', $a, $matches);
            $matches = $matches[1];
            $matches = str_replace('<span class="num_delim"> </span>', '', $matches );
            $count = explode(' ', $matches);
            if (!$count[1] )
                throw new Exception(__CLASS__.'::' .__FUNCTION__.' не удалось получить количество постов со стены ' . $this->page_adr);
            $this -> count = $count[1];
            //echo "<br>posts: $count[1]<br>";
            return $count[1];
        }
        
        private function get_page($page = '') {
            if (!$page) $page = $this->page_adr;
            $hnd = curl_init($page);
            curl_setopt($hnd, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($hnd, CURLOPT_FOLLOWLOCATION, true);
            $a = curl_exec($hnd);
            return $a;
       }

        private function remove_tags($text) {
            $text = str_replace( '<br>',    "\r\n", $text );
            $text = str_replace( '&#189;',  "½",    $text );
            $text = str_replace( '&#188;',  "¼",    $text );
            $text = str_replace( '&#190;',  "¾",    $text );
            $text = str_replace( '&#9658;', "►",    $text );
            $text = str_replace( '&#33;',   "!",    $text );
            $text = str_replace( '&#9829;', "",    $text );
            $text = htmlspecialchars_decode($text);
            $text = strip_tags( $text );
            return $text;
        }

        /**
         * Функция мульти запроса на CURL
         * @param array $data Данные для запроса
         * @param array $options Опции для всех запросов
         * @param array $oneoptions Опции для отдельных запросов
         * @return array
         */
        function multiRequest($data, $options = array(), $oneoptions = array()) {
            // Массив для ресурсов соединения
            $curls = array();
            // Массив для результатов
            $result = array();
            // Инициализация мульти запроса
            $mh = curl_multi_init();
            // Задание параметров запроса
            foreach ($data as $id => $d)
            {
                $curls[$id] = curl_init();
                $url = (is_array($d) && !empty($d['url'])) ? $d['url'] : $d;
                curl_setopt($curls[$id], CURLOPT_URL,            $url);
                curl_setopt($curls[$id], CURLOPT_HEADER,         false);
                curl_setopt($curls[$id], CURLOPT_RETURNTRANSFER, true);
                // Дополнительные опции общие запросов
                if (!empty($options))
                {
                    curl_setopt_array($curls[$id], $options);
                }
                // Дополнительные опции для определенного запроса
                if (!empty($oneoptions[$id]))
                {
                    curl_setopt_array($curls[$id], $oneoptions[$id]);
                }
                // Если post запрос
                if (is_array($d))
                {
                    if (!empty($d['post']))
                    {
                        curl_setopt($curls[$id], CURLOPT_POST,       1);
                        curl_setopt($curls[$id], CURLOPT_POSTFIELDS, $d['post']);
                    }
                }
                curl_multi_add_handle($mh, $curls[$id]);
            }
            // Выполняем запрос пока есть соединения
            $running = null;
            do
            {
                curl_multi_exec($mh, $running);
            }
            while($running > 0);
            // Получаем данные и закрываем соединения
            foreach($curls as $id => $content)
            {
                $result[$id] = curl_multi_getcontent($content);
                curl_multi_remove_handle($mh, $content);
            }
            curl_multi_close($mh);
            return $result;
        }
    }
?>