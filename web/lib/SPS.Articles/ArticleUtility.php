<?php
    /**
     * ArticleUtility
     * @package    SPS
     * @subpackage Articles
     * @author     Shuler
     */
    class ArticleUtility {

        /** in seconds*/

        const TimeBeetwenPosts   =  269;//seconds

        const PostsPerDayInFeed  =  150;

        const PostsPerHourInFeed =  15;

        const PostsPer7HourByUser = 40;

        const SociateDummyArticleId = 4221948;

        const QueueSourceSb       = 'sb';

        const QueueSourceVk       = 'vk';

        const QueueSourceSociate  = 'sociate';

        const QueueSourceVkPostponed = 'vk_postponed';

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
            $object->statusId = StatusUtility::Enabled; //если выносим из защищенного интервала
            ArticleQueueFactory::UpdateByMask(
                $object,
                array('startDate', 'endDate', 'isDeleted', 'deleteAt', 'protectTo', 'statusId'),
                array('articleQueueId' => $queueId, 'statusIdIn' => [StatusUtility::Enabled, StatusUtility::Finished])
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
            $search = [
                'targetFeedId'  =>  $targetFeedId,
                'startDateFrom' =>  $from,
                'startDateTo'   =>  $intervalTime->modify('+' . self::TimeBeetwenPosts . ' seconds'),
                'addedFrom'     =>  ArticleUtility::QueueSourceSb
            ];
            //удаляем из проверки сам пост
            if( $articleQueueId ) {
                $search['articleQueueIdNE'] = $articleQueueId;
            }
            $check = ArticleQueueFactory::Get( $search );


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
                'statusIdNE'    =>  StatusUtility::Deleted,
            );
            $faq = new ArticleQueue();
            $faq->statusId = $statusId;
            return ArticleQueueFactory::UpdateByMask($faq, array('statusId'), $search );
        }

        /**
         *@var $targetFeedId int
         *@var $startDate DateTimeWrapper
         *@var $protectTo DateTimeWrapper
         */
        public static function InsertFakeAQ( $targetFeedId, $startDate, $protectTo ) {
            //предполагаем, что этот пост уже есть
            if ( ArticleUtility::IsInSociatedInterval($targetFeedId, $startDate)) {
                echo 'ccccombo!';
                return false;
            }
            $articleQueue = new ArticleQueue;
            $articleQueue->articleId = ArticleUtility::SociateDummyArticleId;
            $articleQueue->collectLikes = false;
            $articleQueue->sentAt       = 0;
            $articleQueue->startDate    = (new DateTimeWrapper($startDate->Default24hFormat()))->modify('-1 minute');
            $articleQueue->endDate      = (new DateTimeWrapper($startDate->Default24hFormat()))->modify( '+10 minutes');
            $articleQueue->targetFeedId = $targetFeedId;
            $articleQueue->statusId     = StatusUtility::Finished;
            $articleQueue->createdAt    = new DateTimeWrapper(ParserVkontakte::false_created_time);
            $articleQueue->isDeleted    = false;
            $articleQueue->type         = 'content'; //неспортивно
            $articleQueue->addedFrom    = ArticleUtility::QueueSourceSociate;
            $articleQueue->protectTo    = $protectTo;
            ArticleQueueFactory::Add($articleQueue, [BaseFactory::WithReturningKeys => true]);

            $articleRecord = ArticleRecordFactory::Get(['articleId' => ArticleUtility::SociateDummyArticleId]);
            $articleRecord = reset($articleRecord);
            $newArticleRecord = clone( $articleRecord );
            $newArticleRecord->articleRecordId = false;
            $newArticleRecord->articleQueueId  = $articleQueue->articleQueueId;
            ArticleRecordFactory::Add($newArticleRecord);
            GridLineUtility::make_grids( $targetFeedId, $startDate );
            ArticleUtility::setAQStatus($articleQueue->startDate, $protectTo, StatusUtility::Finished, $articleQueue->targetFeedId );
            return true;
        }

        //проверяем, нет ли уже на это время поста из sociate
        public static function IsInSociatedInterval( $targetFeedId, $postTime ) {
            $search = array(
                'startDateTo'   =>  $postTime,
                'protectToGE'   =>  $postTime,
                'targetFeedId'  =>  $targetFeedId,
                'addedFrom'     =>  ArticleUtility::QueueSourceSociate
            );

            return (bool)ArticleQueueFactory::Count( $search);
        }


    }
?>
