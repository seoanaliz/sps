<?php
    /**
     * ArticleUtility
     * @package    SPS
     * @subpackage Articles
     * @author     Shuler
     */
    class ArticleUtility {

        /** in minutes*/
        const PostsPerDayInFeed = 75;
        const TimeBeetwenPosts  = 5;

        public static function IsTopArticleWithSmallPhoto(SourceFeed $sourceFeed, ArticleRecord $articleRecord) {
            if (!empty($articleRecord->photos) && count($articleRecord->photos) == 1 && SourceFeedUtility::IsTopFeed($sourceFeed)) {
                $photoItem = current($articleRecord->photos);
                $path = MediaUtility::GetArticlePhoto($photoItem);
                $dimensions = ImageHelper::GetImageSizes($path);

                if ($dimensions['width'] < 250 && $dimensions['height'] < 250) {
                    return true;
                }
            }

            return false;
        }

        public static function ChangeQueueDates($queueId, $timestamp) {
            $object = ArticleQueueFactory::GetById($queueId);

            $oldDate = new DateTimeWrapper($object->startDate->DefaultFormat());
            self::BuildDates($object, $timestamp);
            $newDate = new DateTimeWrapper($object->startDate->DefaultFormat());

            $object->isDeleted = false;
            $object->deleteAt = null;
            ArticleQueueFactory::UpdateByMask($object, array('startDate', 'endDate', 'isDeleted', 'deleteAt'), array('articleQueueId' => $queueId, 'statusId' => 1));

            $targetFeed = TargetFeedFactory::GetById($object->targetFeedId);

            AuditUtility::CreateEvent(
                'gridLineTime'
                , 'articleQueue'
                , $queueId
                , "Changed by editor VkId " . AuthUtility::GetCurrentUser('Editor')->vkId
                    . ", queueId is " . $queueId
                    . ", old time is " . $oldDate->modify('+1 minute')->defaultFormat()
                    . ", new time is " . $newDate->modify('+1 minute')->defaultFormat()
                    . ", public is " . $targetFeed->title
            );
        }

        public static function BuildDates($object, $timestamp) {
            $object->startDate = new DateTimeWrapper(date('r', $timestamp));
            $object->endDate = new DateTimeWrapper(date('r', $timestamp));

            $object->startDate->modify('-30 seconds');
            $object->endDate->modify('+9 minutes');
        }

        public static function IsTooCloseToPrevious( $targetFeedId, $newPostTimestamp )
        {
            $intervalTime = new DateTimeWrapper(date('r', $newPostTimestamp));
            $from = new DateTimeWrapper(date('r', $newPostTimestamp));
            $from->modify( '- ' . self::TimeBeetwenPosts . ' minutes');
            $search = array(
                'targetFeedId'  =>  $targetFeedId,
                'startDateFrom' =>  $from,
                'startDateTo'   =>  $intervalTime->modify('+ 1 minute')
            );
            $check = ArticleQueueFactory::Get( $search );
            return !empty( $check );
        }

        public static function IsArticlesLimitReached($targetFeedId, $newPostTimestamp)
        {
            $midnightNextDay = new DateTimeWrapper(date('r', $newPostTimestamp));
            $midnightNextDay->modify('+ 1 day')->modify('midnight');
            $midnight = new DateTimeWrapper(date('r', $newPostTimestamp));
            $midnight->modify('midnight');
            //ограничение по количеству постов в ленте
            $search = array(
                'targetFeedId'  =>  $targetFeedId,
                'startDateFrom' =>  $midnight,
                'startDateTo'   =>  $midnightNextDay,
                BaseFactoryPrepare::PageSize => 1
            );

            $articlesCount = ArticleQueueFactory::Count( $search);
            return $articlesCount >= self::PostsPerDayInFeed;
        }
    }
?>