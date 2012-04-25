<?php
    Package::Load( 'SPS.Site' );

    /**
     * SaveLinkInfoControl Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class SaveLinkInfoControl {

        /**
         * Entry Point
         */
        public function Execute() {
            $metaDetail = new MetaDetail();
            $metaDetail->url = Request::getString('url');
            $metaDetail->pageTitle = Request::getString('title');
            $metaDetail->metaDescription = Request::getString('description');
            $metaDetail->alt = Request::getString('img');
            $metaDetail->isInheritable = false;
            $metaDetail->statusId = 1;

            if (!empty($metaDetail->url)) {
                $originalObject = MetaDetailFactory::GetOne(array('url' => $metaDetail->url));
                if (!empty($originalObject)) {
                    $metaDetail->metaDetailId = $originalObject->metaDetailId;
                }
            }

            if (!empty($metaDetail->metaDetailId)) {
                $result = MetaDetailFactory::Update($metaDetail);
            } else {
                $result = MetaDetailFactory::Add($metaDetail);
            }

            echo ObjectHelper::ToJSON($result);
        }
    }
?>