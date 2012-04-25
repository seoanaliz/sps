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
            $data = Request::getArray('data');

            $metaDetail = new MetaDetail();
            $metaDetail->url = $data['link'];
            $metaDetail->pageTitle = !empty($data['header']) ? $data['header'] : '';
            $metaDetail->metaDescription = !empty($data['description']) ? $data['description'] : '';
            $metaDetail->alt = '';
            $metaDetail->isInheritable = false;
            $metaDetail->statusId = 1;

            if (!empty($data['coords'])) {
                $dimensions = $this->getDimensions($data['coords']);

                $urlData = UrlParser::Parse($metaDetail->url);

                if (!empty($urlData['img'])) {
                    $tmpName = Site::GetRealPath('temp://') . md5($urlData['img']) . '.jpg';
                    $fileContent = (Site::IsDevel()) ? file_get_contents($urlData['img']) : UrlParser::getUrlContent($urlData['img']);
                    file_put_contents($tmpName, $fileContent);
                    $file = array(
                        'tmp_name'  => $tmpName,
                        'name'      => $tmpName,
                    );

                    ImageHelper::Crop( $tmpName, $tmpName, $dimensions['x'], $dimensions['y'], $dimensions['w'], $dimensions['h'], 100 );

                    $fileUploadResult = MediaUtility::SaveTempFile( $file, 'Link', 'photos' );

                    if( !empty( $fileUploadResult['filename'] ) ) {
                        MediaUtility::MoveObjectFilesFromTemp( 'Link', 'photos', array($fileUploadResult['filename']) );
                        unlink($tmpName);

                        $metaDetail->alt = $fileUploadResult['filename'];
                    }
                }
            }

            //original id
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

        private function getDimensions($coords) {
            $result = array();

            $fields = array( 'x', 'y', 'w', 'h' );

            foreach( $fields as $field ) {
                $value = $coords[$field];
                if( ( $value < 0 ) || empty( $value ) ) {
                    $value = 0;
                }

                $result[$field] = $value;
            }

            return $result;
        }
    }
?>