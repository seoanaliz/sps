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
            $object = new ArticleQueue();
            self::BuildDates($object, $timestamp);
            $object->isDeleted = false;
            $object->deleteAt = null;
            ArticleQueueFactory::UpdateByMask($object, array('startDate', 'endDate', 'isDeleted', 'deleteAt'), array('articleQueueId' => $queueId, 'statusId' => 1));
        }

        public static function BuildDates($object, $timestamp) {
            $object->startDate = new DateTimeWrapper(date('r', $timestamp));
            $object->endDate = new DateTimeWrapper(date('r', $timestamp));

            $object->startDate->modify('-30 seconds');
            $object->endDate->modify('+9 minutes');
        }
    }
?>