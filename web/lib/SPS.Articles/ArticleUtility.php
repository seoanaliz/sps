<?php
    /**
     * ArticleUtility
     * @package    SPS
     * @subpackage Articles
     * @author     Shuler
     */
    class ArticleUtility {

        /** in seconds*/
        const TimeBeetwenPosts   =  299;

        const PostsPerDayInFeed  =  150;

        const PostsPerHourInFeed =  15;

        const PostsPer7HourByUser = 40;

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

            if ($object->protectTo ) {
                ArticleUtility::setAQStatus($object->startDate, $object->protectTo, StatusUtility::Enabled, $object->targetFeedId );
            }

            $object->isDeleted = false;
            $object->deleteAt  = null;
            $object->protectTo = null;

            ArticleQueueFactory::UpdateByMask(
                $object,
                array('startDate', 'endDate', 'isDeleted', 'deleteAt', 'protectTo'),
                array('articleQueueId' => $queueId, 'statusId' => 1)
            );

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

        public static function IsTooCloseToPrevious( $targetFeedId, $newPostTimestamp, $articleQueueId )
        {
            $intervalTime = new DateTimeWrapper(date('r', $newPostTimestamp));
            $from = new DateTimeWrapper(date('r', $newPostTimestamp));
            $from->modify( '- ' . self::TimeBeetwenPosts . ' seconds');
            $search = array(
                'targetFeedId'  =>  $targetFeedId,
                'startDateFrom' =>  $from,
                'startDateTo'   =>  $intervalTime->modify('+' . self::TimeBeetwenPosts . ' seconds')
            );

            if( $articleQueueId ) {
                $search['articleQueueIdNE'] = $articleQueueId;
            }
            $check = ArticleQueueFactory::Get( $search );
            //удаляем из проверки сам пост

            return !empty( $check );
        }

        public static function IsArticlesLimitReached($targetFeedId, $newPostTimestamp, $articleQueueId )
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
            if( $articleQueueId ) {
                $search['articleQueueIdNE'] = $articleQueueId;
            }

            $articlesCount = ArticleQueueFactory::Count( $search);
            return $articlesCount >= self::PostsPerDayInFeed;
        }

        public static function IsInIntervalLimitsForFeed( $targetFeedId, $newPostTimestamp, $articleQueueId )
        {
            //добавляем 31 секунду, учитывая сдвиг в ArticleUtility::BuildDates
            $intervalStart = new DateTimeWrapper(date('r', $newPostTimestamp + 31));
            $h = $intervalStart->format('H');
            $intervalStart->setTime($h, 0, 0);
            $intervalStop  = new DateTimeWrapper(date('r', $newPostTimestamp));
            $intervalStop->setTime($h + 1, 0, 0);
            //ограничение по количеству постов в ленте

            $search = array(
                'targetFeedId'  =>  $targetFeedId,
                'startDateFrom' =>  $intervalStart,
                'startDateTo'   =>  $intervalStop,
                 BaseFactoryPrepare::PageSize => 1
            );

            if( $articleQueueId ) {
                $search['articleQueueIdNE'] = $articleQueueId;
            }
            $articlesCount = ArticleQueueFactory::Count( $search);
            return $articlesCount < self::PostsPerHourInFeed;
        }

        public static function IsInInervalsLimitsForUser($authorVkId, $newPostTimestamp )
        {
            $intervalStart  = new DateTimeWrapper(date('r', $newPostTimestamp));

            $intervalStart->modify('- 7 hour');
            $intervalStop   = new DateTimeWrapper(date('r', $newPostTimestamp));
            $intervalStop->
            $intervalStop->modify('+ 7 hour');
            $intervalMiddle = new DateTimeWrapper(date('r', $newPostTimestamp));

            //ограничение по количеству постов в ленте
            $search = array(
                'author'  =>  $authorVkId,
                'startDateFrom' =>  $intervalStart,
                'startDateTo'   =>  $intervalMiddle,
                 BaseFactoryPrepare::PageSize => 1
            );

            $firstIntervalCount = ArticleQueueFactory::Count( $search);

            $search['startDateFrom'] = $intervalMiddle;
            $search['startDateTo']   = $intervalStop;

            $secondIntervalCount = ArticleQueueFactory::Count( $search);
            return  (
                  $firstIntervalCount   < self::PostsPer7HourByUser &&
                  $secondIntervalCount  < self::PostsPer7HourByUser
            );

        }

        public static function checkLimitsForFeed( $targetFeedId, $newPostTimestamp, $articleQueueId )
        {
            if( self::IsTooCloseToPrevious($targetFeedId, $newPostTimestamp, $articleQueueId)) {
                return 'Time between posts is too small';
            } elseif ( self::IsArticlesLimitReached($targetFeedId, $newPostTimestamp, $articleQueueId)) {
                return 'Too many posts this day';
            } elseif ( !self::IsInIntervalLimitsForFeed($targetFeedId, $newPostTimestamp, $articleQueueId)) {
                return 'Too many posts this hour';
            }
            return false;
        }

        public static function isInProtectedInterval( $targetFeedId, $newPostTimestamp, $queueId = false ) {
            $newPostTime = new DateTimeWrapper(date('r', $newPostTimestamp));
            $search = array(
                'startDateTo'   =>  $newPostTime,
                'protectToGE'   =>  $newPostTime,
                'targetFeedId'  =>  $targetFeedId,
            );
            if ( $queueId ) {
                $search['articleQueueIdNE'] = $queueId;
            }

            return (bool)ArticleQueueFactory::Count( $search);
        }

        //ставит статус элементам очереди
        public static function setAQStatus( $dateFrom, $dateTo, $statusId, $targetFeedId ) {
            $dateFrom = new  DateTimeWrapper( $dateFrom->format('r') );
            $search = array(
                'startDateFrom' =>  $dateFrom->modify('+90 seconds'),
                'startDateTo'   =>  $dateTo,
                'targetFeedId'  =>  $targetFeedId,
            );
            $faq = new ArticleQueue();
            $faq->statusId = $statusId;
            return ArticleQueueFactory::UpdateByMask($faq, array('statusId'), $search );
        }
    }
?>
