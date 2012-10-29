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

        $articleQueueId = Request::getInteger('articleQueueId');
        $deleteDateTime = Request::getInteger('articleQueueId');

        $articleQueue = ArticleQueueFactory::GetById($articleQueueId);
        $articleQueue->deleteAt = new DateTimeWrapper($deleteDateTime);
        ArticleQueueFactory::Update($articleQueue, array('deleteAt'));
        //ArticleQueueFactory::UpdateByMask($articleQueue,  array('articleQueueId' => $articleQueue->articleQueueId));

        $result = UrlParser::Parse(Request::getString('url'));
        $callback = Request::getString('callback');

        if (!empty($callback)) {
            echo "$callback (" . ObjectHelper::ToJSON($result) . ");";
        } else {
            echo ObjectHelper::ToJSON($result);
        }
    }
}
?>
