<?php
    Package::Load('SPS.Site/base');

    /**
     * SaveGridLineControl Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class SaveGridLineControl extends BaseControl {

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

            $TargetFeedAccessUtility = new TargetFeedAccessUtility($this->vkId);

            //check access
            if (!$TargetFeedAccessUtility->canSaveGridLine($targetFeedId)) {
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
                $object->repeat = false;
                $queryResult = GridLineFactory::Add($object, array(BaseFactory::WithReturningKeys => true));
            } else {
                $queryResult = GridLineFactory::Update($object, array(BaseFactory::WithReturningKeys => true));
            }

            if (!$queryResult) {
                $result['message'] = 'saveError';
            } else {
                $result['success'] = true;
                $canEdit = $TargetFeedAccessUtility->getRoleForTargetFeed($targetFeedId) != UserFeed::ROLE_AUTHOR;
                $result['html'] = SlotUtility::renderEmpty($object, $object->time, $canEdit);
            }

            echo ObjectHelper::ToJSON($result);
        }
    }
?>