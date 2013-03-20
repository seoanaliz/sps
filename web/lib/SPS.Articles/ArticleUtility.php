<?php
    /**
     * ArticleUtility
     * @package    SPS
     * @subpackage Articles
     * @author     Shuler
     */
    class ArticleUtility {

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
    }
?>