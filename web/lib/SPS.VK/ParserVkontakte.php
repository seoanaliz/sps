<?php

    class ParserVkontakte {

        private $page_adr;
        private $page_id;
        private $page_short_name;
        private $count;

        const MAP_SIZE = 'size=180x70';//контактовское значение для размера карт
        const MAP_NEW_SIZE = 'size=360x140';//то значение, на которое ^ надо заменить
        const PAGE_SIZE = 20;
        const LIMIT_BREAK = 30;//порог отсева постов по лайкам, в процентах
        const LIKES_LIMIT = 20;//ниже этого порога лайков всем постам выставляется "-"
        const WALL_URL = 'http://vk.com/wall-';
        const VK_URL = 'http://vk.com';
        const GET_PHOTO_DESC = true; // собирать ли внутреннее описание фото (очень нестабильно и долго)
        const TESTING = false;
        /**
         * Максимальное количество постов, для которых можно запросить лайки
         */
        const MAX_POST_LIKE_COUNT = 90;

        public function __construct($public_id = '')
        {
            if ($public_id != '') $this ->set_page($public_id);
        }

        public function set_page($id, $sh_name = '')
        {
            $this->page_adr         =   self::WALL_URL . $id;
            $this->page_id          =   $id;
            $this->page_short_name  =   $sh_name;

        }

        //сюда приходит нечто вида vk.com/idXXXX, vk.com/publicXXXX либо vk.com/blabla
        //возвращает массив
        //      type        :   id(человек), public(Группа)
        //      id          :   контактовский номер чела/группы.
        //      avatarа     :   адрес фотки юзера/группы (может принимать значение standard - одна из картинок контакта типа "недоступен", "ненадежен" и тп)
        //      name        :   имя/название паблика (паблик может не иметь названия)
        //      short_name  :   короткий адрес(берется из ссылки, то есть может быть вида id234242, vasyapupkin...)
        //      если страница удалена, вернет false. при проблемах с закачкой - exception
        public function get_info($url)
        {
            if (self::TESTING) echo '<br>get info'.$url . '<br>';
            $a = $this->get_page($url);
            if (!$a) {
                throw new Exception('Не удалось скачать страницу '.$url);
            }

            $url = trim($url, '/');
            $short_name = end(explode('/',$url));

            if (substr_count($a, 'profile_avatar')> 0) {
                if (!preg_match('/user_id":(.*?),/', $a, $oid))
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
                    'id'        =>  !empty($oid[1]) ? $oid[1] : null,
                    'avatara'   =>  $ava,
                    'name'      =>  $name,
                    'short_name' =>     $short_name
                );

            } elseif(substr_count($a, 'public_avatar')> 0 || substr_count($a, 'group_avatar')> 0) {
                if (substr_count($a, 'public_avatar')> 0 )
                    $type = 'public';
                else
                    $type = 'group';

                preg_match('/(?s)top_header">(.*?)<\/div>/', $a, $name);
                if (!preg_match('/group_id":(.*?),/',$a, $gid))
                    if(!preg_match('/loc":"\?gid=(.*?)[&"]/', $a, $gid))
                        preg_match('/public.init\(.*?id":(.*?),/', $a, $gid);
                preg_match('/(?s)id=".*?avatar.*?src="(.*?)"/', $a, $ava);
                if (substr_count($ava[1], 'png') > 0 || substr_count($ava[1], 'gif') > 0)
                    $ava = 'standard';
                else
                    $ava = $ava[1];
                if (!preg_match('/Groups.init.*\"loc\":\"(.*?)\"/', $a, $short_name))
                    if (!preg_match('/public.init.*?\"public_link\":\"(.*?)\"/', $a, $short_name))
                        echo 'error _____ preg_match()<br><br>';
                $short_name = $short_name[1];
                $short_name = str_replace('/', '', $short_name);
                $short_name = str_replace('\\', '', $short_name);
                preg_match('/(?s)id=\"public_followers\".*?span class=\"fl_r\".?>.*?(\d.*?)<\/div>/', $a, $population);
                $population = str_replace('<span class="num_delim"> </span>', '', (!empty($population[1]) ? $population[1] : ''));
                $population =  (int)$population;
                return array(
                    'type'       =>     $type,
                    'id'         =>     $gid[1],
                    'avatara'    =>     $ava,
                    'name'       =>     !empty($name[1]) ? $name[1] : '',
                    'short_name' =>     $short_name,
                    'population' =>     $population
                );
            }
            return false;
        }

        //возвращает Json с постами. поля:
        //likes - относительные лайки. возможные значения:
        //          -1               пост не прошел отбора, его не нужно выводить
        //          "-"              лайков у поста меньше 20(попадает в выдачу из-за
        //                           того, что остальные посты +- такие же)
        //          "x%"(1<x<~100)   относительная крутизна поста

        //likes_tr - абсолютные лайки
        //id - внутренний id поста в контакте
        //text
        //time - время
        //retweet - массив с информацией об источнике поста
        //link -
        //photo - масив фото
        //      id - внутренний id фотки в ко
        //      url - адрес фотки
        //      desc - описание фотки,
        //video - масив видео
        //      id - внутренний id
        //music - масив музыки
        //      id - внутренний id
        //map  - ссылка на кусок googl map(360х140)
        //poll - ссылка на контактовское голосование. данные по нему уже чз api надо выдирать
        //text_links - массив линков внутри текста,пока заглушка позже
        //doc - линк на контактовский документ
        //
        //
        //возвращает false при отсутствии валидных записей
        //exception 'wall's end' при достижении конца стены
        //
        //$page_number - id страницы,
        //$short_name - короткое имя, нужно для проверки, было ли собщение от
        //              группы или посетителей(от последних отсеивается)
        //$trig_inc - нужно ли собирать внутренний текст с фото
        //

        public function get_posts_nv($page_number, $trig_inc = false)
        {
            $offset = $page_number * self::PAGE_SIZE;
            if (!isset($this->count))
                $this->get_posts_count();

            echo 'offset<br>';
            print_r( $offset );
            echo "offset<br>
            $this->count";

            if ($offset > $this->count) {
                throw new Exception("wall's end");
            }

            $a = $this->get_page($this->page_adr."?offset=$offset&own=1");
            if (!$a) {
                throw new Exception('Не удалось скачать страницу '.$this->page_adr."?offset=$offset");
            }
//            file_put_contents('1.txt', $a );
            preg_match_all('/(?s)<body.*?>(.*)<script id/', $a, $matches);
            $a = $matches[1][0] . '</div></div>';
            file_put_contents('1.txt', $a );
            $document = phpQuery::newDocument( $a );
            echo '<br>1';
            $hentry = $document->find('div.post');

            //разбираем страницу по постам
            $posts = array();
            $t = 0;
            foreach ($hentry as $el) {
                $pq = pq($el);

                //определение авторства поста. Нужно только для групп
                //                $author =  $pq->find('a.author')->attr('href');
                //                $author = str_replace('/',  '', $author);
                //                $author = str_replace('\\', '', $author);
                //
                //                if ( $author != $this->page_short_name) {
                //                    echo '<br>несовпадение!<br>';
                //                    continue;
                //                }

                //контактовский номер поста
                $id = $pq->find('div.reply_link_wrap')->attr('id');
                             if (!$id) throw new Exception(__CLASS__.'::' .__FUNCTION__.
                    ' не удалось получить id поста со стены ' . $this->page_adr);
                $posts[$t]['id'] = str_replace('wpe_bottom-', '', $id);

                //голосования
                $poll = $pq->find('div.page_media_poll')->attr('id');
                $poll = str_replace('post_poll', '', $poll);
                if (!$poll) $poll = '';
                $posts[$t]['poll'] = $poll;

                //карты
                $maps  =  $pq->find('img.page_media_map')->attr('src');
                $maps  = str_replace(self::MAP_SIZE, self::MAP_NEW_SIZE, $maps);
                if (!$maps) $maps= '';
                $posts[$t]['map'] = $maps;


                //лайки
                $likes = $pq->find('div.post_like')->text();
                if (!$likes) $likes = 0;
                $posts[$t]['likes'] = (int)$likes;
                $posts[$t]['likes_tr'] = (int)$likes;

                //время
                $time = $pq->find('div.replies > div.reply_link_wrap');
                $time = $time->find('span')->text();
                $time = $this->get_time($time);

                if (!$time)
                    throw new Exception(__CLASS__.'::' .__FUNCTION__.
                        " не удалось получить time поста $id со стены " . $this->page_adr);
                $posts[$t]['time'] = $time;

                //ретвит
                $retweet = $pq->find('a.published_by')->attr('href');
                if ($retweet){

                    $posts[$t]['retweet'] = $this->get_info(self::VK_URL.$retweet);
                } else $posts[$t]['retweet'] = array();


                //текст
                $text = $pq->find('div.wall_post_text')->html();
                if (substr_count($text, '<span style="display: none">') > 0){
                    $text = explode('<span style="display: none">', $text);
                    $text = end($text);
                }
                //                    if (substr_count($text, 'section=search') > 0){
                //                        preg_match_all('/>#.*?</', $text, $matches);
                //                        print_r($matches);
                //                        $text = preg_replace('/(?s)(.*?)(<a onclick.*?href.*?\&amp.*?\/a>)(.*)/','$1<<###>>$3', $text);
                //
                //                    }

                $text = $this->remove_tags($text);
                $posts[$t]['text'] = $text;

                //ссылки, хештеги
                //в текст будут вставлятся 'якоря', к которым будут привязыватся ссылки и хеши
                $posts[$t]['text_links'] = array();


                //изображения, видео, аудио
                $img_arr = array();
                $vid_arr = array();
                $mus_arr = array();
                $image = 0;
                $video = 0;
                $music = 0;
                $posts[$t]['link'] = '';
                $posts[$t]['doc'] = '';

                foreach($pq->find('a') as $link){
                    $oncl = pq($link)->attr('onclick');

                    //фото
                    if (substr_count($oncl, 'showPhoto') > 0){
                        preg_match("/showPhoto\('(.*?)',/", $oncl, $match);
                        if (!isset($match[1])) continue;

                        $img_arr[$image]['id'] = $match[1];
                        $img_arr[$image]['desc'] = '';

                        //продгоняем инфу о фото под формат json
                        preg_match("/temp:({.*?})/", $oncl, $match);
                        if (isset($match[1])){
                            $match[1] = str_replace('x_:', '"x_":', $match[1]);
                            $match[1] = str_replace('y_:', '"y_":', $match[1]);
                            $match[1] = str_replace('z_:', '"z_":', $match[1]);
                            $match[1] = str_replace('base', '"base"', $match[1]);
                            $match =  (array)json_decode($match[1]);
                            //выбираем фото с макс разрешением
                            $link = $match['base'];
                            if (isset($match['z_'])) $postlink = $match['z_'][0];
                            else
                                if (isset($match['y_'])) $postlink = $match['y_'][0];
                                else
                                    if (isset($match['x_'])) $postlink = $match['x_'][0];
                                    else  {
                                        throw new Exception(__CLASS__.'::' .__FUNCTION__.
                                            " не удалось получить фото поста $id со стены " . $this->page_adr."?offset=$offset");
                                    }

                            if (substr_count($postlink, 'http') > 0 )
                                $img_arr[$image]['url']  = $postlink . '.jpg';
                            else {
                                $img_arr[$image]['url']  = $link . $postlink . '.jpg';
                            }
                            $image++;
                        }

                    //видео
                    }elseif (substr_count($oncl, "act: 'graffiti'") > 0){
                        $img_arr[$image]['id'] = pq($link)->attr('href');
                        $img_arr[$image]['desc'] = '';
                        $img_arr[$image]['url'] = pq($link)->find('img')->attr('src');;
                        if (self::TESTING) print_r($img_arr[$image]);
                        if (!$img_arr[$image]['id'] || !$img_arr[$image]['url'])
                            throw new Exception(__CLASS__.'::' .__FUNCTION__.
                                " не удалось получить данные поста (гаффити)
                                            $id со стены " . $this->page_adr."?offset=$offset");
                        $image++;

                    }elseif (substr_count($oncl, 'showVideo') > 0){
                        preg_match("/showVideo\('(.*?)',/", $oncl, $match);
                        if (!isset($match[1])) continue;
                        $vid_arr[$video]['id'] = $match[1];
                        $video++;

                    //музыка
                    }elseif(substr_count($oncl, 'playAudio') > 0){
                        preg_match("/playAudioNew\('(.*?)'/", $oncl, $match);
                        if (!isset($match[1])) continue;
                        $mus_arr[$music]['id'] = $match[1];
                        $music++;

                    //линки и документы
                    }elseif (pq($link)->attr('class') == 'lnk') {
                        $link = pq($link)->attr('href');

                        $doc = '';
                        if (substr_count($link, '/away.php?to=') > 0){
                            $link = str_replace('/away.php?to=', '', $link);
                            $link = urldecode($link);
                        }elseif (substr_count($link, '/doc') > 0){
                            $doc = $link;
                            $link = '';
                        }else{
                            $link = '';
                            $doc = '';
                        }

                        $posts[$t]['link'] = $link;
                        $posts[$t]['doc'] = $doc;
                    }
                }

                //получение описания каждой фотки, крайне сомнительная вещь
                if ( count($img_arr) > 0 and $trig_inc) {
                    $this->get_photo_desc($img_arr, $text);//спорно
                }

                $posts[$t]['photo'] = $img_arr;
                $posts[$t]['video'] = $vid_arr;
                $posts[$t]['music'] = $mus_arr;
                unset($img_arr);
                unset($vid_arr);
                unset($mus_arr);
                unset($id);
                unset($poll);
                unset($maps);
                unset($retweet);
                unset($time);
                unset($text_links);
                unset($doc);

                if (self::TESTING){
                    echo '<br>---------------------------------<br>';
                    foreach ($posts[$t] as $k=>$v){
                        echo $k.' = ';
                        print_r($v);
                        echo '<br>';
                    }
                }

                $t++;
            }

            if (count($posts) > 0){
                $posts = $this->kill_attritions($posts);
                return $posts;
            } else{
                //                echo '<br>zero<br>';
                return false;
            }
        }

        public function get_posts( $page_number )
        {
            $offset = $page_number * self::PAGE_SIZE;
            $params = array(
                'owner_id'  =>  '-' . $this->page_id,
                'offset'    =>  $offset,
                'count'     =>  20,
                'fileter'   =>  'owner'
            );

            $res = VkHelper::api_request( 'wall.get', $params );
            unset( $res[0] );
            $posts = $this->post_conv( $res );
            $posts = $this->kill_attritions( $posts );

            return $posts;
        }

        private function post_conv( $posts, $trig_inc = false )
        {
            $result_posts_array = array();

            foreach( $posts as $post ) {
                $id         =   $this->page_id . '_' . $post->id;
                $likes      =   $post->likes->count;
                $likes_tr   =   $likes;
                $retweet    =   $post->reposts->count;
                $time       =   $post->date;
                $text       =   TextHelper::fromUTF8( $this->remove_tags( $post->text ));
                $maps = '';
                $doc  = '';
                $link = '';
                $poll = '';
                $photo = array();
                $video = array();
                $audio = array();
                $text_links = array();

                if ( isset( $post->attachments )) {
                    foreach( $post->attachments as $attachment )
                    {

                        switch( $attachment->type ) {
                            case 'photo':
                                 $photo[] =
                                     array(
                                         'id'   =>  $attachment->photo->owner_id . '_' . $attachment->photo->pid,
                                         'desc' =>  '',
                                         'url'  =>  isset( $attachment->photo->src_big ) ?
                                                                    $attachment->photo->src_big :
                                                                    $attachment->photo->src
                                     );
                                 break;
                            case 'graffiti':
                                 $photo[] =
                                     array(
                                         'id'   =>  $attachment->graffiti->owner_id . '_' . $attachment->graffiti->gid,
                                         'desc' =>  '',
                                         'url'  =>  isset( $attachment->graffiti->big_src ) ?
                                                                    $attachment->graffiti->big_src :
                                                                    $attachment->graffiti->src
                                     );
                                 break;
                            case 'audio':
                                $audio[] = array( $attachment->audio->owner_id . '_' . $attachment->audio->aid );
                                break;
                            case 'video':
                                $video[] = array( $attachment->video->owner_id . '_' . $attachment->video->vid );
                                break;
                            case 'link':
                                $link =  $attachment->link->url;
                                break;
                            case 'poll':
                                $poll = $attachment->poll->poll_id;
                                 break;
                            default:
                                break;
                        }
                    }
                }

                $result_posts_array[] = array('id'      => $id,      'likes' => $likes, 'likes_tr' => $likes_tr,
                                              'retweet' => $retweet, 'time'  => $time,  'text'     => $text,
                                              'map'     => $maps,    'doc'   => $doc,   'photo'    => $photo,
                                              'music'   => $audio,   'video' => $video, 'link'     => $link,
                                              'poll'    => $poll,    'text_links'   =>  $text_links  );
            }
            print_r($result_posts_array);
            return $result_posts_array;
        }

        private function get_average(array &$a)
        {
            $q = count($a);
            $sum = 0;
            foreach( $a as $post ){

                if (substr_count($post['likes'], '%') > 0 ||
                    substr_count($post['likes'], '+') > 0 ||
                    $post['likes'] == -1){
                    $q--;
                }
                else
                    $sum += $post['likes'];
            }
            //            echo 'cymma = ' . $sum . 'and q = ' . $q . '<br>';
            return ( $sum / $q );
        }

        private function kill_attritions($array)
        {
            $res = array();
            $sr =  $this->get_average($array);

            $i = 0;
            $t = 0;
            //отсев крупных
            while(isset($array[$i]['likes'])){
                if ($array[$i]['likes'] > ($sr * 2) ){
                    if ($sr > 1){
                        $array[$i]['likes'] = '+' ;

                    }else
                        $array[$i]['likes'] = '-';

                    $t ++;
                }
                $i++;
            }

            $sr =  $this->get_average($array);

            $i = 0;
            $t = 0;

            //отсев мелких
            while(isset($array[$i]['likes'])){

                if (substr_count($array[$i]['likes'], '+') > 0
                    || substr_count($array[$i]['likes'], '-') > 0){
                    $i++;
                    continue;
                }

                if ($array[$i]['likes'] < $sr / 2 ) {
                    $t ++;
                    $array[$i]['likes'] = -1;
                }
                $i++;
            }

            $sr = $this->get_average($array);
            $ed = $sr * 2;
            unset($t);

            $t = 0;

            //отсев значений ниже порога, оценка оставшихся в %
            while (isset($array[$t]['likes'])){
                #
                if (    substr_count($array[$t]['likes'], '%') > 0 ||
                    # substr_count($array[$t]['likes'], '+') > 0 ||
                    $array[$t]['likes'] == '-1'
                    || substr_count($array[$t]['likes'], '-') > 0) {
                    $t++;
                    continue;

                }
                if ($array[$t]['likes_tr'] >= (self::LIMIT_BREAK / 100) * $ed)
                {
                    if ($ed < 1)
                        $array[$t]['likes'] = '-';
                    else
                        $array[$t]['likes'] = round(($array[$t]['likes_tr'] * 100) / $ed ) . '%';
                }
                else {
                    $array[$t]['likes'] = -1;
                }

                $t++;
            }
            //удаление ненужных постов
            $dre = count($array);
            for ( $i = 0 ; $i < $dre ; $i++ ){
                if ( $array[$i]['likes'] == -1);
//                    if (self::SAVE_POST_ID)
//                        $array[$i] = $array[$i]['id'];
//                    else
//                         unset($array[$i]);
                elseif ($array[$i]['likes_tr'] < self::LIKES_LIMIT)
                    $array[$i]['likes'] = '-';
            }

            $array = array_values($array);
            return $array;

        }

        //возвращает количество постов паблика(
        //если указать wall_url, вернет количество постов с этого )
        public function get_posts_count($wall_url = '')
        {
            $params = array( 'owner_id' => '-' . $this->page_id,
                'count'    =>  1,
                'filter' => 'owner' );
            $res = VkHelper::api_request( 'wall.get', $params );
            $this->count = $res[0];
            return (int) $res[0];
        }

        private function u_w( $str )
        {
            return iconv("utf-8", "windows-1251", $str);
        }

        private function get_page($page = '')
        {

            if ($page == '')
                $page = $this->page_adr;
            if (self::TESTING) echo '<br>get page url = ' . $page;
            $hnd = curl_init($page);
            //            curl_setopt($hnd , CURLOPT_HEADER, 1);
            curl_setopt($hnd, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($hnd, CURLOPT_FOLLOWLOCATION, true);
            $a = curl_exec($hnd);
            if (curl_errno($hnd))
                throw new Exception('curl error : ' . curl_error($hnd) . ' trying
                    to get ' . $page);
            if (!$a)  throw new Exception("can't download page " . $page);
            file_put_contents(Site::GetRealPath('temp://page.txt'), $a);
            //проверка на доступность
            if( substr_count($a, 'Вы не можете просматривать стену этого сообщества.') > 0 ||
                substr_count($a, $this->u_w('Вы не можете просматривать стену этого сообщества.')) > 0 )
                throw new Exception('access denied to' . $page);

            if (substr_count($a, $this->u_w('ообщество не найден')) == 0 &&
                (substr_count($a, '404 Not Found') == 0) &&
                (substr_count($a, 'общество не найден') == 0))  ;
            else
            {
                throw new Exception('page not found : ' . $page);
            }
            if (substr_count($a, $this->u_w('Страница заблокирована')) == 0 &&
                (substr_count($a, 'Страница заблокирована') == 0))  ;
            else
            {
                throw new Exception('page is blocked: ' . $page);
            }
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
            $text = str_replace( '&#8243;', "",    $text );
            $text = htmlspecialchars_decode($text);
            $text = html_entity_decode($text);
            $text = strip_tags( $text );
            $text = preg_replace('/#[^\s]+/', '',$text);
            return trim($text);
        }

        private function get_photo_desc(&$picsArr, $text)
        {
            $old_text = $text;
            $text = substr($text, 0, 255);
            if(count($picsArr)<=0) return false;

            foreach($picsArr as &$pic){
                //                    echo '<br>torture  '.$pic['id'] . '<br>';
                $id = $pic['id'];
                $h = curl_init();
                curl_setopt($h,CURLOPT_URL,'http://vk.com/photo' . $pic['id']);
                curl_setopt($h,CURLOPT_HEADER,0);
                curl_setopt($h,CURLOPT_RETURNTRANSFER,1);
                curl_setopt($h, CURLOPT_FOLLOWLOCATION, true);
                $desc = curl_exec($h);

                $desc = str_replace('"id":"'.$id.'"','i#d',$desc);
                $desc = str_replace('id','#', $desc);

                preg_match("/\{i\#d[^\#]*?desc\":\"(.*?)\"/", $desc, $matches);

                if (!empty($matches[1])) {
                    $matches[1] = $this->remove_tags($matches[1]);
                    if (isset($matches[1]) && substr_count($matches[1], "href") == 0 &&
                        $matches[1] != $text &&
                        $matches[1] != $old_text &&
                        $matches[1] != 'едактировать описание' &&
                        $matches[1] != $this->u_w('Редактировать описание')) {
                        $pic['desc'] =  $matches[1];
                    }else  $pic['desc'] = '';
                } else {
                    $pic['desc'] = '';
                }
                unset ($desc);
                unset ($matches);
            }
            return true;
        }

        public function get_time($date)
        {
            //начало сегодняшнего дня (для сегодняшних постов)
            $date = trim($date);
            $da = date("d,m,Y");
            $da = explode(',' ,$da);
            $today_zero = mktime(0, 0, 0, $da[1], $da[0], $da[2]);
            $result = false;
            if (is_numeric($date) && strlen($date) == 10) return $date;
            $nowtime = time() + 10800;
            //случай с недавним постом(в пределах 5 минут)
            if (substr_count($date, 'одну') > 0 || substr_count($date, 'две') > 0
                ||  substr_count($date, 'три') > 0
                ||  substr_count($date, 'олько что') > 0
                ||  substr_count($date, 'секун') > 0 ){

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
                case 'окт': $month = 10; break;
                case 'ноя': $month = 11; break;
                case 'дек': $month = 12; break;
                default: return false;
            }
            return $month;
        }

        //$post_ids  = массив idпаблика_idпоста
        //ограничение - 90 постов
        public static function get_post_likes( $post_ids )
        {
            $post_ids = implode( ',', $post_ids );
            $params = array(
                'posts'   =>   $post_ids
            );

            $res = VkHelper::api_request( 'wall.getById', $params, 0 );

            if ( !empty( $res->error )) {
                throw new Exception('wall.getById::'.$res->error->error_msg);
            }

            $result = array();
            foreach( $res as $post ) {
                $result[ $post->to_id . '_' . $post->id ] = array(
                      'likes'   =>     $post->likes->count,
                      'reposts' =>     $post->reposts->count,
                );
            }
            return $result;
        }
    }
?>