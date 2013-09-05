<?php

    class AccessTokenUtility {

        /** @var $targetFeed TargetFeed*/
        public static function getTokenForTargetFeed( $targetFeedId, $random = false, $version = false ) {
            $access_token = false;

            $search = array(
                'targetFeedId'  =>  $targetFeedId,
                '_role'         =>  array(UserFeed::ROLE_ADMINISTRATOR, UserFeed::ROLE_OWNER, UserFeed::ROLE_EDITOR),
            );

            $userFeeds = UserFeedFactory::Get($search);
            if( empty($userFeeds)) {
                return $access_token;
            }
            $index = $random ? array_rand($userFeeds) : 0;

            $searchAT = array( 'vkId' => $userFeeds[$index]->vkId );
            if( $version ) {
                $searchAT['version'] = $version;
            }
            $access_token = AccessTokenFactory::GetOne($searchAT);
            if (!empty( $access_token ))
                return $access_token->accessToken;
            return $access_token;
        }

        public static function getAllTokens( $targetFeedId, $version = 0, $roles = array())
        {
            $result = array();
            if( empty( $roles )) {
                $roles = array( UserFeed::ROLE_EDITOR );
            }

            $search = array(
                'targetFeedId'  =>  $targetFeedId,
                '_role'         =>  $roles,
            );

            $userFeeds = UserFeedFactory::Get($search);
            if( empty($userFeeds)) {
                return $result;
            }
            $vkIds = array_values( ArrayHelper::GetObjectsFieldValues($userFeeds, array('vkId')));

            $searchAT = array(
                'vkIdIn' => array_values($vkIds),
            );
            if( $version ) {
                $searchAT['version'] = $version;
            }

            $access_tokens = AccessTokenFactory::Get($searchAT);
            foreach( $access_tokens as $access_token ) {
                if( !empty( $access_token->accessToken)) {
                    $result[$access_token->vkId] = $access_token->accessToken;
                }
            }
            return $result;
        }

        /** @var $targetFeed TargetFeed*/
        public static function getPublisherTokenForTargetFeed($targetFeedId, $random = false )
        {
            $publishers = TargetFeedPublisherFactory::Get(array('targetFeedId' => $targetFeedId));
            if (empty($publishers)) {
                return false;
            }
            $index = $random ? array_rand( $publishers) : 0;
            $publisher = PublisherFactory::GetById( $publishers[$index]->publisherId);

            if (!empty( $publisher->vk_token ))
                return $publisher->vk_token;
            return false;
        }

        public static function getTokens( $authorVkId, $targetFeed ) {
            $result = array();
            $Author = AuthorFactory::GetOne(array('vkId' => $authorVkId));
            if (empty($Author))
                return $result;
            if ( $Author->postFromBot == 1  && $targetFeed->isOur == 1 ) {
                $userFeeds  =  UserFeedFactory::Get(array( 'targetFeedId' => $targetFeed->targetFeedId ));
                $userFeeds  =  ArrayHelper::Collapse( $userFeeds, 'vkId', $convertToArray = false);
                $vkIds = array_keys($userFeeds);
                if (empty($vkIds))
                    return $result;
                $botAuthors =  AuthorFactory::Get(
                    array(
                        'vkIdIn' => $vkIds,
                        'isBot' =>  true
                    ));
                if (empty($botAuthors))
                    return $result;
                $botAuthors =  ArrayHelper::Collapse( $botAuthors, 'vkId', $convertToArray = false);
                $tokens     =  AccessTokenFactory::Get(array(
                    'vkIdIn' => array_keys( $botAuthors ),
                    'version' => AuthVkontakte::$Version
                ));

                shuffle( $tokens );
                $result = $tokens;
            } else {
                $result = AccessTokenFactory::GetOne(array(
                    'vkId' => $authorVkId,
                    'version' => AuthVkontakte::$Version
                ));
                $result = array($result);
            }
            return $result;
        }

    }
