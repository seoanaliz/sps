<?php
    /**
     * Editor
     *
     * @package SPS
     * @subpackage Articles
     */
    class EditorsUtitlity {

        const TempPublisherId = 1;

        //делаем новые фиды, назначаем/удаляем админов
        public static function SetTargetFeeds( $userVkId, $publicsIds )
        {
            if(  !is_array( $publicsIds) || empty( $publicsIds ) || !$userVkId )
                return false;
            $author = self::CheckIfRegistered($userVkId);
            if( empty($author ))
                return false;
            $publicInfo = StatPublics::get_publics_info( $publicsIds );

            $targetFeeds    = TargetFeedFactory::Get(array('_externalId' => $publicsIds ));
            $targetFeeds    = ArrayHelper::Collapse($targetFeeds, 'externalId', 0);
            $userFeeds      = UserFeedFactory::Get( array('vkId' => $userVkId ));
            $userFeeds      = ArrayHelper::Collapse($userFeeds, 'targetFeedId', 0);

            $newUserFeeds = array();
            //массив подтвержденных пабликов. удалим связь юзера с остальными
            $targetFeedIds = array();

            foreach( $publicsIds as $publicId ) {
                if( !isset( $targetFeeds[$publicId]) && isset( $publicInfo[ $publicId ])) {

                    $targetFeed = new TargetFeed();
                    $targetFeed->externalId  =  $publicId;
                    $targetFeed->title       =  $publicInfo[$publicId]['name'];
                    $targetFeed->publisherId =  self::TempPublisherId;
                    $targetFeed->statusId    =  StatusUtility::Enabled;
                    $targetFeed->type        =  TargetFeedUtility::VK;
                    $targetFeed->period      =  60;
                    $targetFeed->startTime   =  '09:00:00';

                    TargetFeedFactory::Add( $targetFeed, array( BaseFactory::WithReturningKeys => true));
                    $targetFeeds[ $publicId ] = $targetFeed;
                }

                if (!isset ($userFeeds[$targetFeeds[ $publicId ]->targetFeedId])) {
                    $userFeed = new UserFeed();
                    $userFeed->targetFeedId = $targetFeeds[ $publicId ]->targetFeedId;
                    $userFeed->role         = UserFeed::ROLE_OWNER;
                    $userFeed->vkId         = $userVkId;
                    $newUserFeeds[]         = $userFeed;
                }

                $targetFeedIds[] = $targetFeeds[ $publicId ]->targetFeedId;
            }
            if( !empty( $newUserFeeds )) {
                UserFeedFactory::AddRange( $newUserFeeds);
            }

            $allUserTargetFeedIds = array_keys( $userFeeds );

            $targetFeedIds4delete = array_diff($allUserTargetFeedIds, $targetFeedIds);
            self::DeleteUserFeed($userVkId, $targetFeedIds4delete);
        }

        public static function CheckIfRegistered( $userVkId )
        {
            $author = AuthorFactory::GetOne( array('vkId' => $userVkId ));
            if( !$author ) {
                $author = new Author();
                $profiles = VkAPI::GetInstance()->getProfiles(array('uids' => $userVkId, 'fields' => 'photo'));
                $profile = current($profiles);
                $author->firstName = $profile['first_name'];
                $author->lastName = $profile['last_name'];
                $author->avatar = $profile['photo'];
                $author->vkId   = $userVkId;
                $author->statusId = StatusUtility::Enabled;
                AuthorFactory::Add($author, array(BaseFactory::WithReturningKeys => true));
            }
            return $author;

        }

        public static function DeleteUserFeed( $userVkId, $targetFeedIds) {
            if( !is_array($targetFeedIds) || empty( $targetFeedIds ))
                return false;
            $targetFeedIdsString = implode( ',', $targetFeedIds );
            $sql = 'DELETE FROM "userFeed" WHERE "vkId" = @vkId and "targetFeedId" IN (' . $targetFeedIdsString . ')';
            $cmd = new SqlCommand($sql, ConnectionFactory::Get());
            $cmd->SetInteger('@vkId', $userVkId);
            $cmd->Execute();
        }

    }
?>