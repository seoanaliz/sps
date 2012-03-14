<?php
    Package::Load( 'SPS.Site' );

    /**
     * ImageUploadControl Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class ImageUploadControl {

        /**
         * Entry Point
         */
        public function Execute() {
            $result = MediaUtility::SaveTempFile( !empty($_FILES['Filedata']) ? $_FILES['Filedata'] : null, 'Article', 'photos' );
            if( !empty( $result['filename'] ) ) {
                $result['path']  = MediaUtility::GetFilePath( 'Article', 'photos', 'small', $result['filename'], MediaServerManager::$TempLocation );
            } else if( !empty( $result['error'] ) ) {
                $result['error'] = LocaleLoader::Translate('errors.files.' . $result['error']);
            }

            echo ObjectHelper::ToJSON( $result );
        }
    }
?>