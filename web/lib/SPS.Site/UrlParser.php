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

            $result['title'] = !empty($title) ? $title : $url;
            if (!empty($description)) $result['description'] = $description;
            if (!empty($img)) $result['img'] = $img;

            return $result;
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