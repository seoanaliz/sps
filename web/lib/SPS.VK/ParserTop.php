<?php

class tf_parcer
{
    const TOKEN = '7770233f7f7460a97f7460a96c7f5cc82477f747f7140af4eaf4fcadc9b46dd';
    const API_URL = 'api.topface.ru';
    const TESTING = false;
    public $counter = 0;
    private $ssid = ''; //id текущей сессии

//    public function __construct()
//    {
//        $this->auth();
//    }

    //отправляет данные в json
    private $db;
    public function qurl_request_js($url, $arr_of_fields, $headers = '', $uagent = '')
    {
        if (empty($url)) {
            return false;
        }
        if(self::TESTING) print_r($arr_of_fields);

        $ch = curl_init($url);


        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array('Content-type: application/json') ,
            CURLOPT_POSTFIELDS => $arr_of_fields
        );

        // Setting curl options
        curl_setopt_array( $ch, $options );


        $result = curl_exec($ch);
        if (curl_errno($ch)){
            echo "<br>error in curl: ". curl_error($ch) ."<br>";
            return 'error in curl: '. curl_error($ch);
        }

        curl_close($ch);
        return $result;
    }

    private function get_cities()
    {
        $cities = array(
            'Moscow'            =>  1,
            'Ekaterinburg'      =>  49,
            'Kazan'             =>  60,
            'Kiev'              =>  314,
            'Minsk'             =>  282,
            'Novosibirsk'       =>  99,
            'Samara'            =>  123,
            'Saint Petersburg'  =>  2,
            'Ufa'               =>  151,
            'Harkov'            =>  280
        );
        return $cities;
    }

    private function auth()
    {
        $request_params = array(
            'locale'    =>  'ru',
            'platform'  =>  'vk',
            'sandbox'   =>  1,
            'sid'       =>  134497174,
            'token'     =>  self::TOKEN,
            'clienttype'=>  'sdasd'
        );
        $response = $this->tf_api_wrap('auth', $request_params);

        $this->ssid = $response->ssid;

        return true;

    }

    private function tf_api_wrap( $service,$request_params )
    {
        if (self::TESTING) {
            echo 'враппер, ' . $service . '<br>';
        }
        $request = array(   'service'   =>  $service,
            'data'      =>  $request_params
        );
        if ($this->ssid)
            $request['ssid'] = $this->ssid;


        $request  = json_encode($request);
        if (self::TESTING) {
            echo '<br>данные запроса<br>';
            print_r($request);
            echo '<br>';
        }

        $response = $this->qurl_request_js(self::API_URL, $request);
        $response = json_decode($response);
        if (self::TESTING) {
            echo '<br>ответ<br>';
            print_r($response);
            echo '<br>';
        }

        if (isset($response->error)){
            throw new exception('Error in ' . __CLASS__ . '::' . __FUNCTION__ .
                ", problems with top request : " . $response->error->message);
        }

        return $response->result;
    }

    //возвращает массив в json
    //имеющиеся поля совпадают с парсером контакта: id, photo, link, likes, text
    //остальных нет
    //в поле id - идентификатор юзера в topface
    //$sex: 0 - ж, 1 - м
    public function get_top( $sex = 0 )
    {

        $this->auth();
        $cities = $this->get_cities();

        $res = array();
        $uids = array();
        //перебор топов городов
        foreach($cities as $city){
            $request_params  = array(
                'sex'  =>  $sex,
                'city' => $city
            );

            $response = $this->tf_api_wrap('top', $request_params);
            foreach($response->top as &$entry){
                $uids[] = $entry->uid;
                $res[] = array(
                    'id'      =>  $entry->uid,
                    'link'    =>  'http://topface.com/vklike/' . $entry->uid. '/',
                    'likes'   =>  $entry->liked,
                    'photo'   =>  array(
                        '0' => array(
                            'url' => $entry->photo
                        )
                    )
                );
            }

            sleep(0.1);
        }

        $request_params = array(
            'uids'      =>  $uids,
            'fields'    =>  array('first_name', 'age')
        );

        $response = $this->tf_api_wrap('profiles', $request_params);
        $i = 0;
        foreach($response->profiles as $entry) {
            while (1) {
                if ($entry->age > 35 || $entry->age < 18) break;
                if ($res[$i]['id'] == $entry->uid) {
                    $res[$i]['text'] = $entry->first_name . ', ' . $entry->age;
                    $i++;
                    break;
                } else {
                    unset($res[$i]);
                    $i++;
                    if ($i > 10000) {
                        throw new exception('Error in ' . __CLASS__ . '::' . __FUNCTION__ .
                            'something really goes wrong with arrays of top and extra data');
                    }
                }
            }
        }

        if (self::TESTING) {
            echo 'count uids = ' . count($uids) . '<br>';
            echo 'res  = '. count($res) . '<br>';
            echo '<br>res 2 = ' . $i . '<br>';
            foreach($res as $entry){
                print_r($entry);
                echo '<br>';
            }
        }
        return $res;
    }

    //возвращает массив в json эро фоток
    //имеющиеся поля совпадают с парсером контакта: id, photo, link, likes, text
    //остальных нет
    //в поле id - идентификатор юзера в topface
    //$sex: 0 - ж, 1 - м
    public function get_fragaria($sex = 0)
    {
        $this->auth();
        $cities = $this->get_cities();
        $res = array();
        $uids = array();
        //берем по 50 эро контентников с каждого города
        foreach($cities as $city){
            $request_params  = array(
                'sex'  =>  $sex,
                'city' => $city,
                'agebegin' => 18,
                'ageend' => 30,
//                                            'ero'=> true
            );

            $this->tf_api_wrap('filter',$request_params);
            $request_params  = array('limit'  =>    50, 'ero' => true);
            $response = $this->tf_api_wrap('search',$request_params);
//                 print_r($response);

            foreach($response->users as $user) {

                $request_params  = array('uid'  => $user->uid);
                $response2 = $this->tf_api_wrap('album',$request_params);
                //разбираем альбомы пользователей
                $photo_counter = 0;
                foreach($response2->album as $photo) {
                    if ($photo->ero == 1) {
                        $photo_counter++;
                        $text = $user->first_name . ' (' . $user->age . ')';
                        $l = $photo->likes;
                        $d = $photo->dislikes;
                        $l = ($l + $d) == 0 ? $l+1 : $l;
                        $d = $d == 0 ? $d+1 : $d;
//                              #3.8416
                        $likes = (($l + $l*0.25) / ($l + $d) - 1.96 * SQRT(($l * $d) / ($l + $d) + 0.9604) /($l + $d)) / (1 + 3 / ($l + $d));
                        $likes = round($likes * 100, 0);

                        if ( $l + $d < 20 AND $l / $d < 1) continue;
                        $res[] = array(
                            'id'      =>  $user->uid . '_' . $photo_counter,
                            'link'    =>  'http://topface.com/vklike/' . $user->uid. '/',
                            'likes'   =>  $likes,
//                                    'dislikes'   =>  $photo->dislikes,
                            'photo'   =>  array(
                                '0' => array(
                                    'url' => $photo->big
                                )
                            ),

                            'text'    =>  $text
                        );

                        //NSFW!!!
                        if (self::TESTING) {
//                            if ($likes > 30) {
                            echo "<br>";
                            $name = $user->first_name . ' (' . $user->age . ')';
                            echo $user->uid . '_' . $photo_counter . ' ' . $name;
                            echo "<br>";
                            echo '<img src="' . $photo->big . '"><br>' . 'likes: ' . $likes .
                                '%  ' . $photo->likes . '/' . $photo->dislikes . '<br>';
                            echo "<br>";
                            echo "<br>";
                        }
                    }
                }
            }
            return json_encode($res);
        }
    }

    public  function draw($count = 100)
    {



        $sql = 'SELECT
                        id,path,name,
                        ((likes + 1.9208) / (likes + dislikes) -
                            1.96 * SQRT((likes * dislikes) / (likes + dislikes) + 0.9604) /
                                    (likes + dislikes)) / (1 + 3.8416 / (likes + dislikes))
                            AS mark
                        FROM erotica WHERE likes + dislikes > 0
                        ORDER BY mark DESC;';#
        $result = $this->db->query($sql);
        if ($this->db->errno) throw new Exception(__CLASS__ . '::' . __FUNCTION__ .
            ' Error : ' . $this->db->error);
        while ($row = $result->fetch_array(MYSQLI_ASSOC)){
//                    if ((round($row['mark'],2)*100) > 50) continue;
            echo "<br>";
            echo $row['id'] . ' ' . $row['name'];
            echo "<br>";
            echo '<img src="' . $row['path'] . '"><br>' . 'mark: ' . (round($row['mark'],2)*100) . '%<br>';
            echo "<br>";
            echo "<br>";
        }
    }
}

//
//try {
//
// $a = new tf_parcer();
//
//        print_r($a->get_fragaria());
//        print_r($a->get_top());
//
//} catch(exception $e) {
//    echo '<br>' . $e->getMessage();
//}
//

?>