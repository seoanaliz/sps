<?php
   
    class ParserVkontakte {

        private $page_adr;
        private $count;
        const PAGE_SIZE = 20;
        const WALL_URL = 'http://vk.com/wall-';
        const VK_URL = 'http://vk.com';

        //
        public function __construct($public_id = '') {
            if ($public_id) $this->page_adr = self::WALL_URL. $public_id;
        }



        //сюда приходит нечто вида vk.com/idXXXX, vk.com/publicXXXX либо vk.com/blabla
        //возвращает массив
        //      type    :   id(человек), public(Группа)
        //      id      :   контактовский номер чела/группы.
        //      avatarа :   адрес фотки юзера/группы (может принимать значение standard - одна из картинок контакта типа "недоступен", "ненадежен" и тп)
        //      name    :   имя/название паблика (паблик может не иметь названия)
        //      если страница удалена, вернет false. при проблемах с закачкой - exception
        public function get_info($url)
        {
            $a = $this->get_page($url);
            if (!$a) {

                throw new Exception('Не удалось скачать страницу '.$url);
            }

            if (substr_count($a, 'profile_avatar')> 0){
                if (!preg_match('/user_id":(.*?),/', $a, $oid));
                preg_match('/"loc":"\?id=(.*?)"/', $a, $oid);
                preg_match('/profile_avatar".*? src="(.*?)"/', $a, $ava);
                if (substr_count($ava[1], 'png') > 0 || substr_count($ava[1], 'gif') > 0) $ava = 'standard';
                else $ava = $ava[1];
                $err_counter = 0;
                if(!preg_match('/(?s)id="header.*?b>([^<].*?)<\/h1/', $a, $name)){
                    preg_match('/(?s)id="header.*?title">([^<].*?)<\/h1/', $a, $name);
                }
                $name = $name[1];
                return array(
                    'type'      =>  'id',
                    'id'        =>  $oid[1],
                    'avatarа'    =>  $ava,
                    'name'      =>  $name
                );

            } elseif(substr_count($a, 'public_avatar')> 0 || substr_count($a, 'group_avatar')> 0){
                preg_match('/(?s)top_header">(.*?)<\/div>/', $a, $name);
                if (!preg_match('/group_id":(.*?),/',$a, $gid))
                    if(!preg_match('/loc":"\?gid=(.*?)[&"]/',$a, $gid))
                        preg_match('/public.init\(.*?id":(.*?),/', $a, $gid);
                preg_match('/(?s)id=".*?avatar.*?src="(.*?)"/', $a, $ava);
                if (substr_count($ava[1], 'png')>0 || substr_count($ava[1], 'gif')>0) $ava = 'standard';
                else $ava = !empty($ava[1]) ? $ava[1] : null;
                return array(
                    'type'      =>  'public',
                    'id'        =>  $gid[1],
                    'avatarа'    =>  $ava,
                    'name'      =>  $name[1]
                );
            }

            return false;
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
        //
        //$trig_inc - нужно ли собирать текст с
        public function get_posts($page_number, $trig_inc = 1)
        {
            $offset = $page_number * self::PAGE_SIZE;
            if (!isset($this->count))
                $this->get_posts_count();

            if ($offset > $this->count) {
                return false;
            }

            $a = $this->get_page($this->page_adr."?offset=$offset");
            if (!$a) {
                throw new Exception('Не удалось скачать страницу '.$this->page_adr."?offset=$offset");
            }
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
                $time = $pq->find('div.replies > div.reply_link_wrap');
                $time =  $time->find('span')->text();
                //                iconv( "windows-1251", "utf-8", $time->find('span')->text());
                $time = $this->get_time($time);
                if (!$time)
                    throw new Exception(__CLASS__.'::' .__FUNCTION__.
                        ' не удалось получить time поста со стены ' . $this->page_adr);
                $posts[$t]['time'] = $time;

                //контактовский номер поста
                $id = $pq->find('div.reply_link_wrap')->attr('id');
                if (!$id) throw new Exception(__CLASS__.'::' .__FUNCTION__.
                    ' не удалось получить id поста со стены ' . $this->page_adr);
                $posts[$t]['id'] = str_replace('wpe_bottom-', '', $id);

                //ретвит
                $retwitt = $pq->find('a.published_by')->attr('href');
                if ($retwitt){

                    $posts[$t]['retwitt'] = $this->get_info(self::VK_URL.$retwitt);
                } else $posts[$t]['retwitt'] = '';

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

                    if (substr_count($oncl, 'showPhoto') > 0){
                        preg_match("/showPhoto\('(.*?)',/", $oncl, $match);
                        if (!isset($match[1])) continue;
                        $img_arr[$image]['id'] = $match[1];
                        $img_arr[$image]['desc'] = '';

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
                                        throw new Exception(__CLASS__.'::' .__FUNCTION__.
                                            " не удалось получить фото поста $id со стены " . $this->page_adr);
                                    }

                            $img_arr[$image]['url']  = $link.$postlink.'.jpg';
                            $image++;
                        }

                    }elseif (substr_count($oncl, 'showVideo') > 0){
                        preg_match("/showVideo\('(.*?)',/", $oncl, $match);
                        if (!isset($match[1])) continue;
                        $vid_arr[$video]['id'] = $match[1];
                        $video++;

                    }elseif(substr_count($oncl, 'playAudio') > 0){
                        preg_match("/playAudioNew\('(.*?)'/", $oncl, $match);
                        if (!isset($match[1])) continue;
                        $mus_arr[$music]['id'] = $match[1];
                        $music++;

                    }
                }

                //получение описания каждой фотки
                if ($trig_inc && count($img_arr) > 0) {
                    $this->get_photo_desc($img_arr);//спорно
                }

                $posts[$t]['photo'] = $img_arr;
                $posts[$t]['video'] = $vid_arr;
                $posts[$t]['music'] = $mus_arr;


                $t++;
            }

            return $posts;
        }

        //возвращает количество постов паблика()
        public function get_posts_count()
        {
            $a = $this->get_page($this->page_adr);
            preg_match('/<div class="summary" id="fw_summary">(.*?)<\/div/', $a, $matches);
            $matches = $matches[1];
            $matches = str_replace('<span class="num_delim"> </span>', '', $matches );
            $count = explode(' ', $matches);
            if (!$count[1] )
                throw new Exception(__CLASS__.'::' .__FUNCTION__.' не удалось получить количество постов со стены ' . $this->page_adr);
            $this -> count = $count[1];
            return $count[1];
        }

        private function get_page($page = '')
        {
            if (!$page) $page = $this->page_adr;
            $hnd = curl_init($page);
            //            curl_setopt($hnd , CURLOPT_HEADER, 1);
            curl_setopt($hnd, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($hnd, CURLOPT_FOLLOWLOCATION, true);
            $a = curl_exec($hnd);
            if (curl_errno($hnd)) return false;
            return $a;
        }

        private function remove_tags($text)
        {
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

        private function get_photo_desc(&$picsArr)
        {
            if(count($picsArr)<=0) return false;

            $hArr = array();//handle array

            foreach($picsArr as &$pic){
                $h = curl_init();
                curl_setopt($h,CURLOPT_URL,'http://vk.com/photo' . $pic['id']);
                curl_setopt($h,CURLOPT_HEADER,0);
                curl_setopt($h,CURLOPT_RETURNTRANSFER,1);
                curl_setopt($h, CURLOPT_FOLLOWLOCATION, true);
                array_push($hArr,$h);
            }

            $mh = curl_multi_init();
            foreach($hArr as $k => $h)
                curl_multi_add_handle($mh,$h);

            $running = null;
            do{
                curl_multi_exec($mh, $running);
            }while($running > 0);

            // get the result and save it in the result ARRAY
            foreach($hArr as $k => $h){
                $desc = curl_multi_getcontent($h);
                preg_match("/\"id\":\"{$pic['id']}\".*?\"desc\":\"(.*?)\",\"hash\"/", $desc, $matches);
                //                $document = phpQuery::newDocument($desc);
                if (isset($matches[1]) && substr_count($matches[1], 'href') == 0){
                    $pic['desc'] =  $this->remove_tags($matches[1]);
                }

            }

            curl_multi_close($mh);

            return true;
        }

        private function get_time($date)
        {
            //начало сегодняшнего дня (для сегодняшних постов)
            $date = trim($date);
            $da = date("d,m,Y");
            $da = explode(',' ,$da);
            $today_zero = mktime(0, 0, 0, $da[1], $da[0], $da[2]);

            if (is_numeric($date) && strlen($date) == 10) return $date;
            $nowtime = time() + 10800;
            //случай с недавним постом(в пределах 5 минут)
            if (substr_count($date, 'одну') > 0 || substr_count($date, 'две') > 0
                ||  substr_count($date, 'три') > 0){

                $result = $nowtime;
                //случай с недавним постом(до 3 часов](точность в пределах часа ))
            } elseif (substr_count($date, 'назад') > 0){
                if (substr_count($date, 'час '))
                    $result = $nowtime - 3600;

                elseif (substr_count($date, 'часа'))
                    $result = $nowtime - reset(explode(' ', trim($date)))*3600;

                elseif (substr_count($date, 'минут'))
                    $result = $nowtime - reset(explode(' ', trim($date)))*60;

                //случай с постом этого года, точность в пределах минут
            } elseif(substr_count($date, ' в ') > 0) {
                $tmp = explode(' в ', trim($date));

                //разбор времени
                $time = explode(':', $tmp[1]);
                $time = $time[0] * 3600 + $time[1] * 60;

                //разбор даты
                $tmp[0] = trim($tmp[0]);
                if(substr_count($tmp[0], 'сегодня') > 0){
                    $result = $today_zero + $time;

                } elseif (substr_count($tmp[0], 'вчера') > 0){
                    $result = $today_zero + $time - 86400;

                } elseif(substr_count($tmp[0], ' ') > 0){
                    $tmp2 = explode(' ', $tmp[0]);
                    if (!$month = $this->get_month ($tmp2[1])) return false;
                    $result = mktime(0, 0, 0, $month, $tmp2[0], 2012) + $time;
                }

                //случай с постом до этого года, точность - в пределах суток
            } elseif(substr_count($date, ' ') == 2){
                $date = explode(' ', $date);
                if (!$date[1] = $this->get_month(trim($date[1]))) return false;
                $result = mktime(12, 0, 0, $date[1], $date[0], $date[2]);
            }

            return $result;

        }

        private function get_month($text_mon)
        {
            //омфг
            $text_mon = (string)$text_mon;
            switch ($text_mon){
                case 'янв': $month = 1; break;
                case 'фев': $month = 2; break;
                case 'мар': $month = 3; break;
                case 'апр': $month = 4; break;
                case 'мая': $month = 5; break;
                case 'июн': $month = 6; break;
                case 'июл': $month = 7; break;
                case 'авг': $month = 8; break;
                case 'сен': $month = 9; break;
                case 'отк': $month = 10; break;
                case 'ноя': $month = 11; break;
                case 'дек': $month = 12; break;
                default: return false;
            }
            return $month;
        }
    }
?>