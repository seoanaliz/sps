<?php
    Package::Load('SPS.Site/base');

    /**
     * CreateGridLineControl Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     * @author     Eugene Kulikov
     */
    class CreateGridLineControl extends BaseControl {

        public function Execute() {
            $time = Request::getString( 'time' ); // время (например, 12:40)
            $type = Request::getString( 'type' );
            $targetFeedId = Request::getInteger( 'targetFeedId' );
            $timestamp = Request::getString( 'timestamp' ); // timestamp начала дня (нужен для даты)

            $result = array(
                'success' => false
            );

            if (empty($time) || empty($timestamp) || !is_numeric($timestamp) || empty($targetFeedId) || !isset(GridLineUtility::$Types[$type])) {
                echo ObjectHelper::ToJSON($result);
                return false;
            }

            $targetFeed = TargetFeedFactory::GetById($targetFeedId);
            if (empty($targetFeed)) {
                echo ObjectHelper::ToJSON($result);
                return false;
            }

            $TargetFeedAccessUtility = new TargetFeedAccessUtility($this->vkId);
            //check access
            if (!$TargetFeedAccessUtility->canSaveGridLine($targetFeedId)) {
                echo ObjectHelper::ToJSON($result);
                return false;
            }

            $Date = new DateTimeWrapper(date('d.m.Y', $timestamp));

            $object = new GridLine();
            $object->startDate = $Date;
            $object->endDate = $Date;
            $object->time = new DateTimeWrapper($time);
            $object->type = $type;
            $object->targetFeedId = $targetFeedId;
            $object->repeat = false;

            $queryResult = GridLineFactory::Add($object, array(BaseFactory::WithReturningKeys => true));

            if (!$queryResult) {
                $result['message'] = 'saveError';
            } else {
                $result['success'] = true;
                $result['gridLineId'] = $object->gridLineId;
            }

            echo ObjectHelper::ToJSON($result);
        }
    }
?>