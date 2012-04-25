<?php
    Package::Load( 'SPS.Site' );

    /**
     * GetLinkInfoControl Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class GetLinkInfoControl {

        /**
         * Entry Point
         */
        public function Execute() {
            $result = array();

            $url = Request::getString('url');
            if ($url) {
                $metaDetail = MetaDetailFactory::GetOne(array('url' => $url));
                if (!empty($metaDetail)) {
                    $result['url'] = $url;
                    $result['title'] = $metaDetail->pageTitle;
                    $result['description'] = $metaDetail->metaDescription;
                    $result['img'] = $metaDetail->alt;
                }
            }

            echo ObjectHelper::ToJSON($result);
        }
    }
?>