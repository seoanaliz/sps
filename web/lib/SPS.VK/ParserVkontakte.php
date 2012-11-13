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

        public function get_posts( $page_number )
        {
            $offset = $page_number * self::PAGE_SIZE;

            if (!isset($this->count))
                $this->get_posts_count();

            if ($offset > $this->count) {
                throw new Exception("wall's end");
            }

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
                $text       =   $this->remove_tags( $post->text);
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
                                              'poll'    => $poll,    'text_links'   =>  $text_links
                );
            }
            return $result_posts_array;
        }

        private function get_average( array &$a )
        {
            $q = count( $a );
            $sum_likes = 0;
            $sum_reposts = 0;
            foreach( $a as $post ){
                if (substr_count($post['likes'], '%') > 0 ||
                    substr_count($post['likes'], '+') > 0 ||
                    $post['likes'] == -1){
                    $q--;
                }
                else {
                    $sum_likes   += $post['likes'];
                    $sum_reposts += $post['retweet'] / $post['likes_tr'];
                }
            }
            //            echo 'cymma = ' . $sum . 'and q = ' . $q . '<br>';
            return ( array(
                'avg_likes' => $sum_likes / $q,
                'avg_retweet' => $sum_reposts / $q
            ));
        }

        private function kill_attritions( $array )
        {
            $res = array();
            $sr =  $this->get_average( $array );
            $lte = $sr['avg_retweet'];
            $i = 0;
            $t = 0;
            //отсев крупных
            while(isset($array[$i]['likes'])){
                if ( $array[$i]['likes'] > ( $sr['avg_likes'] * 2 )){
                    if ( $sr['avg_likes'] > 1){
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

                if ($array[$i]['likes'] < $sr['avg_likes'] / 2 ) {
                    $t ++;
                    $array[$i]['likes'] = -1;
                }
                $i++;
            }

            $sr = $this->get_average($array);
            $ed = $sr['avg_likes'] * 2;
            unset($t);

            $t = 0;

            //отсев значений ниже порога, оценка оставшихся в %
            while (isset($array[$t]['likes'])){
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

            foreach( $array as &$post ) {
                if( $post['likes'] != -1 && $post['likes'] != '-' && $sr['avg_retweet'] ) {
                    $als = ( $post['retweet'] / rtrim($post['likes_tr'], '%')) /  $sr['avg_retweet'] ;
                    $als = $als > 2 ? 2 : $als;
                    $als = $als < 0.5 ? 0.5 : $als;
                    $post['likes'] = round( rtrim($post['likes'], '%') *  $als ) . '%';
                }
            }
            //удаление ненужных постов
            $dre = count($array);
            for ( $i = 0 ; $i < $dre ; $i++ ){

                if ( $array[$i]['likes'] == -1 )
                    ;
                elseif ($array[$i]['likes_tr'] < self::LIKES_LIMIT) {
                    $array[$i]['likes'] = '-';
                }
            }

            $array = array_values( $array );
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

        public function get_album_as_posts( $public_id, $album_id, $limit = false, $offset = false)
        {
            $params = array(
                'gid'       =>  $public_id,
                'aid'       =>  $album_id,
            );

            if (is_numeric($limit))    $params['limit'] = $limit;
            if (is_numeric($offset))    $params['offset'] = $offset;

            $res = VkHelper::api_request( 'photos.get', $params );
            $query_line = array();

            foreach( $res as $photo )
            {
                $query_line[]= $photo->owner_id . '_' . $photo->pid;
            }
            $query_line = implode( ',', $query_line );
            sleep( 0.3 );

            $params = array(
                'photos'    =>  $query_line,
                'extended'  =>  1,
            );
            $res = VkHelper::api_request( 'photos.getById', $params );
            $posts = VkAlbums::post_conv( $res );
            $posts = $this->kill_attritions( $posts );
            return $posts;
        }

        /**
         * Возвращает количество фото в альбоме
         * @param $public_id
         * @param $album_id
         * @return int
         * @throws Exception
         */
        public function get_photo_count_in_album( $public_id, $album_id )
        {
            $params = array(
                'gid' => $public_id,
                'aids' => $album_id,
            );

            $res = VkHelper::api_request('photos.getAlbums', $params);

            if (!empty($res->error)) {
                throw new Exception('wall.getById::' . $res->error->error_msg);
            } else {
               if (count($res)) {
                   $res = array_pop($res);
                   return $res->size;
               } else {
                   throw new Exception('Cann`t get album info '.$public_id.'_'.$album_id);
               }
            }
        }
}
