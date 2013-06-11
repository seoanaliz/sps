<?php
    /**
     * Editor
     *
     * @package SPS
     * @subpackage Articles
     */
    class EditorsUtility {

        const TempPublisherId = 1;

        //делаем новые фиды, назначаем/удаляем админа
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
            //массив подтвержденных пабликов.
            $confirmedTargetFeedIds = array();

            //делаем новый список фидов, где юзер - админ
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

                    SourceFeedUtility::DownloadImage( $publicId, $publicInfo[$publicId]['ava']);
                    TargetFeedFactory::Add( $targetFeed, array( BaseFactory::WithReturningKeys => true));
                    $targetFeeds[ $publicId ] = $targetFeed;
                }

                $newUserFeeds[] = new UserFeed( $userVkId, $targetFeeds[ $publicId ]->targetFeedId, UserFeed::ROLE_OWNER );
                $confirmedTargetFeedIds[] = $targetFeeds[ $publicId ]->targetFeedId;
            }

            //добавляем в список те паблики, где юзер был и остался автором
            foreach( $userFeeds as $targetFeedId => $userFeed ) {
                if( in_array( $userFeed->role, array( UserFeed::ROLE_AUTHOR, UserFeed::ROLE_EDITOR, UserFeed::ROLE_ADMINISTRATOR ))
                    && !in_array( $targetFeedId, $confirmedTargetFeedIds )) {
                    $newUserFeeds[] = new UserFeed( $userVkId, $targetFeedId, $userFeed->role );
                }
            }

            //удаляем все старые зависимости автора
            UserFeedFactory::DeleteForVkId( $userVkId );

            //сохраняем новый список фидов юзера
            if( !empty( $newUserFeeds )) {
                UserFeedFactory::AddRange( $newUserFeeds);
            }


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