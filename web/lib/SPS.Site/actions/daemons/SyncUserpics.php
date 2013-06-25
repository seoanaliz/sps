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

            $page = 0;
            $pageSize = 100;
            while( $feeds = $this->get_feeds($page++, $pageSize, new SourceFeedFactory(), SourceFeedUtility::Source )) {
                SourceFeedUtility::UpdateFeedInfo( $feeds );
                if( $feeds ) {
                    SourceFeedFactory::UpdateRange( $feeds );
                }
            }
//
            $page = 0;
            while( $feeds = $this->get_feeds($page++, $pageSize, new TargetFeedFactory(), TargetFeedUtility::VK )) {
                SourceFeedUtility::UpdateFeedInfo( $feeds );
                if( $feeds ) {
                    TargetFeedFactory::UpdateRange( $feeds );
                }
            }
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

        public function get_feeds( $page, $pageSize, $factory, $type )
        {
            $mapping    = BaseFactory::GetMapping( get_class( $factory ) );
            $sql = 'SELECT * FROM "' . $mapping['view'] . '" WHERE "type" = @type OFFSET @offset LIMIT @limit;';

            $cmd = new SqlCommand( $sql, ConnectionFactory::Get());
            $cmd->SetInteger( '@offset', $page * $pageSize );
            $cmd->SetInteger( '@limit',  $pageSize );
            $cmd->SetString ( '@type',  $type );
            $ds = $cmd->Execute();

            $structure  = BaseFactory::getObjectTree( $ds->Columns );
            $result = array();
            while ( $ds->Next()) {
                $result[$ds->GetValue('externalId')] = BaseFactory::GetObject($ds, $mapping, $structure);
            }
            return $result;
        }
    }
?>