<?php

    class AccessTokenUtility {
        /** @var $targetFeed TargetFeed*/
        public static function getTokenForTargetFeed( $targetFeed, $random = false ) {
            $access_token = false;
            $search = array(
                'targetFeedId'  =>  $targetFeed->targetFeedId,
                '_role'         =>  array(UserFeed::ROLE_ADMINISTRATOR, UserFeed::ROLE_OWNER, UserFeed::ROLE_EDITOR)
            );
            $userFeeds = UserFeedFactory::Get($search);
            if( empty($userFeeds)) {
                return $access_token;
            }

            $index = $random ? array_rand($userFeeds) : 0;

            $access_token =  AccessTokenFactory::GetOne( array( 'vkId' => $userFeeds[$index]->vkId ));
            if( !empty( $access_token ))
                return $access_token->accessToken;
            return $access_token;
        }

        /** @var $targetFeed TargetFeed*/
        public static function getPublisherTokenForTargetFeed($targetFeed, $random = false )
        {
            $publishers = TargetFeedPublisherFactory::Get($targetFeed->targetFeedId);
            if( empty($publishers))
                return false;

            $index = $random ? array_rand( $publishers) : 0;
            $publisher = PublisherFactory::GetById( $publishers[0]->publisherId);

            if( !empty( $publisher->vk_token ))
                return $publisher->vk_token;
            return false;
        }

    }
