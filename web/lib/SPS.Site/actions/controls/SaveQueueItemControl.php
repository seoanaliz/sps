<?php
/**
 * SaveArticleControl Action
 * @package    SPS
 * @subpackage Site
 * @author     Shuler
 */
class SaveQueueItemControl extends BaseControl
{

    private function convert_line_breaks($string, $line_break = PHP_EOL)
    {
        $patterns = array(
            "/(<br>|<br \/>|<br\/>|<div>)\s*/i",
            "/(\r\n|\r|\n)/",
        );
        $replacements = array(
            PHP_EOL,
            $line_break
        );
        $string = preg_replace($patterns, $replacements, $string);
        return $string;
    }


    /**
     * Entry Point
     */
    public function Execute()
    {
        $result = array(
            'success' => false
        );

        $articleQueueId = Request::getInteger('articleQueueId');
        $text = trim(Request::getString('text'));
        $link = trim(Request::getString('link'));
        $photos = Request::getArray('photos');
        $targetFeedId = Request::getInteger('targetFeedId');
        $userGroupId = Request::getInteger('userGroupId');
        $sourceFeedId = Request::getInteger('sourceFeedId');
        if (!$userGroupId) {
            $userGroupId = null;
        }

        $TargetFeedAccessUtility = new TargetFeedAccessUtility($this->vkId);
        $role = $TargetFeedAccessUtility->getRoleForTargetFeed($targetFeedId);
        if (is_null($role)) {
            return ObjectHelper::ToJSON(array('success' => false));
        }

        $text = $this->convert_line_breaks($text);
        $text = strip_tags($text);

        //parsing link
        $linkInfo = UrlParser::Parse($link);
        if (empty($linkInfo)) {
            $link = null;
        }

        if (empty($text) && empty($photos) && empty($link)) {
            $result['message'] = 'emptyArticle';
            echo ObjectHelper::ToJSON($result);
            return false;
        }

        $articleRecord = ArticleRecordFactory::GetOne( array( 'articleQueueId' => $articleQueueId ));
        $articleRecord->content = $text;
        $articleRecord->likes = 0;
        $articleRecord->photos = !empty($photos) ? $photos : array();
        $articleRecord->link = $link;
        $queryResult = $this->update($articleQueueId, $articleRecord);


        if (!$queryResult) {
            $result['message'] = 'saveError';
        } else {
            $result['success'] = true;
            if ($articleQueueId) {
                $result['id'] = $articleQueueId;
            }
        }

        if ($result['success']) {
            $result['articleQueueId'] = $articleQueueId;
        }

        echo ObjectHelper::ToJSON($result);
    }

    private function update($id, $articleRecord)
    {

        ConnectionFactory::BeginTransaction();

        $result = ArticleRecordFactory::UpdateByMask($articleRecord, array('content', 'photos', 'link'), array('articleQueueId' => $id));

        ConnectionFactory::CommitTransaction($result);
        return $result;
    }
}

?>
