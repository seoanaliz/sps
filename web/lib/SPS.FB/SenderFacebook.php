<?php
    class SenderFacebook {
        private $data_array;
        const METH = 'https://graph.facebook.com/';

        public  function __construct( $data_array )
        {
            $data_array = json_decode($data_array);
            $data_array->targeting = json_encode($data_array->targeting);
            $data_array->message = $this->remove_tags($data_array->message);
            $this->album = $data_array->album;
            $this->data_array = $data_array;
        }

        private function qurl_request($url, $arr_of_fields, $headers = '', $uagent = '')
        {
            print_r($arr_of_fields);
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

            if (is_array($headers)) { // РµСЃР»Рё Р·Р°РґР°РЅС‹ РєР°РєРёРµ-С‚Рѕ Р·Р°РіРѕР»РѕРІРєРё РґР»СЏ Р±СЂР°СѓР·РµСЂР°
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            }

            if (!empty($uagent)) { // РµСЃР»Рё Р·Р°РґР°РЅ UserAgent
                curl_setopt($ch, CURLOPT_USERAGENT, $uagent);
            } else{
                curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; rv:2.0.1) Gecko/20100101 Firefox/4.0.1)');
            }

            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);

            if (is_array($arr_of_fields)) {
                curl_setopt( $ch, CURLOPT_POSTFIELDS,  $arr_of_fields);

            } else return false;

            $result = curl_exec($ch);
            if (curl_errno($ch)){
                throw new exception("error in curl: ". curl_error($ch) );
            }

            curl_close($ch);
            return $result;
        }

        private function remove_tags($text)
        {
            $text = str_replace( '<br>', "\r\n", $text );
            $text = htmlspecialchars_decode($text);
            $text = html_entity_decode($text);
            $text = strip_tags( $text );
            return $text;
        }

        public  function send_photo()
        {
            $url = self::METH . $this->album . '/photos/';

            unset($this->data_array->page);
            unset($this->data_array->album);
            $this->data_array = (array) $this->data_array;
            $result = $this->qurl_request($url, $this->data_array);

            $result = json_decode($result);

            if (isset($result->error)) {
                throw new exception($result->error->message);
            }

            return $result->id;
        }


    }
?>