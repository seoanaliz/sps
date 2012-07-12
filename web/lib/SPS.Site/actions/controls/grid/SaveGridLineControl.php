<?php
    Package::Load( 'SPS.Site' );

    /**
     * SaveGridLineControl Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class SaveGridLineControl {

        public function Execute() {
            $gridLineId = Request::getInteger( 'gridLineId' );
            $time = Request::getString( 'time' );
            $type = Request::getString( 'type' );
            $targetFeedId = Request::getInteger( 'targetFeedId' );
            $startDate = Request::getDateTime('startDate');
            $endDate = Request::getDateTime('endDate');

            $result = array(
                'success' => false
            );

            if (empty($time) || empty($targetFeedId) || empty($type) || empty(GridLineUtility::$Types[$type])) {
                echo ObjectHelper::ToJSON($result);
                return false;
            }

            $targetFeed = TargetFeedFactory::GetById($targetFeedId);

            if (empty($targetFeed)) {
                echo ObjectHelper::ToJSON($result);
                return false;
            }

            //check access
            if (!AccessUtility::HasAccessToTargetFeedId($targetFeedId)) {
                echo ObjectHelper::ToJSON($result);
                return false;
            }

            $object = new GridLine();
            $object->gridLineId = $gridLineId;
            $object->startDate = $startDate;
            $object->endDate = $endDate;
            $object->time = new DateTimeWrapper($time);
            $object->type = $type;
            $object->targetFeedId = $targetFeedId;

            if (empty($object->gridLineId)) {
                $queryResult = GridLineFactory::Add($object);
            } else {
                $queryResult = GridLineFactory::Update($object);
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