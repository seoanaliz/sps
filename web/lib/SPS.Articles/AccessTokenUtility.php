<?php

    class AccessTokenUtility {
        public static function getTokenForTargetFeedId( $targetFeedId, $random = false ) {
            $access_token = false;
            $search = array(
                'targetFeedId'  =>  $targetFeedId,
                '_role'         =>  array(UserFeed::ROLE_ADMINISTRATOR, UserFeed::ROLE_OWNER, UserFeed::ROLE_EDITOR)
            );
            $userFeeds = UserFeedFactory::Get($search);
            if( empty($userFeeds)) {
                return $access_token;
            }

            $index = $random ? array_rand($userFeeds) : 0;

            return AccessTokenFactory::GetOne( array( 'vkId' => $userFeeds[$index]->vkId ));

        }

    }
