<?php
    Package::Load( 'SPS.Site' );

    /**
     * SaveGridLineItemControl Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class SaveGridLineItemControl {

        public function Execute() {
            $gridLineId = Request::getInteger( 'gridLineId' );
            $gridLineItemId = Request::getInteger( 'gridLineItemId' );
            $time = Request::getString( 'time' );
            $timestamp = Request::getInteger( 'timestamp' );
            $itemDate = new DateTimeWrapper(date('d.m.Y', !empty($timestamp) ? $timestamp : null) . ' ' . $time);

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
            if (!AccessUtility::HasAccessToTargetFeedId($gridLine->targetFeedId)) {
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

            echo ObjectHelper::ToJSON($result);
        }
    }
?>