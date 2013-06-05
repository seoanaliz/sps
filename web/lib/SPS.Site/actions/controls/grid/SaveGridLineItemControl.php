<?php
    /**
     * SaveGridLineItemControl Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class SaveGridLineItemControl extends BaseControl {

        public function Execute() {
            $TargetFeedAccessUtility = new TargetFeedAccessUtility($this->vkId);

            $gridLineId = Request::getInteger( 'gridLineId' );
            $gridLineItemId = Request::getInteger( 'gridLineItemId' );
            $time = Request::getString( 'time' );
            $timestamp = Request::getInteger( 'timestamp' );
            $itemDate = new DateTimeWrapper(date('d.m.Y', !empty($timestamp) ? $timestamp : null) . ' ' . $time);
            $queueId = Request::getInteger( 'queueId' );

            $result = array(
                'success' => false
            );

            if (empty($time) || empty($gridLineId)) {
                echo ObjectHelper::ToJSON($result);
                return false;
            }

            $gridLine = GridLineFactory::GetById($gridLineId);
            if (empty($gridLine)) {

                echo ObjectHelper::ToJSON($result);
                return false;
            }

            //check access
            if (!$TargetFeedAccessUtility->canSaveGridLine($gridLine->targetFeedId)) {
                echo ObjectHelper::ToJSON($result);
                return false;
            }
            if( $queueId && ArticleUtility::IsTooCloseToPrevious( $gridLine->targetFeedId, $itemDate->getTimestamp())) {
                $result['message'] = 'Time between posts is too small';
                echo ObjectHelper::ToJSON($result);
                return false;
            }

            if (!$TargetFeedAccessUtility->canSaveGridLine($gridLine->targetFeedId)) {
                echo ObjectHelper::ToJSON($result);
                return false;
            }

            $object = new GridLineItem();
            $object->gridLineItemId = $gridLineItemId;
            $object->gridLineId = $gridLineId;
            $object->date = $itemDate;

            if (!empty($object->gridLineItemId)) {
                $queryResult = GridLineItemFactory::Update($object);
            } else {
                $queryResult = GridLineItemFactory::Add($object);
            }

            if (!$queryResult) {
                $result['message'] = 'saveError';
            } else {
                $result['success'] = true;
            }

            if (!empty($queueId)) {
                //актуализируем время запланированного контента
                ArticleUtility::ChangeQueueDates($queueId, $itemDate->format('U'));
            }

            echo ObjectHelper::ToJSON($result);
        }
    }
?>