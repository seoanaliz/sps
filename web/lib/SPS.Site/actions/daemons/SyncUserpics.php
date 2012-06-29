<?php
    Package::Load( 'SPS.Site' );

    /**
     * SyncUserpics Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class SyncUserpics {

        /**
         * Entry Point
         */
        public function Execute() {
            set_time_limit(0);
            Logger::LogLevel(ELOG_DEBUG);

            $sources = SourceFeedFactory::Get(array('type' => SourceFeedUtility::Source), array(BaseFactory::WithColumns => '"externalId"'));
            $targets = TargetFeedFactory::Get(array('type' => TargetFeedUtility::VK), array(BaseFactory::WithColumns => '"externalId"'));

            $externalIds = array_merge(
                ArrayHelper::GetObjectsFieldValues( $sources, array( 'externalId' ) )
                , ArrayHelper::GetObjectsFieldValues( $targets, array( 'externalId' ) )
            );

            foreach ($externalIds as $externalId) {
                if (in_array($externalId, SourceFeedUtility::$Tops)) continue;
                SourceFeedUtility::SaveRemoteImage($externalId);
            }
        }
    }

?>