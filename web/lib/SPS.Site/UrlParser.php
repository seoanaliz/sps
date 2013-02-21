<?php
    /**
     * UrlParser
     * @package    SPS
     * @subpackage VK
     * @author     Shuler
     */
    class UrlParser {
        public static function Parse($url) {
            $cacheKey = 'url_' . md5($url);
            $cacheResult = MemcacheHelper::Get( $cacheKey );
            if ($cacheResult !== false) {
                return $cacheResult;
            }

            $result = array();
            $html = self::GetUrlContent($url);
            $html = mb_check_encoding($html, 'UTF-8') ? $html : utf8_encode($html);
            if (empty($html)) {
                return $result;
            }

            $urlData = parse_url($url);
            $baseUrl = $urlData['scheme'] . '://' . $urlData['host'];

            $document = phpQuery::newDocument($html);

            $title = $document->find( "meta[property='og:title']" )->attr('content');
            $title = empty($title) ? $document->find('title')->html() : $title;

            $description = $document->find( "meta[property='og:description']" )->attr('content');
            $description = empty($description) ? $document->find("meta[name='description']")->attr('content') : $description;

            $img = $document->find( "meta[property='og:image']" )->attr('content');
            $img = empty($img) ? $document->find("link[rel='image_src']")->attr('href') : $img;
            $imgOriginal = $document->find("#original_image_src")->attr('value');

            //fix img
            if (strpos($img, 'http') === false) {
                $img = $baseUrl . $img;
            }

            $title = trim($title);
            $description = trim($description);

            $result['title'] = !empty($title) ? $title : $url;
            if (!empty($description)) $result['description'] = $description;

            if (!empty($img)) {
                $result['img'] = $img;
                $result['imgOriginal'] = !empty($imgOriginal) ? $imgOriginal : $img;
            }

            //кешируем данные на 10 минут
            MemcacheHelper::Set( $cacheKey, $result, 0, 600 );

            return $result;
        }

        public static function IsContentWithLink($content) {
            if (preg_match('%([a-zA-Z0-9-.]+\.(?:ru|com|net|me|edu|org|info|biz|uk|ua))([a-zA-Z0-9-_?/#,&;]+)?%uim', $content)) {
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

        public static function getUrlContent($url) {
            $hnd = curl_init($url);
            curl_setopt($hnd, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($hnd, CURLOPT_FOLLOWLOCATION, true);
            $result = curl_exec($hnd);
            if (curl_errno($hnd)) return false;
            return $result;
        }
    }
?>