<?php
/**
 * User: x100up
 * Date: 29.10.12 22:51
 * In Code We Trust
 */

Package::Load( 'SPS.Site' );


class PostDeletePlanControl
{
    public function Execute() {
        $result = array();
        $articleQueueId = Request::getInteger('queueId');
        $time = Request::getString('time');

        if (is_null($articleQueueId) || is_null($time)) {
            $result['success'] = false;
            $result['error'] = 'Need more data';
        } else {

            $articleQueue = ArticleQueueFactory::GetById($articleQueueId);

            //check access
            if (!AccessUtility::HasAccessToTargetFeedId($articleQueue->targetFeedId)) {
                $result['success'] = false;
                $result['error'] = 'Access Denied';
            } else {
                list($hour, $minutes) = explode(':', $time);
                $ts = $articleQueue->startDate->getTimestamp();
                $articleQueue->deleteAt = new DateTimeWrapper(null);
                $articleQueue->deleteAt->setTimestamp($ts)->modify('+'.$hour.' hours')->modify('+'.$minutes.' minutes');
                ArticleQueueFactory::UpdateByMask($articleQueue, array('deleteAt'), array('articleQueueId' => $articleQueueId));
                $result['success'] = true;
            }
        }

        echo ObjectHelper::ToJSON($result);
    }
}
?>
