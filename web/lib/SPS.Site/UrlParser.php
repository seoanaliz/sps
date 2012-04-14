<?php
    /**
     * UrlParser
     * @package    SPS
     * @subpackage VK
     * @author     Shuler
     */
    class UrlParser {
        public static function Parse($url) {
            $result = array();

            $html = self::GetUrlContent($url);
            if (empty($html)) {
                return $result;
            }

            $document = phpQuery::newDocument($html);

            $title = $document->find('title')->html();
            $description = $document->find("meta[name='description']")->attr('content');
            $img = $document->find("link[rel='image_src']")->attr('href');

            $title = trim($title);
            $description = trim($description);

            $result['title'] = !empty($title) ? $title : $url;
            if (!empty($description)) $result['description'] = $description;
            if (!empty($img)) $result['img'] = $img;

            return $result;
        }

        public static function IsContentWithLink($content) {
            if (preg_match('/\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|$!:,.;]*[A-Z0-9+&@#\/%=~_|$]/uim', $content)) {
                return true;
            } else {
                return false;
            }
        }

        public static function IsContentWithHash($content) {
            if (preg_match('/(^|\s)#(\w+)/uim', $content)) {
                return true;
            } else {
                return false;
            }
        }

        private static function getUrlContent($url) {
            $hnd = curl_init($url);
            curl_setopt($hnd, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($hnd, CURLOPT_FOLLOWLOCATION, true);
            $result = curl_exec($hnd);
            if (curl_errno($hnd)) return false;
            return $result;
        }
    }
?>