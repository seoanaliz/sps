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
            $time = Request::getString( 'time' );
            $type = Request::getString( 'type' );
            $targetFeedId = Request::getInteger( 'targetFeedId' );

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
            $object->startDate = DateTimeWrapper::Now();
            $object->endDate = DateTimeWrapper::Now();
            $object->time = new DateTimeWrapper($time);
            $object->type = $type;
            $object->targetFeedId = $targetFeedId;

            $sqlResult = GridLineFactory::Add($object);

            if ($sqlResult) {
                $result['success'] = true;
            }

            echo ObjectHelper::ToJSON($result);
        }
    }
?>