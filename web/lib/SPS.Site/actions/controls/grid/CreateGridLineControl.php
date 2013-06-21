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
            $time = Request::getString( 'time' );
            $type = Request::getString( 'type' );
            $targetFeedId = Request::getInteger( 'targetFeedId' );
            $startDate = Request::getDateTime('startDate');
            $endDate = Request::getDateTime('endDate');

            $result = array(
                'success' => false
            );

            if (empty($time) || empty($targetFeedId) || !isset(GridLineUtility::$Types[$type])) {
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

            $object = new GridLine();
            $object->startDate = $startDate;
            $object->endDate = $endDate;
            $object->time = new DateTimeWrapper($time);
            $object->type = $type;
            $object->targetFeedId = $targetFeedId;
            $object->repeat = false;

            $queryResult = GridLineFactory::Add($object, array(BaseFactory::WithReturningKeys => true));

            if (!$queryResult) {
                $result['message'] = 'saveError';
            } else {
                $result['success'] = true;
                $canEdit = $TargetFeedAccessUtility->getRoleForTargetFeed($targetFeedId) != UserFeed::ROLE_AUTHOR;
                $result['html'] = SlotUtility::renderNew($object, $object->startDate, $canEdit);
            }

            echo ObjectHelper::ToJSON($result);
        }
    }
?>