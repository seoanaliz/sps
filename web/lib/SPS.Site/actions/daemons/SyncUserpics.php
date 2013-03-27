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

            $this->get_users_userpics();
            $this->get_feeds_userpics();
        }

        public function get_feeds_userpics()
        {
            $sources = SourceFeedFactory::Get(array('type' => SourceFeedUtility::Source), array(BaseFactory::WithColumns => '"externalId"'));
            $targets = TargetFeedFactory::Get(array('type' => TargetFeedUtility::VK), array(BaseFactory::WithColumns => '"externalId"'));

            $externalIds = array_merge(
                ArrayHelper::GetObjectsFieldValues( $sources, array( 'externalId' ))
                , ArrayHelper::GetObjectsFieldValues( $targets, array( 'externalId' ))
            );

            $externalIdsClean = array_diff($externalIds, SourceFeedUtility::$Tops );
            SourceFeedUtility::SaveRemoteImage($externalIdsClean);
        }

        public function get_users_userpics()
        {
            $authors = AuthorFactory::Get();
            $editors = EditorFactory::Get();
            $externalIds = array_unique( array_merge(
                ArrayHelper::GetObjectsFieldValues( $editors, array( 'vkId' ))
                , ArrayHelper::GetObjectsFieldValues( $authors, array( 'vkId' ))
            ));
            $externalIdsChunks = array_chunk( $externalIds, 500 );
            $usersInfo = array();
            foreach( $externalIdsChunks as $chunk ) {
                $usersInfo = ArrayHelper::MergeDistinct( $usersInfo, StatUsers::get_vk_user_info( $chunk ));
                sleep(0.3);
            }

            array_walk( $authors, function( $author ) use ( $usersInfo) {
                if( isset( $usersInfo[$author->vkId])) {
                    $author->avatar = $usersInfo[$author->vkId]['ava'];
                }
            });
            array_walk( $editors, function( $editor ) use( $usersInfo ) {
                if( isset( $usersInfo[$editor->vkId])) {
                    $editor->avatar = $usersInfo[$editor->vkId]['ava'];
                }

            });

            AuthorFactory::UpdateRange( $authors );
            EditorFactory::UpdateRange( $editors );
        }
    }
?>